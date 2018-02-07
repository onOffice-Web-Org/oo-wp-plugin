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
$estateValues = array();

if ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_SUCCESS)
{
	echo 'SUCCESS!';
}
else
{
	if ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_ERROR)
	{
		echo 'ERROR!';
	}

	/* @var $pForm \onOffice\WPlugin\Form */
	foreach ( $pForm->getInputFields() as $input => $table )
	{
		$line = null;

		$selectTypes = array(
			\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_MULTISELECT,
			\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_SINGLESELECT,
		);

		$typeCurrentInput = $pForm->getFieldType( $input );

		if ( in_array( $typeCurrentInput, $selectTypes, true ) )
		{
			$line = $pForm->getFieldLabel( $input ).': ';

			$permittedValues = $pForm->getPermittedValues( $input, true );
			$selectedValue = $pForm->getFieldValue( $input, true );
			$line .= '<select size="1" name="'.esc_attr($input).'">';

			foreach ( $permittedValues as $key => $value )
			{
				if ( is_array( $selectedValue ) )
				{
					$isSelected = in_array( $key, $selectedValue, true );
				}
				else
				{
					$isSelected = $selectedValue == $key;
				}
				$line .=  '<option value="'.esc_attr($key).'"'.($isSelected ? ' selected' : '').'>'.esc_html($value).'</option>';
			}
			$line .= '</select>';
		}
		else
		{
			$line .= $pForm->getFieldLabel( $input ).': <input name="'.esc_attr($input).'" value="'
					.esc_attr($pForm->getFieldValue( $input, true )).'">';
		}

		if ( $pForm->isMissingField( $input ) )
		{
			$line .= ' <span>Bitte ausfüllen!</span>';
		}

		if ($table == 'address')
		{
			$addressValues []= $line;
		}

		if ($table == 'estate')
		{
			$estateValues []= $line;
		}
	}

	echo '<h2>Ihre Kontaktdaten</h2>'
		.'<p>';
	echo implode('<br/>', $addressValues);
	echo '</p>
		<h2>Angaben zu Ihrem Eigentum</h2>
		<p>';
	echo implode('<br/>', $estateValues);
	echo '</p>';

?>

	<input type="submit" value="GO!">
<?php
}
?>

</form>