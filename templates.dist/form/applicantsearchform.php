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

$pathComponents = [ONOFFICE_PLUGIN_DIR, 'templates.dist', 'fields.php'];
require(implode(DIRECTORY_SEPARATOR, $pathComponents));
$displayError = false;
?>
<form method="post" id="onoffice-form" class="oo-form oo-form-applicantsearch" data-applicant-form-id="<?php echo esc_attr($pForm->getFormId()); ?>" novalidate>

	<input type="hidden" name="oo_formid" value="<?php echo esc_attr($pForm->getFormId()); ?>">
    <input type="hidden" name="oo_formno" value="<?php echo esc_attr($pForm->getFormNo()); ?>">
	<?php wp_nonce_field('onoffice_form_' . esc_attr($pForm->getFormId()), 'onoffice_nonce', false); ?>
	<?php if ( isset( $currentEstate ) ) : ?>
	<input type="hidden" name="Id" value="<?php echo esc_attr($currentEstate['Id']); ?>">
	<?php endif; ?>

<?php

$selectTypes = array(
		\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_MULTISELECT,
		\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_SINGLESELECT,
	);

if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_ERROR) {
	echo '<p role="status">'.esc_html__('An error has occurred. Please check your details.', 'onoffice-for-wp-websites').'</p>';
} elseif ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_REQUIRED_FIELDS_MISSING) {
	echo '<p role="status">'.esc_html__('Not all mandatory fields have been filled out. Please check your entries.', 'onoffice-for-wp-websites').'</p>';
	$displayError = true;
} elseif ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_RECAPTCHA_SPAM) {
	echo '<p role="status">'.esc_html__('Spam recognized!', 'onoffice-for-wp-websites').'</p>';
}

/* @var $pForm \onOffice\WPlugin\Form */
foreach ( $pForm->getInputFields() as $input => $table ) {
	if ( in_array( $input, array('message', 'Id') ) ) {
		continue;
	}

	$isRequired = $pForm->isRequiredField( $input );
	$addition   = $isRequired ? '<span class="oo-visually-hidden">'.esc_html__('Pflichtfeld', 'onoffice-for-wp-websites').'</span><span aria-hidden="true">*</span>' : '';
	$inputAddition = $isRequired ? ' required' : '';
	$label = esc_html($pForm->getFieldLabel($input)) . ' ' . wp_kses_post($addition);
	
	$permittedValues = $pForm->getPermittedValues( $input, true );

	if ($input === 'Umkreis') {
		echo '<fieldset>'
			.'<legend>'.esc_html__('search within distance of:', 'onoffice-for-wp-websites').'</legend>';

		foreach ($pForm->getUmkreisFields() as $key => $values) {

			if (in_array($values['type'], $selectTypes)) {
				$permittedValues = $values['permittedvalues'];

				echo '<select class="custom-single-select" size="1" name="'.esc_attr($key).'">';
				echo '<option value="">'.esc_html('not specified').'</option>';

				foreach ( $permittedValues as $countryCode => $countryName ) {
					echo '<option value="'.esc_attr($countryCode).'">'
						.esc_html($countryName).'</option>';
				}

				echo '</select>';
			} else {
				
				echo '<input type="text" name="'.esc_attr($key).'" value="'
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $inputAddition contains safe attribute string
                    .esc_attr($pForm->getFieldValue( $key )).'"'.$inputAddition.'>';
			}
		}

		echo '</fieldset>';
		continue;
	}

	if ($input === 'regionaler_zusatz') {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $label contains escaped HTML
		 echo '<label><span class="oo-label-text ' . ($displayError && $isRequired ? ' displayerror' : '') . '">'.$label.'<select class="custom-single-select" size="1" name="'.esc_attr($input).'">';
		$pRegionController = new \onOffice\WPlugin\Region\RegionController();
		if ($permittedValues === null) {
			$regions = $pRegionController->getRegions();
		} else {
			$regions = $pRegionController->getParentRegionsByChildRegionKeys(array_keys($permittedValues));
		}
		$selectedValue = $pForm->getFieldValue( $input, true );
		foreach ($regions as $pRegion) {
			/* @var $pRegion Region */
			printRegion( $pRegion, [$selectedValue] );
		}
		echo '</select></label>';
	} else {

		if (\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_SINGLESELECT== $pForm->getFieldType($input)) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $label contains escaped HTML and renderFormField returns escaped HTML
            echo '<div class="oo-single-select"><label for="'.esc_attr($input).'-ts-control"><span class="oo-label-text' . ($displayError && $isRequired ? ' displayerror' : '') . '">'.$label.'</span></label>'.renderFormField($input, $pForm).'</div>';
		} else {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $label contains escaped HTML and renderFormField returns escaped HTML
			echo '<div class="oo-single-select"><label><span class="oo-label-text' . ($displayError && $isRequired ? ' displayerror' : '') . '">'.$label.renderFormField($input, $pForm, false).'</span></label></div>';
		}
	}
}

$pForm->setGenericSetting('submitButtonLabel', esc_html__('Search for Prospective Buyers', 'onoffice-for-wp-websites'));
include(ONOFFICE_PLUGIN_DIR.'/templates.dist/form/formsubmit.php');
echo '<svg viewBox="0 0 30 30" id="spinner"></svg>';

echo '<br>';

if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_SUCCESS) {
	$applicants = $pForm->getResponseFieldsValues();
	$rangeFields = array_keys($pForm->getSearchcriteriaRangeInfos());
	$umkreisFields = $pForm->getUmkreisFields();
	$countResults = $pForm->getCountAbsolutResults();
	echo '<br><span>'.esc_html(
			/* translators: %s will be replaced with a number. */
			sprintf(_n(
				'%s Prospective Buyer', '%s Prospective Buyers', $countResults, 'onoffice-for-wp-websites'),
					number_format_i18n($countResults))).'</span><br>';

	foreach ($applicants as $address => $searchdata) {
		echo '<br>';
		/* translators: %s will be replaced with a customer reference number. */
		echo '<span>'.esc_html(sprintf(__('Customer ref. number %s', 'onoffice-for-wp-websites'), $address)).'</span>';
		echo '<br>';
		$umkreis = array();

		foreach ($searchdata as $name => $value) {
			if (in_array($name, $rangeFields)) {
				$realName = $pForm->getFieldLabel($name);

				if (is_array($value)) {
					if ($value[0] > 0) {
						echo esc_html($realName).' min. '.esc_html($value[0]).'<br>';
					}

					if ($value[1] > 0) {
						echo esc_html($realName).' max. '.esc_html($value[1]);
					}
					echo '<br>';
					continue;
				}
			} elseif (in_array($name, array_keys($umkreisFields))) {
				$typeCurrentInput = $umkreisFields[$name]['type'];

				$realName = $umkreisFields[$name]['label'];
				$umkreis[$realName] = $value;

				if ($name == 'range' && $value > 0) {
					$umkreis[$realName] .= esc_html('km distance');
				}
			} else {
				$realName = $pForm->getFieldLabel($name);
				$typeCurrentInput = $pForm->getFieldType( $name );
			}

			if (in_array($pForm->getFieldType($name), $selectTypes) &&
				$name !== 'regionaler_zusatz') {
				if (in_array($typeCurrentInput, $selectTypes)) {
					$permittedValues = $pForm->getPermittedValues($name);

					if (!is_array($value)) {
						$value = $permittedValues[$value];
					} else {
						// multiple values selected in search criteria
						$value = implode(', ', array_intersect_key($permittedValues, array_flip($value)));
					}
				}
			} else if ($name === 'regionaler_zusatz') {
				$pRegionController = new \onOffice\WPlugin\Region\RegionController();

				$pRegion = $pRegionController->getRegionByKey(array_pop($value));
				/* @var $pRegion \onOffice\WPlugin\Region\Region */
				if ($pRegion !== null) {
					$value = esc_html($pRegion->getName());
				}
			}

			echo '<span>'.esc_html($realName).': '.(is_array($value) ? esc_html(implode(', ', $value)) : esc_html($value)).'</span><br>';
		}

		if (count($umkreis) > 0) {
			echo '<span><i>'.esc_html(implode(' ', array_values($umkreis))).'</i></span><br>';
		}
	}
}

?>
</form>