<?php

use onOffice\WPlugin\Region\Region;
use onOffice\WPlugin\Region\RegionController;
use onOffice\WPlugin\Types\FieldTypes;

if ( ! function_exists( 'printRegion') ) {
	function printRegion( onOffice\WPlugin\Region\Region $pRegion, $selected = array(), $level = 0 ) {
		$prefix = str_repeat( '-', $level );
		$selectStr = (in_array($pRegion->getId(), $selected, false) ? ' selected' : '');
		echo '<option value="'.esc_html( $pRegion->getId() ).'" '.$selectStr.'>'
			.$prefix.' '.esc_html( $pRegion->getName() ).'</option>';
		foreach ( $pRegion->getChildren() as $pRegionChild ) {
			printRegion($pRegionChild, $selected, $level + 1);
		}
	}
}

if ( ! function_exists( 'printCountry' )) {
	function printCountry ($values, $selectedValue)	{
		echo '<option value="">'.esc_html__('Choose country', 'onoffice').'</option>';
		foreach ($values as $key => $name)
		{
			$selected = null;
			if ($key == $selectedValue)
			{
				$selected = 'selected';
			}
			echo '<option value="'.esc_attr($key).'" '.$selected.'>'.esc_html($name).'</option>';
		}
	}
}

if (!function_exists('renderFieldEstateSearch')) {
	function renderFieldEstateSearch(string $inputName, array $properties) {
		$multiSelectableTypes = array(
			FieldTypes::FIELD_TYPE_SINGLESELECT,
			FieldTypes::FIELD_TYPE_MULTISELECT,
		);

		$selectedValue = $properties['value'];
		$inputType = 'type="text" ';
		if ($properties['type'] === onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_FLOAT) {
			$inputType = 'type="number" step="0.1" ';
		} elseif ($properties['type'] === onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_INTEGER) {
			$inputType = 'type="number" step="1" ';
		} elseif ($properties['type'] === onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_DATE) {
			$inputType = 'type="date" ';
		}

		if ($inputName == "radius")
		{
			$inputType = 'type="number" step="1" ';
		}

		if ( $properties['type'] === FieldTypes::FIELD_TYPE_BOOLEAN ) {
			echo '<br>';
			echo '<fieldset>
		<input type="radio" id="'.esc_attr($inputName).'_u" name="'.esc_attr($inputName).'" value="u"
			'.($selectedValue === null ? ' checked' : '').'>
		<label for="'.esc_attr($inputName).'_u">'.esc_html__('Not Specified', 'onoffice').'</label>
		<input type="radio" id="'.esc_attr($inputName).'_y" name="'.esc_attr($inputName).'" value="y"
			'.($selectedValue === true  ? 'checked' : '').'>
		<label for="'.esc_attr($inputName).'_y">'.esc_html__('Yes', 'onoffice').'</label>
		<input type="radio" id="'.esc_attr($inputName).'_n" name="'.esc_attr($inputName).'" value="n"
			'.($selectedValue === false ? 'checked' : '').'>
		<label for="'.esc_attr($inputName).'_n">'.esc_html__('No', 'onoffice').'</label>
	  </fieldset>';
		} elseif ( in_array($properties['type'], $multiSelectableTypes) &&
			$inputName !== 'regionaler_zusatz' &&
			$inputName != 'country') {
				$permittedValues = $properties['permittedvalues'];
			$htmlOptions = '';
			foreach ($permittedValues as $key => $value) {
				$htmlOptions .= '<option value="'.esc_attr($key).'">'.esc_html($value).'</option>';
			}
			$htmlSelect = '<select class="custom-multiple-select form-control" name="' . esc_html($inputName) . '[]" multiple="multiple">';
			$htmlSelect .= $htmlOptions;
			$htmlSelect .= '</select>';
			echo $htmlSelect;
		} elseif ( $inputName === 'regionaler_zusatz' ) {
			echo renderRegionalAddition($inputName, $selectedValue ?? [], true, $properties['label'], $properties['permittedvalues'] ?? null);
		}
		elseif ( $inputName === 'country' )	{
			echo '<select class="custom-single-select" size="1" name="'.esc_attr($inputName).'">';
			printCountry($properties['permittedvalues'], $selectedValue);
			echo '</select>';
		}
		elseif ( FieldTypes::isNumericType( $properties['type'] ) ||
			FieldTypes::FIELD_TYPE_DATETIME === $properties['type'] ||
			FieldTypes::FIELD_TYPE_DATE === $properties['type']) {
				esc_html_e('From: ', 'onoffice');
				echo '<input name="'.esc_attr($inputName).'__von" '.$inputType;
				echo 'value="'.esc_attr(isset($selectedValue[0]) ? $selectedValue[0] : '').'"><br>';
				esc_html_e('Up to: ', 'onoffice');
				echo '<input name="'.esc_attr($inputName).'__bis" '.$inputType;
				echo 'value="'.esc_attr(isset($selectedValue[1]) ? $selectedValue[1] : '').'"><br>';
			} else {
			$lengthAttr = !is_null($properties['length']) ?
				' maxlength="'.esc_attr($properties['length']).'"' : '';
			echo '<input name="'.esc_attr($inputName).'" '.$inputType;
			echo 'value="'.esc_attr($selectedValue).'"'.$lengthAttr.'><br>';
		}
	}
}

if (!function_exists('renderFormField')) {
	function renderFormField(string $fieldName, onOffice\WPlugin\Form $pForm, bool $searchCriteriaRange = true): string
	{
		$output = '';
		$typeCurrentInput = $pForm->getFieldType($fieldName);
		$isRequired = $pForm->isRequiredField($fieldName);
		$requiredAttribute = $isRequired ? 'required ' : '';
		$permittedValues = $pForm->getPermittedValues($fieldName, true);
		$selectedValue = $pForm->getFieldValue($fieldName, true);
		$isRangeValue = $pForm->isSearchcriteriaField($fieldName) && $searchCriteriaRange;
		$fieldLabel = $pForm->getFieldLabel($fieldName, true);

		if ($fieldName == 'range')
		{
			$typeCurrentInput = onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_INTEGER;
		}

		if (\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_SINGLESELECT == $typeCurrentInput) {
			$output .= '<select class="custom-single-select" size="1" name="'.esc_html($fieldName).'">';
			/* translators: %s will be replaced with the translated field name. */
			$output .= '<option value="">'.esc_html(sprintf(__('Choose %s', 'onoffice'), $fieldLabel)).'</option>';
			foreach ($permittedValues as $key => $value) {
				if (is_array($selectedValue)) {
					$isSelected = in_array($key, $selectedValue, true);
				} else {
					$isSelected = $selectedValue == $key;
				}
				$output .= '<option value="'.esc_attr($key).'"'.($isSelected ? ' selected' : '').'>'
					.esc_html($value).'</option>';
			}
			$output .= '</select>';
		} elseif ($fieldName === 'regionaler_zusatz') {
			if (!is_array($selectedValue))
			{
				$selectedValue = [];
			}
			$output .= renderRegionalAddition($fieldName, $selectedValue, true, $fieldLabel, $permittedValues ?? null);
		} elseif (\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_MULTISELECT === $typeCurrentInput ||
			(\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_SINGLESELECT === $typeCurrentInput &&
			$isRangeValue)) {

			$postfix = '';

			if (\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_MULTISELECT === $typeCurrentInput) {
				$postfix = '[]';
			}

			$htmlOptions = '';
			foreach ($permittedValues as $key => $value) {
				$htmlOptions .= '<option value="'.esc_attr($key).'">'.esc_html($value).'</option>';
			}
			$output = '<select class="custom-multiple-select form-control" name="' . esc_html($fieldName) . '[]" multiple="multiple">';
			$output .= $htmlOptions;
			$output .= '</select>';
		} else {
			$inputType = 'type="text" ';
			$value = 'value="'.esc_attr($pForm->getFieldValue($fieldName, true)).'"';

			if ($typeCurrentInput == onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_BOOLEAN) {
				$inputType = 'type="checkbox" ';
				$value = 'value="y" '.($pForm->getFieldValue($fieldName, true) == 1 ? 'checked="checked"' : '');
			} elseif ($typeCurrentInput === onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_FLOAT ||
				$typeCurrentInput === 'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:float') {
				$inputType = 'type="number" step="0.01" ';
			} elseif ($typeCurrentInput === onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_INTEGER ||
					$typeCurrentInput === 'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:decimal') {
				$inputType = 'type="number" step="1" ';
			}

			if ($isRangeValue && $pForm->inRangeSearchcriteriaInfos($fieldName) &&
				count($pForm->getSearchcriteriaRangeInfosForField($fieldName)) > 0) {

				foreach ($pForm->getSearchcriteriaRangeInfosForField($fieldName) as $key => $rangeDescription) {
					$value = 'value="'.esc_attr($pForm->getFieldValue($key, true)).'"';
					$output .= '<input '.$inputType.$requiredAttribute.' name="'.esc_attr($key).'" '
						.$value.' placeholder="'.esc_attr($rangeDescription).'">';
				}
			} else {
				$output .= '<input '.$inputType.$requiredAttribute.' name="'.esc_attr($fieldName).'" '.$value.'>';
			}
		}
		return $output;
	}
}


if (!function_exists('renderRegionalAddition')) {
	function renderRegionalAddition(string $inputName, array $selectedValue, bool $multiple, string $fieldLabel, array $permittedValues = null): string
	{
		$output = '';
		$name = esc_attr($inputName).($multiple ? '[]' : '');
		$multipleAttr = $multiple ? 'multiple size="5"' : 'size="1"';
		$output .= '<select name="'.$name.'" '.$multipleAttr.'>';
		$pRegionController = new RegionController();

		if ($permittedValues !== null) {
			$regions = $pRegionController->getParentRegionsByChildRegionKeys(array_keys($permittedValues));
		} else {
			$regions = $pRegionController->getRegions();
		}
		ob_start();
		echo '<option value="">'.esc_html(sprintf(__('Choose %s', 'onoffice'), $fieldLabel)).'</option>';
		foreach ($regions as $pRegion) {
			/* @var $pRegion Region */
			printRegion( $pRegion, $selectedValue ?? [] );
		}
		$output .= ob_get_clean();
		$output .= '</select>';
		return $output;
	}
}