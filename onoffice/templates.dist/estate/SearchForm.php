<?php

/**
 *
 *    Copyright (C) 2018  onOffice Software AG
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

$visible = $pEstates->getVisibleFilterableFields();
$multiSelectableTypes = array(
	FieldTypes::FIELD_TYPE_SINGLESELECT,
	FieldTypes::FIELD_TYPE_MULTISELECT,
);

if (count($visible) === 0) {
	return;
}

?>

<form method="get">

<?php

foreach ($visible as $inputName => $properties) :
	$selectedValue = $properties['value'];
	echo '<p>';
	echo $properties['label'].': ';
	echo '<br>';
	if ( $properties['type'] === FieldTypes::FIELD_TYPE_BOOLEAN ) {
		echo '<br>';
		echo '<fieldset>
    <input type="radio" id="'.esc_attr($inputName).'_u" name="'.esc_attr($inputName).'" value="u"
		'.($selectedValue === null ? ' checked' : '').'>
    <label for="'.esc_attr($inputName).'_u">Keine Angabe</label>
    <input type="radio" id="'.esc_attr($inputName).'_y" name="'.esc_attr($inputName).'" value="y"
		'.($selectedValue === true  ? 'checked' : '').'>
    <label for="'.esc_attr($inputName).'_y">Ja</label>
    <input type="radio" id="'.esc_attr($inputName).'_n" name="'.esc_attr($inputName).'" value="n"
		'.($selectedValue === false ? 'checked' : '').'>
    <label for="'.esc_attr($inputName).'_n">Nein</label>
  </fieldset>';
	} elseif ( in_array($properties['type'], $multiSelectableTypes) &&
		$inputName !== 'regionaler_zusatz' ) {
		$permittedValues = $properties['permittedvalues'];
		echo '<select size="5" name="'.esc_attr($inputName).'[]" multiple>';
		foreach ( $permittedValues as $key => $value ) {
			if ( is_array( $selectedValue ) ) {
				$isSelected = in_array( $key, $selectedValue, true );
			} else {
				$isSelected = $selectedValue == $key;
			}
			echo '<option value="'.esc_attr($key).'"'.($isSelected ? ' selected' : '').'>';
			echo esc_html($value).'</option>';
		}
		echo '</select>';
	} elseif ( $inputName === 'regionaler_zusatz' ) {
		echo '<select size="5" name="'.esc_attr($inputName).'[]" multiple>';
		$pRegionController = new \onOffice\WPlugin\Region\RegionController();
		$regions = $pRegionController->getRegions();
		foreach ($regions as $pRegion) {
			/* @var $pRegion \onOffice\WPlugin\Region\Region */
			printRegion( $pRegion, $selectedValue );
		}
		echo '</select>';
	} elseif ( FieldTypes::isNumericType( $properties['type'] ) ||
		FieldTypes::FIELD_TYPE_DATETIME === $properties['type'] ||
		FieldTypes::FIELD_TYPE_DATE === $properties['type']) {
		echo 'von: ';
		echo '<input name="'.esc_attr($inputName).'__von" type="text" ';
		echo 'value="'.esc_attr(isset($selectedValue[0]) ? $selectedValue[0] : '').'"><br>';
		echo 'bis: ';
		echo '<input name="'.esc_attr($inputName).'__bis" type="text" ';
		echo 'value="'.esc_attr(isset($selectedValue[1]) ? $selectedValue[1] : '').'"><br>';
	} else {
		$lengthAttr = !is_null($properties['length']) ?
			' maxlength="'.esc_attr($properties['length']).'"' : '';
		echo '<input name="'.esc_attr($inputName).'" type="text" ';
		echo 'value="'.esc_attr($selectedValue).'"'.$lengthAttr.'><br>';
	}
	echo '</p>';
endforeach;
?>

	<input type="submit" value="<?php esc_attr_e('Abschicken', 'onoffice'); ?>">
</form>
<br>