<?php
/**
 *
 *    Copyright (C) 2025 onOffice GmbH
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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2025, onOffice(R) GmbH
 *
 */

class CaptchaEnterpriseHandler
{
    const API_URL = 'https://recaptchaenterprise.googleapis.com/v1/projects/%s/assessments?key=%s';
    const RECAPTCHA_RESPONSE_PARAM = 'g-recaptcha-response';
    const SCORE_THRESHOLD = 0.5;

    /** @var string */
    private $_token = '';

    /** @var string */
    private $_projectId = '';

    /** @var string */
    private $_siteKey = '';

    /** @var string */
    private $_apiKey = '';

    /** @var string */
    private $_action = 'submit_form';

    /** @var array */
    private $_errorCodes = [];

    /** @var float|null */
    private $_score = null;

    /**
     * @param string $token
     * @param string $projectId
     * @param string $siteKey
     * @param string $apiKey
     * @param string $action
     */
    public function __construct(string $token, string $projectId, string $siteKey, string $apiKey, string $action = 'submit_form')
    {
        $this->_token = $token;
        $this->_projectId = $projectId;
        $this->_siteKey = $siteKey;
        $this->_apiKey = $apiKey;
        $this->_action = $action;
    }

    /**
     * @return bool
     */
    public function checkCaptcha(): bool
    {
        if (empty($this->_token)) {
            $this->_errorCodes = ['missing-input-response'];
            return false;
        }

        if (empty($this->_projectId)) {
            $this->_errorCodes = ['missing-input-secret'];
            return false;
        }

        if (empty($this->_siteKey)) {
            $this->_errorCodes = ['missing-site-key'];
            return false;
        }

        if (empty($this->_apiKey)) {
            $this->_errorCodes = ['missing-api-key'];
            return false;
        }

        $apiUrl = sprintf(self::API_URL, urlencode($this->_projectId), urlencode($this->_apiKey));

        $response = wp_remote_post($apiUrl, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode([
                'event' => [
                    'token' => $this->_token,
                    'siteKey' => $this->_siteKey,
                    'expectedAction' => $this->_action,
                ],
            ]),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            $this->_errorCodes = ['connection-failed'];
            return false;
        }

        $responseCode = wp_remote_retrieve_response_code($response);
        $result = json_decode(wp_remote_retrieve_body($response), true);

        if ($responseCode !== 200) {
            $this->_errorCodes = [$result['error']['message'] ?? 'api-error'];
            return false;
        }

        return $this->evaluateResult($result);
    }

    /**
     * @param array $result
     * @return bool
     */
    private function evaluateResult(array $result): bool
    {
        // Token gültig?
        if (($result['tokenProperties']['valid'] ?? false) !== true) {
            $this->_errorCodes = [$result['tokenProperties']['invalidReason'] ?? 'invalid-token'];
            return false;
        }

        // Action stimmt?
        if (($result['tokenProperties']['action'] ?? '') !== $this->_action) {
            $this->_errorCodes = ['action-mismatch'];
            return false;
        }

        // Score prüfen
        $this->_score = $result['riskAnalysis']['score'] ?? 0;

        error_log('Captcha Enterprise check result: ' . print_r($result, true));
        error_log('Score: ' . $this->getScore());
        error_log('Errors: ' . implode(', ', $this->getErrorCodes()));

        if ($this->_score < self::SCORE_THRESHOLD) {
            $this->_errorCodes = ['score-too-low'];
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getErrorCodes(): array
    {
        return $this->_errorCodes;
    }

    /**
     * @return float|null
     */
    public function getScore(): ?float
    {
        return $this->_score;
    }
}