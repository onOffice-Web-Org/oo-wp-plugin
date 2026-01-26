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
<form method="post" id="onoffice-form" class="oo-form oo-form-owner" novalidate>

	<input type="hidden" name="oo_formid" value="<?php echo esc_attr($pForm->getFormId()); ?>">
    <input type="hidden" name="oo_formno" value="<?php echo esc_attr($pForm->getFormNo()); ?>">
	<?php wp_nonce_field('onoffice_form_' . esc_attr($pForm->getFormId()), 'onoffice_nonce', false); ?>
<?php

$addressValues = array();
$estateValues = array();
$hiddenValues  = array();

if ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_SUCCESS) {
	echo '<p role="status">'.esc_html__('Thank you for your inquiry. We will get back to you as soon as possible.', 'onoffice-for-wp-websites').'</p>';
} else {
	if ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_ERROR) {
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
		if ($pForm->isHiddenField($input) && $input !== 'message') {
			$hiddenValues []= renderFormField($input, $pForm);
			continue;
		}
		
		switch ($input) {
			case "ort": $fieldLabel = esc_html__('Ort der Immobilie', 'onoffice-for-wp-websites'); break;
			case "plz": $fieldLabel = esc_html__('PLZ der Immobilie', 'onoffice-for-wp-websites'); break;
			case "strasse": $fieldLabel = esc_html__('Straße der Immobilie', 'onoffice-for-wp-websites'); break;
			case "hausnummer": $fieldLabel = esc_html__('Hausnummer der Immobilie', 'onoffice-for-wp-websites'); break;
			default: $fieldLabel = $pForm->getFieldLabel($input);
		}

		$isRequired = $pForm->isRequiredField($input);
		$addition   = $isRequired ? '<span class="oo-visually-hidden">'.esc_html__('Pflichtfeld', 'onoffice-for-wp-websites').'</span><span aria-hidden="true">*</span>' : '';
		
		$isHiddenField = $pForm->isHiddenField($input);
		$label = esc_html($pForm->getFieldLabel($input)) . ' ' . wp_kses_post($addition);

		if ((\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_SINGLESELECT== $pForm->getFieldType($input))) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $label contains escaped HTML and renderFormField returns escaped HTML
            $line =	 !$isHiddenField ? '<div class="oo-single-select"><label for="'.esc_attr($input).'-ts-control"><span class="oo-label-text' . ($displayError && $isRequired ? ' displayerror' : '') . '">'.$label.'</span></label>' . renderFormField($input, $pForm).'</div>' : renderFormField($input, $pForm);
		} else if ((\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_MULTISELECT== $pForm->getFieldType($input))) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $label contains escaped HTML and renderFormField returns escaped HTML
            $line =	 !$isHiddenField ? '<div class="oo-multi-select"><label for="'.esc_attr($input).'-ts-control"><span class="oo-label-text' . ($displayError && $isRequired ? ' displayerror' : '') . '">'.$label.'</span></label>' . renderFormField($input, $pForm).'</div>' : renderFormField($input, $pForm);
		}
		
		else {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $label contains escaped HTML returns escaped HTML
            $line = '<label><span class="oo-label-text' . ($displayError && $isRequired ? ' displayerror' : '') . '">'.$label;
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderFormField returns escaped HTML
			$line .= renderFormField($input, $pForm).'</span></label>';
		}

		if ( in_array( $input, array( 'gdprcheckbox' ) ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderFormField returns escaped HTML
            $line = '<label><span class="oo-label-text' . ($displayError && $isRequired ? ' displayerror' : '') . '">'.renderFormField( 'gdprcheckbox', $pForm );
			$line .= esc_html($pForm->getFieldLabel( 'gdprcheckbox' )) .' '. wp_kses_post($addition).'</span></label>';
		}
		if ( in_array( $input, array( 'message' )) ) {
			$isRequiredMessage = $pForm->isRequiredField( 'message' );
			$additionMessage = $isRequiredMessage ? '<span class="oo-visually-hidden">'.esc_html__('Pflichtfeld', 'onoffice-for-wp-websites').'</span><span aria-hidden="true">*</span>' : '';
			$isHiddenField = $pForm->isHiddenField('message');
			$errorMessage = esc_html__('Please enter a text.', 'onoffice-for-wp-websites');
			$errorHtml = renderErrorHtml($errorMessage, $isRequiredMessage);
			
			if (!$isHiddenField) {
				$line = '<label class="' . ($displayError && $isRequired ? ' displayerror' : '') . '">'.$pForm->getFieldLabel( 'message' );
				$line .= ' '.$additionMessage;
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $errorHtml is already escaped from renderErrorHtml
                $line .= '<textarea name="message" autocomplete="off"' . ($isRequiredMessage ? ' required aria-required="true" aria-invalid="false"' : '') . '>' . esc_textarea($pForm->getFieldValue('message')) . '</textarea>'.$errorHtml.'</label>';

			} else {
				$line = '<input type="hidden" name="message" value="' . $pForm->getFieldValue('message') . '">';
			}
		}
		if ($table == 'address') {
			$addressValues []= $line;
		}

		if ($table == 'estate') {
			$estateValues []= $line;
		}

		if ($table == '') {
			$addressValues []= $line;
		}
	}

	echo '<h2>'.esc_html__('Your contact details', 'onoffice-for-wp-websites').'</h2>'
		.'<div>';
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $addressValues contains escaped HTML from renderFormField
	echo implode('', $addressValues);
	echo '</div>
		<h2>'.esc_html__('Information about your property', 'onoffice-for-wp-websites').'</h2>
		<div>';
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $estateValues contains escaped HTML from renderFormField
	echo implode('', $estateValues);
	echo '</div>';
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $hiddenValues contains escaped HTML from renderFormField
	echo implode($hiddenValues);

	include(ONOFFICE_PLUGIN_DIR.'/templates.dist/form/formsubmit.php');
}
?>

</form>