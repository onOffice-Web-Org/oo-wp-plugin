<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 *
 *    Copyright (C) 2018  onOffice GmbH
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

// Enterprise reCAPTCHA keys
$enterpriseSiteKey = get_option('onoffice-settings-captcha-enterprise-sitekey', '');
$enterpriseProjectId = get_option('onoffice-settings-captcha-enterprise-projectid', '');
$enterpriseApiKey = get_option('onoffice-settings-captcha-enterprise-apikey', '');
$hasEnterprise = !empty($enterpriseSiteKey) && !empty($enterpriseProjectId) && !empty($enterpriseApiKey);

// TODO: Remove later, when Enterprise reCAPTCHA is fully rolled out
$classicSiteKey = get_option('onoffice-settings-captcha-sitekey', '');
$hasClassic = !empty($classicSiteKey);

/** @var \onOffice\WPlugin\Form $pForm */
if ($pForm->needsReCaptcha() && ($hasEnterprise || $hasClassic)) {
    $formId = $pForm->getGenericSetting('formId');
    $pFormNo = $pForm->getFormNo();
    $buttonLabel = esc_html($pForm->getGenericSetting('submitButtonLabel'));

    // Enterprise reCAPTCHA (has priority)
    if ($hasEnterprise) {
?>
    <input type="hidden" name="g-recaptcha-response" id="recaptcha-token-<?php echo esc_attr($pFormNo); ?>" value="">
    <button type="submit" class="submit_button"><?php echo esc_html($buttonLabel); ?></button>
    <script>
        (function() {
            var formNo = <?php echo json_encode($pFormNo); ?>;
            var siteKey = <?php echo json_encode($enterpriseSiteKey); ?>;
            var form = document.querySelector('form[id^="onoffice-form"] input[name="oo_formno"][value="' + formNo + '"]')?.closest('form, #onoffice-form, .oo-form, #leadgeneratorform');
            var btn = form?.querySelector('.submit_button');
            var tokenInput = document.getElementById('recaptcha-token-' + formNo);
            
            if (!form || !btn || !tokenInput) return;
            
            async function handleSubmit(e) {
                e.preventDefault();
                if (!form.checkValidity()) { form.reportValidity(); return; }
                
                btn.disabled = true;
                btn.classList.add('onoffice-unclickable-form');
                
                try {
                    // Load script if needed
                    if (!document.getElementById('recaptcha-enterprise-script')) {
                        await new Promise(function(resolve, reject) {
                            var script = document.createElement('script');
                            script.id = 'recaptcha-enterprise-script';
                            script.src = 'https://www.google.com/recaptcha/enterprise.js?render=' + encodeURIComponent(siteKey);
                            script.onload = resolve;
                            script.onerror = reject;
                            document.head.appendChild(script);
                        });
                    }
                    
                    // Wait for grecaptcha
                    await new Promise(function(resolve) { grecaptcha.enterprise.ready(resolve); });
                    
                    // Get token
                    var token = await grecaptcha.enterprise.execute(siteKey, {action: 'submit_form'});
                    tokenInput.value = token;
                    
                    // Submit form
                    form.submit();
                    
                } catch(error) {
                    console.error('reCAPTCHA Enterprise error:', error);
                    btn.disabled = false;
                    btn.classList.remove('onoffice-unclickable-form');
                }
            }
            
            btn.addEventListener('click', handleSubmit);
        })();
    </script>
<?php
    } else {
        // TODO: Classic Recaptcha, remove later, when Enterprise reCAPTCHA is fully rolled out
?>
    <script>
        function submitForm<?php echo esc_js($pFormNo); ?>() {
            const selectorFormById = `form[id^="onoffice-form"] input[name="oo_formno"][value="<?php echo esc_js($pFormNo); ?>"]`;
            const form = document.querySelector(selectorFormById).closest('#onoffice-form, .oo-form, #leadgeneratorform');
            const submitButtonElement = form.querySelector('.submit_button');
            form.submit();
            submitButtonElement.disabled = true;
            submitButtonElement.classList.add('onoffice-unclickable-form');
        }
    </script>
    <div class="g-recaptcha"
        data-sitekey="<?php echo esc_attr($classicSiteKey); ?>" 
        data-callback="submitForm<?php echo esc_js($pFormNo); ?>" data-size="invisible">
    </div>
    <button class="submit_button"><?php echo esc_html($buttonLabel); ?></button>
    <script>
        (function() {
            const selectorFormById = `form[id^="onoffice-form"] input[name="oo_formno"][value="<?php echo esc_js($pFormNo); ?>"]`;
            const form = document.querySelector(selectorFormById).closest('#onoffice-form, .oo-form, #leadgeneratorform');
            const submitButtonElement = form.querySelector('.submit_button');
            if (typeof onOffice !== 'undefined' && onOffice.captchaControl) {
                onOffice.captchaControl(form, submitButtonElement);
            }
        })();
    </script>
<?php
    }
} else {
?>

<input type="submit" value="<?php echo esc_attr($pForm->getGenericSetting('submitButtonLabel')); ?>">

<?php
}