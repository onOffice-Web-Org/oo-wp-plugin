<?php
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

<form method="post">

	<input type="hidden" name="oo_formid" value="<?php echo $pForm->getFormId(); ?>">
	<input type="hidden" name="oo_formno" value="<?php echo $pForm->getFormNo(); ?>">

<?php

foreach ( $pForm->getInputFields() as $input => $table ) {
	$selectTypes = array(
		onOffice\WPlugin\FieldType::FIELD_TYPE_MULTISELECT,
		onOffice\WPlugin\FieldType::FIELD_TYPE_SINGLESELECT,
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

$language = $pForm->getLanguage();
$pRegionController = new \onOffice\WPlugin\Region\RegionController($language);
$regions = $pRegionController->getRegions();
var_dump($pForm->getFieldValue( 'regionaler_zusatz' ));

foreach ($regions as $pRegion) {
	/* @var $pRegion onOffice\WPlugin\Region\Region */
	printRegion( $pRegion, $pForm->getFieldValue( 'regionaler_zusatz' ) );
}


?>
	</select>
	<input type="submit" value="GO!">
</form>