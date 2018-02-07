<?php

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

if ( ! function_exists( 'printRegion') ) {
	function printRegion( onOffice\WPlugin\Region\Region $pRegion, $selected = null, $level = 0 ) {
		$prefix = str_repeat( '-', $level );
		$selectStr = ($selected == $pRegion->getId() ? ' selected' : '');
		echo '<option value="'.esc_html( $pRegion->getId() ). '"'.$selectStr.'>'.$prefix.' '.esc_html( $pRegion->getName() ).'</option>';
		foreach ( $pRegion->getChildren() as $pRegionChild ) {
			printRegion($pRegionChild, $selected, $level + 1);
		}
	}
}
?>

<form method="get">

	<input type="hidden" name="oo_formid" value="<?php echo $pForm->getFormId(); ?>">
	<input type="hidden" name="oo_formno" value="<?php echo $pForm->getFormNo(); ?>">

<?php

foreach ( $pForm->getInputFields() as $input => $table ) {
	$selectTypes = array(
		\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_MULTISELECT,
		\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_SINGLESELECT,
	);

	$boolFields = array(
		'heizkosten_in_nebenkosten',
	);

	if ($input == 'regionaler_zusatz') {
		continue;
	}

	$typeCurrentInput = $pForm->getFieldType( $input );

	if ( in_array( $input, $boolFields )) {
		echo '</p>'.$pForm->getFieldLabel( $input ).': <input type="checkbox" name="'.$input.'"'
			.('on' == $pForm->getFieldValue( $input, true ) ? ' checked' : '').'></p>';
	} elseif ( in_array( $typeCurrentInput, $selectTypes, true ) ) {
		echo $pForm->getFieldLabel( $input ).': ';

		$permittedValues = $pForm->getPermittedValues( $input, true );
		$selectedValue = $pForm->getFieldValue( $input, true );
		echo '<select size="5" name="'.$input.'[]" multiple>';
		foreach ( $permittedValues as $key => $value ) {
			if ( is_array( $selectedValue ) ) {
				$isSelected = in_array( $key, $selectedValue, true );
			} else {
				$isSelected = $selectedValue == $key;
			}
			echo '<option value="'.esc_html($key).'"'.($isSelected ? ' selected' : '').'>'.esc_html($value).'</option>';
		}
		echo '</select>';
	} else {
		echo $pForm->getFieldLabel( $input ).': <input name="'.$input.'" value="'
			.$pForm->getFieldValue( $input ).'"><br>';
	}
}

?>
	<select name="regionaler_zusatz">
		<option value="">(keine Auswahl)</option>
<?php

$pRegionController = new \onOffice\WPlugin\Region\RegionController();
$regions = $pRegionController->getRegions();

foreach ($regions as $pRegion) {
	/* @var $pRegion onOffice\WPlugin\Region\Region */
	printRegion( $pRegion, $pForm->getFieldValue( 'regionaler_zusatz' ) );
}


?>
	</select>
	<input type="submit" value="GO!">
</form>