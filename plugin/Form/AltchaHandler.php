<?php

/**
 *
 *    Copyright (C) 2026 onOffice GmbH
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace onOffice\WPlugin\Form;

use AltchaOrg\Altcha\Altcha;
use AltchaOrg\Altcha\Algorithm\Pbkdf2;
use AltchaOrg\Altcha\Challenge;
use AltchaOrg\Altcha\ChallengeParameters;
use AltchaOrg\Altcha\Payload;
use AltchaOrg\Altcha\Solution;
use AltchaOrg\Altcha\VerifySolutionOptions;

/**
 * ALTCHA anti-spam handler.
 *
 * Combines configuration (when to activate) and server-side verification
 * (proof-of-work, HMAC signature, replay protection).
 *
 * ALTCHA is activated as a fallback when:
 * - No reCAPTCHA (Enterprise or Classic) is configured
 * - The active theme is one of the supported onOffice WP-Websites themes
 */
class AltchaHandler
{
    const ALTCHA_RESPONSE_PARAM = 'altcha';

    const TRANSIENT_PREFIX = 'altcha_used_';

    const REPLAY_TTL = 600; // 10 minutes, should be longer than the expected time between challenge issuance and form submission

    const DEFAULT_SERVER_URL = 'https://altcha.onofficeweb.com';

    const SUPPORTED_THEMES = [
        'onoffice-pure',
        'onoffice-classic',
        'onoffice-timeless',
        'onoffice-modern',
    ];

    /** @var string */
    private $_payload = '';

    /** @var string */
    private $_hmacKey = '';

    /** @var array */
    private $_errorCodes = [];

    /**
     * @param string $payload  The base64-encoded ALTCHA payload from the form
     * @param string $hmacKey  The shared HMAC key for signature verification
     */
    public function __construct(string $payload, string $hmacKey = '')
    {
        $this->_payload = $payload;
        $this->_hmacKey = $hmacKey;
    }

    /**
     * Verify the ALTCHA challenge response using the official library.
     *
     * @return bool
     */
    public function checkCaptcha(): bool
    {
        if (empty($this->_payload)) {
            $this->_errorCodes[] = 'missing-payload';
            return false;
        }

        if (empty($this->_hmacKey)) {
            $this->_errorCodes[] = 'missing-hmac-key';
            return false;
        }

        // Decode the base64 payload from the widget
        $decoded = base64_decode($this->_payload, true);
        if ($decoded === false) {
            $this->_errorCodes[] = 'invalid-base64';
            return false;
        }

        $data = json_decode($decoded, true);
        if (!is_array($data) || !isset($data['challenge'], $data['solution'])) {
            $this->_errorCodes[] = 'invalid-payload-structure';
            return false;
        }

        // Reconstruct v2 Payload from widget data
        $params = ChallengeParameters::fromArray($data['challenge']['parameters'] ?? []);
        $challenge = new Challenge($params, $data['challenge']['signature'] ?? null);
        $solution = new Solution(
            counter: (int) ($data['solution']['counter'] ?? 0),
            derivedKey: (string) ($data['solution']['derivedKey'] ?? ''),
            time: isset($data['solution']['time']) ? (float) $data['solution']['time'] : null,
        );
        $payload = new Payload($challenge, $solution);

        // Verify PoW + HMAC signature + expiration via official v2 library
        $altcha = new Altcha(hmacSignatureSecret: $this->_hmacKey);
        $result = $altcha->verifySolution(new VerifySolutionOptions(
            algorithm: new Pbkdf2(),
            payload: $payload,
        ));

        if (!$result->verified) {
            if ($result->expired) {
                $this->_errorCodes[] = 'challenge-expired';
            } elseif ($result->invalidSignature) {
                $this->_errorCodes[] = 'invalid-signature';
            } elseif ($result->invalidSolution) {
                $this->_errorCodes[] = 'invalid-solution';
            } else {
                $this->_errorCodes[] = 'verification-failed';
            }
            return false;
        }

        // Replay protection: ensure this challenge has not been used before
        $transientKey = self::TRANSIENT_PREFIX . hash('sha256', $params->nonce . $params->salt);
        if (get_transient($transientKey) !== false) {
            $this->_errorCodes[] = 'challenge-replayed';
            return false;
        }
        set_transient($transientKey, 1, self::REPLAY_TTL);

        return true;
    }

    /**
     * @return array
     */
    public function getErrorCodes(): array
    {
        return $this->_errorCodes;
    }

    // ---- Static configuration helpers ----

    /**
     * Check whether ALTCHA should be active.
     *
     * Returns true when no reCAPTCHA keys are configured AND the current
     * theme is one of the supported onOffice WP-Websites themes.
     */
    public static function isAltchaActive(): bool
    {
        if (self::hasRecaptchaConfigured()) {
            return false;
        }

        return self::isSupportedTheme();
    }

    /**
     * Check whether any reCAPTCHA variant is configured.
     */
    private static function hasRecaptchaConfigured(): bool
    {
        $enterpriseSiteKey = get_option('onoffice-settings-captcha-enterprise-sitekey', '');
        $enterpriseProjectId = get_option('onoffice-settings-captcha-enterprise-projectid', '');
        $enterpriseApiKey = get_option('onoffice-settings-captcha-enterprise-apikey', '');

        if (!empty($enterpriseSiteKey) && !empty($enterpriseProjectId) && !empty($enterpriseApiKey)) {
            return true;
        }

        $classicSiteKey = get_option('onoffice-settings-captcha-sitekey', '');
        $classicSecretKey = get_option('onoffice-settings-captcha-secretkey', '');

        if (!empty($classicSiteKey) && !empty($classicSecretKey)) {
            return true;
        }

        return false;
    }

    /**
     * Check whether the active theme is a supported onOffice WP-Websites theme.
     */
    public static function isSupportedTheme(): bool
    {
        $template = wp_get_theme()->get_template();
        return in_array($template, self::SUPPORTED_THEMES, true);
    }

    /**
     * Get the ALTCHA server URL.
     * Priority: env var > wp-config.php constant > default.
     */
    public static function getServerUrl(): string
    {
        $url = getenv('OO_ALTCHA_SERVER_URL')
            ?: (defined('OO_ALTCHA_SERVER_URL') ? OO_ALTCHA_SERVER_URL : self::DEFAULT_SERVER_URL);
        return !empty($url) ? rtrim($url, '/') : self::DEFAULT_SERVER_URL;
    }

    /**
     * Get the HMAC key for signature verification.
     * Priority: env var > wp-config.php constant.
     */
    public static function getHmacKey(): string
    {
        return getenv('OO_ALTCHA_HMAC_KEY')
            ?: (defined('OO_ALTCHA_HMAC_KEY') ? (string) OO_ALTCHA_HMAC_KEY : '');
    }

    /**
     * Get the full challenge URL used by the widget.
     */
    public static function getChallengeUrl(): string
    {
        return self::getServerUrl() . '/challenge';
    }
}
