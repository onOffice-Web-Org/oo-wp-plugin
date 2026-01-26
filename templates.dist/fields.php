<?php

if ( ! defined( 'ABSPATH' ) ) exit;

use onOffice\WPlugin\Region\Region;
use onOffice\WPlugin\Region\RegionController;
use onOffice\WPlugin\Types\FieldTypes;

if (!function_exists('printRegion')) {
    function printRegion(onOffice\WPlugin\Region\Region $pRegion, $selected = array(), $level = 0)
    {
        $prefix = str_repeat('-', $level);
        $selectStr = (in_array($pRegion->getId(), $selected, false) ? ' selected' : '');
        echo '<option value="' . esc_attr($pRegion->getId()) . '" ' . esc_attr($selectStr) . '>'
            . esc_html($prefix) . ' ' . esc_html($pRegion->getName()) . '</option>';
        foreach ($pRegion->getChildren() as $pRegionChild) {
            printRegion($pRegionChild, $selected, $level + 1);
        }
    }
}

if (!function_exists('printCountry')) {
    function printCountry($values, $selectedValue)
    {
        echo '<option value="">' . esc_html__('Choose country', 'onoffice-for-wp-websites') . '</option>';
        foreach ($values as $key => $name) {
            $selected = '';
            if ($key == $selectedValue) {
                $selected = ' selected';
            }
            echo '<option value="' . esc_attr($key) . '"' . esc_attr($selected) . '>' . esc_html($name) . '</option>';
        }
    }
}

if (!function_exists('renderFieldEstateSearch')) {
	function renderFieldEstateSearch(string $inputName, array $properties)
	{
		$multiSelectableTypes = array(
			FieldTypes::FIELD_TYPE_SINGLESELECT,
			FieldTypes::FIELD_TYPE_MULTISELECT,
		);

		$fieldLabel = $properties['label'];

		$selectedValue = $properties['value'];
		$inputType = 'type="text" ';
		if (in_array($properties['type'], [onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_FLOAT, onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_INTEGER])) {
			$inputType = 'type="number" step="1" ';
		} elseif ($properties['type'] === onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_DATE) {
			$inputType = 'type="date" ';
		}

		if ($inputName == "radius") {
			$inputType = 'type="number" step="1" ';
		}

		if (isset($properties['is-apply-thousand-separator'])) {
			$inputType = 'type="text" class="apply-thousand-separator-format" data-step="1" ';
		}

		if ($properties['type'] === FieldTypes::FIELD_TYPE_BOOLEAN) {
            echo '<fieldset><legend>' . esc_html($fieldLabel) . '</legend>
        <input type="radio" id="' . esc_attr($inputName) . '_u" name="' . esc_attr($inputName) . '" value="u"
            ' . ($selectedValue === null ? ' checked' : '') . '>
        <label for="' . esc_attr($inputName) . '_u">' . esc_html__('Not Specified', 'onoffice-for-wp-websites') . '</label>
        <input type="radio" id="' . esc_attr($inputName) . '_y" name="' . esc_attr($inputName) . '" value="y"
            ' . ($selectedValue === true  ? 'checked' : '') . '>
        <label for="' . esc_attr($inputName) . '_y">' . esc_html__('Yes', 'onoffice-for-wp-websites') . '</label>
        <input type="radio" id="' . esc_attr($inputName) . '_n" name="' . esc_attr($inputName) . '" value="n"
            ' . ($selectedValue === false ? 'checked' : '') . '>
        <label for="' . esc_attr($inputName) . '_n">' . esc_html__('No', 'onoffice-for-wp-websites') . '</label>
      </fieldset>';
		} elseif (($inputName === 'ort' || $inputName === 'Ort') && !empty($properties['permittedvalues'])) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped in renderCityField
            echo renderCityField($inputName, $properties);
		}  elseif (
			in_array($properties['type'], $multiSelectableTypes) &&
			$inputName !== 'regionaler_zusatz' &&
			$inputName != 'country'
		) {
			$permittedValues = $properties['permittedvalues'];
			$htmlOptions = '';
			foreach ($permittedValues as $key => $value) {
				if (is_array($selectedValue)) {
					$isSelected = in_array($key, $selectedValue, true);
				} else {
					$isSelected = $selectedValue == $key;
				}
				$htmlOptions .= '<option value="' . esc_attr($key) . '"' . ($isSelected ? ' selected' : '') . '>' . esc_html($value) . '</option>';
			}
			$htmlSelect = '<label for="' . esc_attr($inputName) . '-ts-control">' . esc_html($fieldLabel) . '</label><select id="' . esc_attr($inputName) . '" aria-hidden="true" class="custom-multiple-select-tom form-control" autocomplete="off" name="' . esc_attr($inputName) . '[]" multiple="multiple">';
			$htmlSelect .= $htmlOptions;
			$htmlSelect .= '</select>';
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- All components are already escaped
			echo $htmlSelect;
		} elseif ($inputName === 'regionaler_zusatz') {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped in renderRegionalAddition
			echo '<label for="' . esc_attr($inputName) . '[]-ts-control">' . esc_html($fieldLabel) . '</label>' . renderRegionalAddition($inputName, $selectedValue ?? [], true, $properties['label'], false, $properties['permittedvalues'] ?? null);
		} elseif ($inputName === 'country') {
			echo '<label for="' . esc_attr($inputName) . '-ts-control">' . esc_html($fieldLabel) . '</label><select id="' . esc_attr($inputName) . '" aria-hidden="true" class="custom-single-select-tom" autocomplete="off" size="1" name="' . esc_attr($inputName) . '">';
			printCountry($properties['permittedvalues'], $selectedValue);
			echo '</select>';
		} elseif (
			FieldTypes::isNumericType($properties['type']) ||
			FieldTypes::FIELD_TYPE_DATETIME === $properties['type'] ||
			FieldTypes::FIELD_TYPE_DATE === $properties['type']
		) {
			echo '<fieldset class="oo-searchrange">';
			echo '<legend>' . esc_html($fieldLabel) . '</legend>';
			echo '<label for="' . esc_attr($inputName) . '__von">';
			esc_html_e('From: ', 'onoffice-for-wp-websites');
			//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $inputType is controlled and safe
			echo '<input id="' . esc_attr($inputName) . '__von" name="' . esc_attr($inputName) . '__von" ' . $inputType;
			echo 'value="' . esc_attr(isset($selectedValue[0]) ? $selectedValue[0] : '') . '"></label>';
			echo '<label for="' . esc_attr($inputName) . '__bis">';
			esc_html_e('Up to: ', 'onoffice-for-wp-websites');
			//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $inputType is controlled and safe
			echo '<input id="' . esc_attr($inputName) . '__bis" name="' . esc_attr($inputName) . '__bis" ' . $inputType;
			echo 'value="' . esc_attr(isset($selectedValue[1]) ? $selectedValue[1] : '') . '"></label></fieldset>';
		} else {
			$lengthAttr = !is_null($properties['length']) ?
				' maxlength="' . esc_attr($properties['length']) . '"' : '';
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $inputType is a controlled attribute string
			echo '<label>' . esc_html($fieldLabel) . '<input autocomplete="off" name="' . esc_attr($inputName) . '" ' . $inputType;
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $lengthAttr is a controlled attribute string
			echo 'value="' . esc_attr($selectedValue) . '"' . $lengthAttr . '></label>';
		}
	}
}

if (!function_exists('renderFormField')) {

	function renderErrorHtml(?string $errorMessage, bool $shouldDisplay): string {
        if (!empty($errorMessage) && $shouldDisplay) {
            return "<div class='error' aria-hidden='true' role='alert' aria-atomic='true'><p>" . esc_html($errorMessage) . "</p></div>";
        }
        return '';
    }
	function renderFormField(string $fieldName, onOffice\WPlugin\Form $pForm, bool $searchCriteriaRange = true): string
	{
		$output = '';
		$autocomplete = null;
		$typeCurrentInput = $pForm->getFieldType($fieldName);
		$isHiddenField = $pForm->isHiddenField($fieldName);
	
		if ($isHiddenField) {
			$name = esc_html($fieldName);
			$value = $pForm->getFieldValue($fieldName, true);

			if ($typeCurrentInput === FieldTypes::FIELD_TYPE_BOOLEAN) {
				$value = empty($value) ? 'u' : ($value == true ? 'y' : 'n');
			}

			if ($typeCurrentInput === FieldTypes::FIELD_TYPE_MULTISELECT) { 
				$value = is_array($value) ? implode(', ', $value) : $value;
			}

			return '<input type="hidden" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '">';
		}


		switch ($fieldName) {
			case 'Briefanrede':
			case 'Anrede':
				$autocomplete = "honorific-prefix"; break;
			case "Titel": $autocomplete = "honorific-prefix"; break;
			case "Vorname": $autocomplete = "given-name"; break;
			case "Name": $autocomplete = "family-name"; break;
			case "Strasse": $autocomplete = "street-address"; break;
			case "Plz":
			case 'plz': $autocomplete = "postal-code"; break;
			case "Ort": $autocomplete = "address-level2"; break;
			case "Zusatz1": $autocomplete = "organization"; break;
			case 'jobTitle':
			case 'jobPosition':
				$autocomplete ="organization-title"; break;
			case "Land": $autocomplete = "country-name"; break;
			case "Geburtsdatum": $autocomplete = "bday"; break;
			case "Homepage": $autocomplete = "url"; break;
			case "Telefon1": $autocomplete = "tel"; break;
			case "Email": $autocomplete = "email"; break;
			default: $autocomplete = "off";
		}
		
		if ($autocomplete !== null) {
			$autocompleteAttribute = ' autocomplete="' . htmlspecialchars($autocomplete) . '"';
		}
		
		$isRequired = $pForm->isRequiredField($fieldName);
		$addition   = $isRequired ? '<span class="oo-visually-hidden">'.esc_html__('Pflichtfeld', 'onoffice-for-wp-websites').'</span><span aria-hidden="true">*</span>' : '';
		$permittedValues = $pForm->getPermittedValues($fieldName, true);
		$selectedValue = $pForm->getFieldValue($fieldName, true);
		$isRangeValue = $pForm->isSearchcriteriaField($fieldName) && $searchCriteriaRange;
		$fieldLabel = $pForm->getFieldLabel($fieldName, true);
		$isApplyThousandSeparatorField = $pForm->isApplyThousandSeparatorField($fieldName);
		$errorMessageDisplay = false;
		$errorHtml = '';
		$ariaLabel = '';

		$requiredAttribute = "";
		if ($isRequired) {
			$requiredAttribute = "required aria-required='true' aria-invalid='false'";
			$requiredAttributeSelect = "required";
			$errorMessageDisplay = true;
		}

		if ($fieldName == 'range') {
			$typeCurrentInput = onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_INTEGER;
		}
				
		switch ($fieldName) {
			case "plz": $ariaLabel = 'aria-label="' . esc_html__('Postal code of the property', 'onoffice-for-wp-websites') . '"'; break;
			case "Plz": $ariaLabel = 'aria-label="' . esc_html__('Postal code', 'onoffice-for-wp-websites') . '"'; break;
		}

		if (\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_SINGLESELECT == $typeCurrentInput) {
			$errorMessage = esc_html__('Please select at least one option.', 'onoffice-for-wp-websites');
			$errorHtml = renderErrorHtml($errorMessage, $errorMessageDisplay);
			/* translators: %s: field label */
			$output .= '<select data-placeholder="' . esc_html(sprintf(__('Choose %s', 'onoffice-for-wp-websites'), $fieldLabel)) . '" id="'.$fieldName.'" aria-hidden="true" class="custom-single-select-tom" autocomplete="off" size="1" name="' . esc_html($fieldName) . '" ' . $requiredAttribute . '>';
			/* translators: %s: field label */
			$output .= '<option value="">' . esc_html(sprintf(__('Choose %s', 'onoffice-for-wp-websites'), $fieldLabel)) . '</option>';
			foreach ($permittedValues as $key => $value) {
				if (is_array($selectedValue)) {
					$isSelected = in_array($key, $selectedValue, true);
				} else {
					$isSelected = $selectedValue == $key;
				}
				$output .= '<option value="' . esc_attr($key) . '"' . ($isSelected ? ' selected' : '') . '>' . esc_html($value) . '</option>';
			}
			$output .= '</select>' . $errorHtml;
		
		} elseif ($fieldName === 'regionaler_zusatz') {
			if (!is_array($selectedValue)) {
				$selectedValue = [];
			}
			$output .= renderRegionalAddition($fieldName, $selectedValue, true, $fieldLabel, $isRequired, $permittedValues ?? null);
		} elseif (
			\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_MULTISELECT === $typeCurrentInput ||
			(\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_SINGLESELECT === $typeCurrentInput &&
				$isRangeValue)
		) {

			$postfix = '';

			if (\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_MULTISELECT === $typeCurrentInput) {
				$postfix = '[]';
			}

			$htmlOptions = '';
			foreach ($permittedValues as $key => $value) {
				if (is_array($selectedValue)) {
					$isSelected = in_array($key, $selectedValue, true);
				} else {
					$isSelected = $selectedValue == $key;
				}
				$htmlOptions .= '<option value="' . esc_attr($key) . '"' . ($isSelected ? ' selected' : '') . '>' . esc_html($value) . '</option>';
			}
			$errorMessage = esc_html__('Please select at least one option.', 'onoffice-for-wp-websites');
			$errorHtml = renderErrorHtml($errorMessage, $errorMessageDisplay);

			$output = '<select aria-hidden="true" tabindex="-1" class="custom-multiple-select-tom form-control" autocomplete="off" name="' . esc_html($fieldName) . '[]" multiple="multiple" ' . $requiredAttribute . '>';
			$output .= $htmlOptions;
			$output .= '</select>'.$errorHtml;
		} else {
			$inputType = 'type="text"';
			$value = 'value="' . esc_attr($pForm->getFieldValue($fieldName, true)) . '"';
			$errorMessage = esc_html__('Please enter a text.', 'onoffice-for-wp-websites');

			if ($typeCurrentInput == onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_BOOLEAN) {
				$inputType = 'type="checkbox"';

				if (($fieldName == 'gdprcheckbox') || ($fieldName == 'AGB_akzeptiert'))
				{
					$errorMessage = esc_html__('Please agree to the terms and conditions.', 'onoffice-for-wp-websites');
				}
				else 
				{
					$errorMessage = esc_html__('Please select this option.', 'onoffice-for-wp-websites');
				}
			
				$value = 'value="y" ' . ($pForm->getFieldValue($fieldName, true) == 1 ? 'checked="checked"' : '');
			} elseif (
				$typeCurrentInput === onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_FLOAT ||
				$typeCurrentInput === 'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:float'
			) {
				$inputType = 'type="number" step="1" ';
			} elseif (
				$typeCurrentInput === onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_INTEGER ||
				$typeCurrentInput === 'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:decimal'
			) {
				$inputType = 'type="number" step="1" ';
				$errorMessage = esc_html__('Please enter a text.', 'onoffice-for-wp-websites');

			} elseif (
				$typeCurrentInput === FieldTypes::FIELD_TYPE_DATE ||
				$typeCurrentInput === FieldTypes::FIELD_TYPE_DATATYPE_DATE
			) {
				$inputType = 'type="date"';
			} elseif (
				$typeCurrentInput === FieldTypes::FIELD_TYPE_DATETIME
			) {
				$inputType = 'type="datetime-local" step="1" ';
			}

			if ($isApplyThousandSeparatorField) {
				$inputType = 'type="text" class="apply-thousand-separator-format"';
			}

			if ($fieldName == 'Email') {
				$inputType = 'type="email"';
				$errorMessage = esc_html__('Please enter a valid e-mail address.', 'onoffice-for-wp-websites');
			}

			if (
				$isRangeValue && $pForm->inRangeSearchcriteriaInfos($fieldName) &&
				count($pForm->getSearchcriteriaRangeInfosForField($fieldName)) > 0
			) {
				$errorHtml = renderErrorHtml($errorMessage, $errorMessageDisplay);
				foreach ($pForm->getSearchcriteriaRangeInfosForField($fieldName) as $key => $rangeDescription) {
                    $value = 'value="' . esc_attr($pForm->getFieldValue($key, true)) . '"';
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $inputType and $requiredAttribute are controlled attribute strings
					$output .= '<label>' . esc_html($rangeDescription) . ' ' . wp_kses_post($addition) . '<input autocomplete="off" ' . $inputType . ' ' . $requiredAttribute . ' name="' . esc_attr($key) . '" '
                        . $value . ' placeholder="' . esc_attr($rangeDescription) . '">' . $errorHtml . '</label>';
                }
			} elseif ($typeCurrentInput === FieldTypes::FIELD_TYPE_DATATYPE_TINYINT) {
				$output = '<fieldset>
					<input type="radio" id="' . esc_attr($fieldName) . '_u" name="' . esc_attr($fieldName) . '" value=""
						' . ($selectedValue === '' ? ' checked' : '') . '>
					<label for="' . esc_attr($fieldName) . '_u">' . esc_html__('Not Specified', 'onoffice-for-wp-websites') . '</label>
					<input type="radio" id="' . esc_attr($fieldName) . '_y" name="' . esc_attr($fieldName) . '" value="1"
						' . ($selectedValue === '1' ? 'checked' : '') . '>
					<label for="' . esc_attr($fieldName) . '_y">' . esc_html__('Yes', 'onoffice-for-wp-websites') . '</label>
					<input type="radio" id="' . esc_attr($fieldName) . '_n" name="' . esc_attr($fieldName) . '" value="0"
						' . ($selectedValue === '0' ? 'checked' : '') . '>
					<label for="' . esc_attr($fieldName) . '_n">' . esc_html__('No', 'onoffice-for-wp-websites') . '</label>
					</fieldset>';
			} else {
				$errorHtml = renderErrorHtml($errorMessage, $errorMessageDisplay);
				$output .= '<input ' . $inputType . $requiredAttribute . ' name="' . esc_attr($fieldName) . '" ' . $value . $autocompleteAttribute .'>'.$errorHtml;
			}
		}
		return $output;
		
	}
}


if (!function_exists('renderRegionalAddition')) {
	function renderRegionalAddition(string $inputName, array $selectedValue, bool $multiple, string $fieldLabel, bool $isRequired, array $permittedValues = null): string
	{
		$output = '';
		$name = esc_attr($inputName) . ($multiple ? '[]' : '');
		$multipleAttr = $multiple ? 'multiple size="5"' : 'size="1"';

		$requiredAttribute = "";
		if ($isRequired) {
			$requiredAttribute = "required aria-required='true' aria-invalid='false'";
		}

		$output .= '<select id="' . $name . '" class="custom-single-select-tom oo-regions" aria-hidden="true" autocomplete="off" name="' . $name . '" ' . $multipleAttr . ' ' . $requiredAttribute . '>';
		$pRegionController = new RegionController();

		if ($permittedValues !== null) {
			$regions = $pRegionController->getParentRegionsByChildRegionKeys(array_keys($permittedValues));
		} else {
			$regions = $pRegionController->getRegions();
		}
		ob_start();
		/* translators: %s: field label for regional addition dropdown */
		echo '<option value="">' . esc_html(sprintf(__('Choose %s', 'onoffice-for-wp-websites'), $fieldLabel)) . '</option>';
		foreach ($regions as $pRegion) {
			/* @var $pRegion Region */
			printRegion($pRegion, $selectedValue ?? []);
		}
		$output .= ob_get_clean();
		$output .= '</select>';
		return $output;
	}
}

if (!function_exists('renderCityField')) {
	function renderCityField(string $inputName, array $properties): string
	{
		$fieldLabel = $properties['label'];
		$permittedValues = $properties['permittedvalues'];
		$htmlSelect = '<label for="' . esc_attr($inputName) . '-ts-control">' . esc_html($fieldLabel) . '</label>';
		$htmlSelect .= '<select aria-hidden="true" class="custom-multiple-select-tom form-control" autocomplete="off" name="' . esc_attr($inputName) . '[]" multiple="multiple" aria-label="' . esc_attr($inputName) .'">';
		foreach ($permittedValues as $value) {
			$selected = '';
			if (is_array($properties['value']) && in_array($value, $properties['value'])) {
				$selected = 'selected';
			}
			$htmlSelect .='<option value="' . esc_attr($value) . '" ' . esc_attr($selected) . '>' . esc_attr($value) . '</option>';
		}
		$htmlSelect .= '</select>';

		return $htmlSelect;
	}
}