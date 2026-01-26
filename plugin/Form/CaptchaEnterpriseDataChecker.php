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

if ( ! defined( 'ABSPATH' ) ) exit;

class CaptchaEnterpriseDataChecker
{
    /**
     *
     */
    public static function addHook()
    {
        add_action('wp_ajax_check_captcha_enterprise_data', function () {

            // Verify nonce
            if (!check_ajax_referer('check_captcha_enterprise_data', 'nonce', false)) {
                wp_send_json_error(['error-codes' => ['invalid-nonce']], 403);
            }

            $pCaptchaDataChecker = new CaptchaEnterpriseDataChecker();
            $pCaptchaDataChecker->check();
        });
    }

    /**
     *
     */
    public function check()
    {
        $token = filter_input(INPUT_POST, CaptchaEnterpriseHandler::RECAPTCHA_RESPONSE_PARAM);
        $projectId = filter_input(INPUT_POST, 'projectId');
        $siteKey = filter_input(INPUT_POST, 'siteKey');
        $apiKey = filter_input(INPUT_POST, 'apiKey');

        $pCaptchaHandler = new CaptchaEnterpriseHandler($token, $projectId, $siteKey, $apiKey, 'test_keys');
        $result = $pCaptchaHandler->checkCaptcha();

        $data = [
            'error-codes' => $pCaptchaHandler->getErrorCodes(),
            'score' => $pCaptchaHandler->getScore(),
        ];

        if ($result) {
            wp_send_json_success($data);
        } else {
            wp_send_json_error($data);
        }
    }
}