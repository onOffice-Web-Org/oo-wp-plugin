<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 *
 *    Copyright (C) 2016  onOffice Software AG
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

include(ONOFFICE_PLUGIN_DIR.'/templates.dist/fields.php');
$displayError = false;
?>


<?php if ($pForm->getEstateContextLabel()) { ?>
<h3>
	<?php
   /** @var \onOffice\WPlugin\Form $pForm */
    echo esc_html($pForm->getEstateContextLabel());
    ?>
</h3>
<?php } ?>

<form method="post" id="onoffice-form" class="oo-form oo-form-default" novalidate>
	<input type="hidden" name="oo_formid" value="<?php echo esc_attr($pForm->getFormId()); ?>">
	<input type="hidden" name="oo_formno" value="<?php echo esc_attr($pForm->getFormNo()); ?>">
	<?php wp_nonce_field('onoffice_form_' . esc_attr($pForm->getFormId()), 'onoffice_nonce', false); ?>
	<?php if ( isset( $estateId ) ) : ?>
	<input type="hidden" name="Id" value="<?php echo esc_attr($estateId); ?>">
	<?php endif; ?>

	<?php

if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_SUCCESS) {
	echo '<p role="status">'.esc_html__('Thank you for your inquiry. We will get back to you as soon as possible.', 'onoffice-for-wp-websites').'</p>';
} else {
	if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_ERROR) {
		echo '<p role="status">'.esc_html__('An error has occurred. Please check your details.', 'onoffice-for-wp-websites').'</p>';
	} elseif ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_REQUIRED_FIELDS_MISSING) {
		echo '<p role="status">'.esc_html__('Not all mandatory fields have been filled out. Please check your entries.', 'onoffice-for-wp-websites').'</p>';
		$displayError = true;
	} elseif ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_RECAPTCHA_SPAM) {
		echo '<p role="status">'.esc_html__('Spam recognized!', 'onoffice-for-wp-websites').'</p>';
	}
	$firstRequired = false;
	$hasRequiredFields = false;

	foreach ($pForm->getInputFields() as $input => $table) {
		if (
			!in_array($input, array('Id', 'gdprcheckbox', 'message')) &&
			$pForm->isRequiredField($input)
		) {
			$hasRequiredFields = true;
			break;
		}
	}

	if ($hasRequiredFields) {
		echo '<div class="oo-form-required" aria-hidden="true">' . esc_html__('* Mandatory fields', 'onoffice-for-wp-websites') . '</div>';
	}
	
	/* @var $pForm \onOffice\WPlugin\Form */
	foreach ( $pForm->getInputFields() as $input => $table ) {

		$isRequired = $pForm->isRequiredField( $input );
		$addition   = $isRequired ? '<span class="oo-visually-hidden">'.esc_html__('Pflichtfeld', 'onoffice-for-wp-websites').'</span><span aria-hidden="true">*</span>' : '';
	
		if ( in_array( $input, array( 'Id' ) ) ) {
			continue;
		}
		if ( in_array( $input, array( 'gdprcheckbox' ) ) ) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderFormField returns escaped HTML
            echo '<label><span class="oo-label-text ' . ($displayError && $isRequired ? ' displayerror' : '') . '">'.renderFormField( 'gdprcheckbox', $pForm );
            echo esc_html($pForm->getFieldLabel( 'gdprcheckbox' )) .' '. wp_kses_post($addition).'</span></label>';
            continue;
        }
		if ( in_array( $input, array( 'message' ) ) ) {
			$isRequiredMessage = $pForm->isRequiredField( 'message' );
			$additionMessage   = $isRequiredMessage ? '<span class="oo-visually-hidden">'.esc_html__('Pflichtfeld', 'onoffice-for-wp-websites').'</span><span aria-hidden="true">*</span>' : '';
			$isHiddenField = $pForm->isHiddenField('message');
			$errorMessage = esc_html__('Please enter a text.', 'onoffice-for-wp-websites');
			$errorHtml = renderErrorHtml($errorMessage, $isRequiredMessage);

			 if (!$isHiddenField) {
                echo  '<label class="' . ($displayError && $isRequired ? ' displayerror' : '') . '">'.esc_html($pForm->getFieldLabel( 'message' ));
                echo  ' '.wp_kses_post($additionMessage);
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $errorHtml is already escaped from renderErrorHtml
                echo  '<textarea name="message" data-rule="text" autocomplete="off"' . ($isRequiredMessage ? ' required aria-required="true" aria-invalid="false"' : '') . '>' . esc_textarea($pForm->getFieldValue('message')) . '</textarea>'.$errorHtml.'</label>';
            } else {
                echo '<input type="hidden" name="message" value="' . esc_attr($pForm->getFieldValue('message')) . '">';
            }
            continue;
		}
	
		$isHiddenField = $pForm->isHiddenField($input);
		$label = esc_html($pForm->getFieldLabel($input)) . ' ' . wp_kses_post($addition);

		if (\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_SINGLESELECT== $pForm->getFieldType($input)) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderFormField returns escaped HTML
            echo !$isHiddenField ? '<div class="oo-single-select"><label for="'.esc_attr($input).'-ts-control"><span class="oo-label-text' . ($displayError && $isRequired ? ' displayerror' : '') . '">'.$label.'</span></label>' . renderFormField($input, $pForm).'</div>' : renderFormField($input, $pForm);
		} else {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderFormField returns escaped HTML
            echo !$isHiddenField ? '<label><span class="oo-label-text' . ($displayError && $isRequired ? ' displayerror' : '') . '">'.$label . renderFormField($input, $pForm).'</span></label>' : renderFormField($input, $pForm);
		}
	}
?>

<?php
	include(ONOFFICE_PLUGIN_DIR.'/templates.dist/form/formsubmit.php');
}
?>
</form>
