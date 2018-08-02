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

if (!function_exists('renderField')) {
	function renderField($inputName, array $properties) {
		$multiSelectableTypes = array(
			FieldTypes::FIELD_TYPE_SINGLESELECT,
			FieldTypes::FIELD_TYPE_MULTISELECT,
		);

		$selectedValue = $properties['value'];
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
			$inputName !== 'regionaler_zusatz' ) {
				$permittedValues = $properties['permittedvalues'];
				echo '<div id="ms2" data-name="'.esc_html($inputName).'" class="multiselect" data-values="'
					.esc_html(json_encode($permittedValues)).'" data-selected="'
					.esc_html(json_encode($selectedValue)).'">
				<input type="button" class="onoffice-multiselect-edit" value="'
					.esc_html__('Werte bearbeiten', 'onoffice').'">
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
		} elseif ( FieldTypes::isNumericType( $properties['type'] ) ||
			FieldTypes::FIELD_TYPE_DATETIME === $properties['type'] ||
			FieldTypes::FIELD_TYPE_DATE === $properties['type']) {
			esc_html_e('From: ', 'onoffice');
			echo '<input name="'.esc_attr($inputName).'__von" type="text" ';
			echo 'value="'.esc_attr(isset($selectedValue[0]) ? $selectedValue[0] : '').'"><br>';
			esc_html_e('Up to: ', 'onoffice');
			echo '<input name="'.esc_attr($inputName).'__bis" type="text" ';
			echo 'value="'.esc_attr(isset($selectedValue[1]) ? $selectedValue[1] : '').'"><br>';
		} else {
			$lengthAttr = !is_null($properties['length']) ?
				' maxlength="'.esc_attr($properties['length']).'"' : '';
			echo '<input name="'.esc_attr($inputName).'" type="text" ';
			echo 'value="'.esc_attr($selectedValue).'"'.$lengthAttr.'><br>';
		}
	}
}