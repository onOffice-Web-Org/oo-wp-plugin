<?php

/**
 *
 *    Copyright (C) 2023 onOffice GmbH
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

namespace onOffice\WPlugin\Renderer;

use Exception;

/**
 *
 */
class InputFieldGoogleRecaptchaAccountRenderer extends InputFieldRenderer
{
    /** @var array Classic reCAPTCHA field names */
    const CLASSIC_FIELDS = [
        'onoffice-settings-captcha-sitekey',
        'onoffice-settings-captcha-secretkey',
    ];

    /** @var array Enterprise reCAPTCHA field names */
    const ENTERPRISE_FIELDS = [
        'onoffice-settings-captcha-enterprise-projectid',
        'onoffice-settings-captcha-enterprise-sitekey',
        'onoffice-settings-captcha-enterprise-apikey',
    ];

    /**
     *
     * @param string $type
     * @param string $name
     * @param string $value
     *
     * @throws Exception
     */

    public function __construct($type, $name, $value = null)
    {
        if (!in_array($type, array('googleRecaptchaAccount'))) {
            throw new Exception('wrong type!');
        }
        parent::__construct($type, $name, $value);
    }


    /**
     *
     */

    public function render()
    {
        $iconShowPassword = '';
        $showDeleteGoogleRecaptchaKeysButton = false;
        $showDeleteEnterpriseKeysButton = false;
        $fieldName = $this->getName();

        // TODO: remove later, when Enterprise reCAPTCHA is fully rolled out
        // Classic reCAPTCHA fields
        if ($fieldName === 'onoffice-settings-captcha-secretkey') {
            $iconShowPassword = '<button type="button" class="button" data-toggle="0">
                    <span class="dashicons dashicons-visibility oo-icon-eye-secret-key" aria-hidden="true"></span> 
                    </button>';
            $showDeleteGoogleRecaptchaKeysButton = true;
        } elseif ($fieldName === 'onoffice-settings-captcha-sitekey') {
            $iconShowPassword = '<button type="button" class="button" data-toggle="0">
                    <span class="dashicons dashicons-visibility oo-icon-eye-site-key" aria-hidden="true"></span> 
                    </button>';
        }
        // Enterprise reCAPTCHA fields
        elseif ($fieldName === 'onoffice-settings-captcha-enterprise-projectid') {
            $iconShowPassword = '<button type="button" class="button" data-toggle="0">
                    <span class="dashicons dashicons-visibility oo-icon-eye-enterprise-projectid" aria-hidden="true"></span> 
                    </button>';
        } elseif ($fieldName === 'onoffice-settings-captcha-enterprise-sitekey') {
            $iconShowPassword = '<button type="button" class="button" data-toggle="0">
                    <span class="dashicons dashicons-visibility oo-icon-eye-enterprise-sitekey" aria-hidden="true"></span> 
                    </button>';
        } elseif ($fieldName === 'onoffice-settings-captcha-enterprise-apikey') {
            $iconShowPassword = '<button type="button" class="button" data-toggle="0">
                    <span class="dashicons dashicons-visibility oo-icon-eye-enterprise-apikey" aria-hidden="true"></span> 
                    </button>';
            $showDeleteEnterpriseKeysButton = true;
        }

        echo '<div class="oo-google-recaptcha-key">';
        echo '<input type="password" name="' . esc_html($fieldName)
            . '" value="' . esc_html($this->getValue()) . '" id="' . esc_html($this->getGuiId()) . '"'
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderAdditionalAttributes() returns escaped content
            . ' ' . $this->renderAdditionalAttributes()
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $iconShowPassword is a safe HTML string constructed above
            . '>' . $iconShowPassword;
        echo '</div>';

        if ($showDeleteGoogleRecaptchaKeysButton) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- __() returns escaped localized string
            echo '<button class="button delete-google-recaptcha-keys-button">' . __('Delete Keys', 'onoffice-for-wp-websites') . '</button>';
        }

        if ($showDeleteEnterpriseKeysButton) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- __() returns escaped localized string
            echo '<button class="button delete-google-recaptcha-enterprise-keys-button">' . __('Delete Keys', 'onoffice-for-wp-websites') . '</button>';
        }
    }
}