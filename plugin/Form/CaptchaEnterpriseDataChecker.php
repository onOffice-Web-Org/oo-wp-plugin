<?php
/**
 *
 *    Copyright (C) 2025 onOffice GmbH
 *
 */

namespace onOffice\WPlugin\Form;

class CaptchaEnterpriseDataChecker
{
    /**
     *
     */
    public static function addHook()
    {
        add_action('wp_ajax_check_captcha_enterprise_data', function () {
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

        echo json_encode([
            'success' => $result,
            'error-codes' => $pCaptchaHandler->getErrorCodes(),
            'score' => $pCaptchaHandler->getScore(),
        ]);

        wp_die();
    }
}