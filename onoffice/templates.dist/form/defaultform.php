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
	<?php if ( isset( $estateId ) ) : ?>
	<input type="hidden" name="Id" value="<?php echo $estateId; ?>">
	<?php endif; ?>

<?php

$selectTypes = array(
	\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_MULTISELECT,
	\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_SINGLESELECT,
);

if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_SUCCESS) {
	echo 'SUCCESS!';
} else {
	if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_ERROR) {
		echo 'ERROR!';
	}

	/* @var $pForm \onOffice\WPlugin\Form */
	foreach ( $pForm->getInputFields() as $input => $table ) {
		if ( $pForm->isMissingField( $input ) ) {
			echo 'Bitte ausfüllen!';
		}

		if ( in_array( $input, array('message', 'Id') ) ) {
			continue;
		}

		$typeCurrentInput = $pForm->getFieldType( $input );
		$isRequired = $pForm->isRequiredField( $input );
		$addition = $isRequired ? '*' : '';
		echo $pForm->getFieldLabel( $input ).$addition.': ';

		if ( in_array( $typeCurrentInput, $selectTypes, true ) ) {
			$line = $pForm->getFieldLabel( $input ).': ';

			$permittedValues = $pForm->getPermittedValues( $input, true );
			$selectedValue = $pForm->getFieldValue( $input, true );
			echo '<select size="1" name="'.esc_attr($input).'">';

			foreach ( $permittedValues as $key => $value ) {
				if ( is_array( $selectedValue ) ) {
					$isSelected = in_array( $key, $selectedValue, true );
				} else {
					$isSelected = $selectedValue == $key;
				}
				echo '<option value="'.esc_attr($key).'"'.($isSelected ? ' selected' : '').'>'
					.esc_html($value).'</option>';
			}
			echo '</select><br>';
		} else {
			$inputType = 'text';
			$value = 'value="'.$pForm->getFieldValue( $input ).'"';

			if ($typeCurrentInput == onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_BOOLEAN) {
				$inputType = 'checkbox';
				$value = $pForm->getFieldValue( $input, true ) == 1 ? 'checked="checked"' : '';
			}

			echo '<input type="'.$inputType.'" name="'.esc_html($input).'" '.$value.'><br>';
		}
	}

	if (array_key_exists('message', $pForm->getInputFields())):
		$isRequiredMessage = $pForm->isRequiredField( 'message' );
		$additionMessage = $isRequiredMessage ? '*' : '';
?>

		Nachricht<?php echo $additionMessage; ?>:<br>
		<textarea name="message"><?php echo $pForm->getFieldValue( 'message' ); ?></textarea><br>

<?php
	endif;
?>

		<input type="submit" value="GO!">

<?php
}
?>
</form>