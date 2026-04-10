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
    const DEFAULT_COMPLEXITY = 50000;

    const OPTION_SERVER_URL = 'onoffice-settings-altcha-server-url';
    const OPTION_HMAC_KEY = 'onoffice-settings-altcha-hmac-key';
    const OPTION_COMPLEXITY = 'onoffice-settings-altcha-complexity';

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

        // 1. Verify PoW + HMAC signature + expiration via official library
        if (!Altcha::verifySolution($this->_payload, $this->_hmacKey)) {
            $this->_errorCodes[] = 'verification-failed';
            return false;
        }

        // 2. Replay protection: ensure this challenge has not been used before
        $decoded = base64_decode($this->_payload, true);
        $data = json_decode($decoded, true);
        $challenge = $data['challenge'] ?? '';

        $transientKey = self::TRANSIENT_PREFIX . hash('sha256', $challenge);
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
     */
    public static function getServerUrl(): string
    {
        $url = get_option(self::OPTION_SERVER_URL, self::DEFAULT_SERVER_URL);
        return !empty($url) ? rtrim($url, '/') : self::DEFAULT_SERVER_URL;
    }

    /**
     * Get the HMAC key for signature verification.
     */
    public static function getHmacKey(): string
    {
        return (string) get_option(self::OPTION_HMAC_KEY, '');
    }

    /**
     * Get the PoW complexity value.
     */
    public static function getComplexity(): int
    {
        $val = (int) get_option(self::OPTION_COMPLEXITY, self::DEFAULT_COMPLEXITY);
        return $val > 0 ? $val : self::DEFAULT_COMPLEXITY;
    }

    /**
     * Get the full challenge URL used by the widget.
     */
    public static function getChallengeUrl(): string
    {
        return self::getServerUrl() . '/altcha';
    }
}
