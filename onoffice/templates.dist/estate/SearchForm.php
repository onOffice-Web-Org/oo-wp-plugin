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


$visible = $pEstates->getVisibleFilterableFields();
$multiSelectableTypes = array(FieldTypes::FIELD_TYPE_SINGLESELECT, FieldTypes::FIELD_TYPE_MULTISELECT);

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
		echo '<input type="checkbox" name="'.esc_attr($inputName).'"'
			.('on' == $selectedValue ? ' checked' : '').'>';
	} elseif ( in_array($properties['type'], $multiSelectableTypes) ) {
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