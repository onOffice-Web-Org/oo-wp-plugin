<?php

use onOffice\WPlugin\Region\Region;
use onOffice\WPlugin\Region\RegionController;
use onOffice\WPlugin\Types\FieldTypes;

if ( ! function_exists( 'printRegion') ) {
	function printRegion( onOffice\WPlugin\Region\Region $pRegion, $selected = array(), $level = 0 ) {
		$prefix = str_repeat( '-', $level );
		$selectStr = (in_array($pRegion->getId(), $selected) ? ' selected' : '');
		echo '<option value="'.esc_html( $pRegion->getId() ).'"'.$selectStr.'>'
			.$prefix.' '.esc_html( $pRegion->getName() ).'</option>';
		foreach ( $pRegion->getChildren() as $pRegionChild ) {
			printRegion($pRegionChild, $selected, $level + 1);
		}
	}
}

if ( ! function_exists( 'printCountry' )) {
	function printCountry ($values, $selectedValue)	{
		echo '<option value="">'.esc_html('Not Specified', 'onoffice').'</option>';
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

if (!function_exists('renderFieldRange')) {
	function renderFieldRange(string $inputName, array $properties) {
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

		if ( $properties['type'] === FieldTypes::FIELD_TYPE_BOOLEAN ) {
			echo '<br>';
			echo '<fieldset>
		<input type="radio" id="'.esc_attr($inputName).'_u" name="'.esc_attr($inputName).'" value="u"
			'.($selectedValue === null ? ' checked' : '').'>
		<label for="'.esc_attr($inputName).'_u">'.esc_html('Not Specified', 'onoffice').'</label>
		<input type="radio" id="'.esc_attr($inputName).'_y" name="'.esc_attr($inputName).'" value="y"
			'.($selectedValue === true  ? 'checked' : '').'>
		<label for="'.esc_attr($inputName).'_y">'.esc_html('Yes', 'onoffice').'</label>
		<input type="radio" id="'.esc_attr($inputName).'_n" name="'.esc_attr($inputName).'" value="n"
			'.($selectedValue === false ? 'checked' : '').'>
		<label for="'.esc_attr($inputName).'_n">'.esc_html('No', 'onoffice').'</label>
	  </fieldset>';
		} elseif ( in_array($properties['type'], $multiSelectableTypes) &&
			$inputName !== 'regionaler_zusatz' &&
			$inputName != 'country') {
				$permittedValues = $properties['permittedvalues'];
				echo '<div data-name="'.esc_html($inputName).'" class="multiselect" data-values="'
					.esc_html(json_encode($permittedValues)).'" data-selected="'
					.esc_html(json_encode($selectedValue)).'">
				<input type="button" class="onoffice-multiselect-edit" value="'
					.esc_html__('Edit values', 'onoffice').'">
			</div>
			';
		} elseif ( $inputName === 'regionaler_zusatz' ) {
			echo '<select size="5" name="'.esc_attr($inputName).'[]" multiple>';
			$pRegionController = new RegionController();
			$regions = $pRegionController->getRegions();
			foreach ($regions as $pRegion) {
				/* @var $pRegion Region */
				printRegion( $pRegion, $selectedValue );
			}
			echo '</select>';
		}
		elseif ( $inputName === 'country' )	{
			echo '<select size="1" name="'.esc_attr($inputName).'">';
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

if (!function_exists('renderSingleField')) {
	function renderSingleField(string $fieldName, onOffice\WPlugin\Form $pForm): string
	{
		$output = '';
		$selectTypes = array(
			\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_MULTISELECT,
			\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_SINGLESELECT,
		);

		$typeCurrentInput = $pForm->getFieldType( $fieldName );
		$isRequired = $pForm->isRequiredField( $fieldName );
		$requiredAttribute = $isRequired ? 'required ' : '';

		if ( in_array( $typeCurrentInput, $selectTypes, true ) ) {
			$permittedValues = $pForm->getPermittedValues( $fieldName, true );
			$selectedValue = $pForm->getFieldValue( $fieldName, true );
			$output .= '<select size="1" name="'.esc_attr($fieldName).'">';

			foreach ( $permittedValues as $key => $value ) {
				if ( is_array( $selectedValue ) ) {
					$isSelected = in_array( $key, $selectedValue, true );
				} else {
					$isSelected = $selectedValue == $key;
				}
				$output .= '<option value="'.esc_attr($key).'"'.($isSelected ? ' selected' : '').'>'
					.esc_html($value).'</option>';
			}
			$output .= '</select><br>';
		} else {
			$inputType = 'type="text" ';
			$value = 'value="'.$pForm->getFieldValue( $fieldName ).'"';

			if ($typeCurrentInput == onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_BOOLEAN) {
				$inputType = 'type="checkbox" ';
				$value = $pForm->getFieldValue( $fieldName, true ) == 1 ? 'checked="checked"' : '';
			} elseif ($typeCurrentInput === onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_FLOAT) {
				$inputType = 'type="number" step="0.1" ';
			} elseif ($typeCurrentInput === onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_INTEGER) {
				$inputType = 'type="number" step="1" ';
			}

			$output .= '<input '.$inputType.$requiredAttribute.' name="'.esc_html($fieldName).'" '.$value.'><br>';
		}
		return $output;
	}
}