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

?>

<form method="post">

	<input type="hidden" name="oo_formid" value="<?php echo $pForm->getFormId(); ?>">
	<input type="hidden" name="oo_formno" value="<?php echo $pForm->getFormNo(); ?>">

<?php

$addressValues = array();
$searchcriteriaValues = array();

if ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_SUCCESS)
{
	echo '<p>SUCCESS!</p>';
}

if ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_ERROR)
{
	echo '<p>ERROR!</p>';
}

if ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_REQUIRED_FIELDS_MISSING)
{
	echo '<p>Missing Fields!</p>';
}

/* @var $pForm \onOffice\WPlugin\Form */
foreach ( $pForm->getInputFields() as $input => $table ) {
	$line = null;

	$typeCurrentInput = $pForm->getFieldType( $input );
	$isSearchcriteriaField = $pForm->isSearchcriteriaField($input);
	$isRequired = $pForm->isRequiredField( $input );
	$addition = $isRequired ? '*' : '';
	$permittedValues = $pForm->getPermittedValues( $input, true );
	$selectedValue = $pForm->getFieldValue( $input, true );
	$line = $pForm->getFieldLabel( $input ).$addition.': ';

	if ( \onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_SINGLESELECT === $typeCurrentInput &&
		!$isSearchcriteriaField ) {
		$line .= '<select size="1" name="'.$input.'">';

		foreach ( $permittedValues as $key => $value ) {
			if ( is_array( $selectedValue ) ) {
				$isSelected = in_array( $key, $selectedValue, true );
			} else {
				$isSelected = $selectedValue == $key;
			}
			$line .=  '<option value="'.esc_html($key).'"'.($isSelected ? ' selected' : '').'>'
				.esc_html($value).'</option>';
		}

		$line .= '</select>';
	} elseif (\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_MULTISELECT === $typeCurrentInput ||
			(\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_SINGLESELECT === $typeCurrentInput &&
				$isSearchcriteriaField)) {
		$line .= '<br><div data-name="'.esc_html($input).'" class="multiselect" data-values="'
			.esc_html(json_encode($permittedValues)).'" data-selected="'
			.esc_html(json_encode($selectedValue)).'">
			<input type="button" class="onoffice-multiselect-edit" value="'
			.esc_html__('Werte bearbeiten', 'onoffice').'"></div>';
	} else {
		$inputType = 'text';
		$valueTag = 'value="'.$pForm->getFieldValue( $input ).'"';

		if ($pForm->getFieldType($input) == onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_BOOLEAN) {
			$inputType = 'checkbox';
			$valueTag = $pForm->getFieldValue( $input, true ) == 1 ? 'checked="checked"' : '';
		}

		if ($pForm->inRangeSearchcriteriaInfos($input) &&
			count($pForm->getSearchcriteriaRangeInfosForField($input)) > 0) {

			foreach ($pForm->getSearchcriteriaRangeInfosForField($input) as $key => $value) {
				$line .= '<input type="'.$inputType.'" value="'
					.$pForm->getFieldValue( $key ).'" name="'.$key.'" placeholder="'.$value.'" '.$valueTag.'> ';
			}
		} else {
			$line .= '<input type="'.$inputType.'" name="'.$input.'" value="'
				.$pForm->getFieldValue( $input ).'" '.$valueTag.'>';
		}
	}

	if ( $pForm->isMissingField( $input ) ) {
		$line .= '<span>Bitte ausfüllen!</span>';
	}

	if ($table == 'address') {
		$addressValues []= $line;
	}

	if ($table == 'searchcriteria') {
		$searchcriteriaValues []= $line;
	}
}


?>
	<p>
	<h1>Ihre Kontaktdaten</h1>
		<div>
			<?php echo implode('<br>', $addressValues); ?>
		</div>
	</p>
	<p>
	<h1>Ihre Suchkriterien</h1>
		<div>
			<?php echo implode('<br>', $searchcriteriaValues) ?>
		</div>
	</p>
	<div>
		<input type="submit" value="GO!">
	</div>
</form>