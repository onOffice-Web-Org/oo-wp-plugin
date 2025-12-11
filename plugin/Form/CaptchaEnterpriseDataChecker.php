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
 * @copyright 2003-2024, onOffice(R) GmbH
 *
 */

class CaptchaEnterpriseDataChecker
{
    /**
     *
     */

    public static function addHook()
    {
        add_action('wp_ajax_check_captcha_enterprise_data', function() {
            $pCaptchaDataChecker = new CaptchaEnterpriseDataChecker();
            $pCaptchaDataChecker->check();
        });
    }


    /**
     *
     */

    public function check()
    {
        $token = filter_input(INPUT_POST, 'token');
        $projectId = filter_input(INPUT_POST, 'projectId');
        $siteKey = filter_input(INPUT_POST, 'siteKey');

        $response = [
            'success' => false,
            'error-codes' => [],
        ];

        if (empty($token)) {
            $response['error-codes'][] = 'missing-input-response';
            echo json_encode($response);
            wp_die();
        }

        if (empty($projectId)) {
            $response['error-codes'][] = 'missing-input-secret';
            echo json_encode($response);
            wp_die();
        }

        if (empty($siteKey)) {
            $response['error-codes'][] = 'missing-site-key';
            echo json_encode($response);
            wp_die();
        }

        // Google reCAPTCHA Enterprise API aufrufen
        $apiUrl = sprintf(
            'https://recaptchaenterprise.googleapis.com/v1/projects/%s/assessments',
            urlencode($projectId)
        );

        $requestBody = [
            'event' => [
                'token' => $token,
                'siteKey' => $siteKey,
                'expectedAction' => 'test_keys',
            ],
        ];

        $apiResponse = wp_remote_post($apiUrl, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($requestBody),
            'timeout' => 30,
        ]);

        if (is_wp_error($apiResponse)) {
            $response['error-codes'][] = 'bad-request';
            echo json_encode($response);
            wp_die();
        }

        $responseBody = json_decode(wp_remote_retrieve_body($apiResponse), true);
        $responseCode = wp_remote_retrieve_response_code($apiResponse);

        if ($responseCode !== 200) {
            $response['error-codes'][] = 'bad-request';
            if (isset($responseBody['error']['message'])) {
                $response['message'] = $responseBody['error']['message'];
            }
            echo json_encode($response);
            wp_die();
        }

        if (isset($responseBody['tokenProperties']['valid']) && $responseBody['tokenProperties']['valid'] === true) {
            $response['success'] = true;
            $response['score'] = isset($responseBody['riskAnalysis']['score']) ? $responseBody['riskAnalysis']['score'] : 'N/A';
        } else {
            $invalidReason = isset($responseBody['tokenProperties']['invalidReason']) 
                ? strtolower(str_replace('_', '-', $responseBody['tokenProperties']['invalidReason']))
                : 'invalid-input-response';
            $response['error-codes'][] = $invalidReason;
        }

        echo json_encode($response);
        wp_die();
    }
}