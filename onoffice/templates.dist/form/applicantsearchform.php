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
	<?php if ( isset( $currentEstate ) ) : ?>
	<input type="hidden" name="Id" value="<?php echo $currentEstate['Id']; ?>">
	<?php endif; ?>

<?php

	$selectTypes = array(
			\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_MULTISELECT,
			\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_SINGLESELECT,
		);

	if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_ERROR)
	{
		echo 'ERROR!';
	}

	/* @var $pForm \onOffice\WPlugin\Form */
	foreach ( $pForm->getInputFields() as $input => $table )
	{
		if ($input == 'Umkreis')
		{
			echo '<br>'
			. '<fieldset>'
			. '<legend>Umkreissuche:</legend>';

			foreach($pForm->getUmkreisFields() as $key => $values)
			{
				echo $values['label'].':<br>';

				if (in_array($values['type'], $selectTypes))
				{
					$permittedValues = $values['permittedvalues'];

					echo '<select size="1" name="'.$key.'">';
					echo  '<option value="">'.esc_html('Keine Angabe').'</option>';

					foreach ( $permittedValues as $countryCode => $countryName )
					{
						echo  '<option value="'.esc_html($countryCode).'">'.esc_html($countryName).'</option>';
					}

					echo '</select><br>';
				}
				else
				{
					echo '<input type="text" name="'.$key.'" value="'.$pForm->getFieldValue( $key ).'"> <br>';
				}
			}
			echo '</fieldset>';

			continue;
		}

		$typeCurrentInput = $pForm->getFieldType( $input );

		$isRequired = $pForm->isRequiredField( $input );
		$addition = $isRequired ? '*' : '';

		if ( in_array( $typeCurrentInput, $selectTypes, true ) )
		{
			echo $pForm->getFieldLabel( $input ).':<br>';

			$permittedValues = $pForm->getPermittedValues( $input, true );
			$selectedValue = $pForm->getFieldValue( $input, true );

			echo '<select size="1" name="'.$input.'">';
			echo  '<option value="">'.esc_html('Keine Angabe').'</option>';

			foreach ( $permittedValues as $key => $value )
			{
				if ( is_array( $selectedValue ) )
				{
					$isSelected = in_array( $key, $selectedValue );
				}
				else
				{
					$isSelected = $selectedValue == $key;
				}

				echo  '<option value="'.esc_html($key).'"'.($isSelected ? ' selected' : '').'>'.esc_html($value).'</option>';
			}
			echo '</select><br>';
		}
		else
		{
			if ($pForm->inRangeSearchcriteriaInfos($input) &&
				count($pForm->getSearchcriteriaRangeInfosForField($input)) > 0)
			{
				echo $pForm->getFieldLabel( $input ).':<br>';

				foreach ($pForm->getSearchcriteriaRangeInfosForField($input) as $key => $value)
				{
					echo '<input name="'.$key.'" placeholder="'.$value.'" value="'.$pForm->getFieldValue($key).'"> ';
				}
				echo '<br>';
			}
			else
			{
				echo $pForm->getFieldLabel( $input ).$addition.': <input type="text" name="'.$input.'" value="'
					.$pForm->getFieldValue( $input ).'"><br>';
			}
		}

		if ( $pForm->isMissingField( $input ) )
		{
			echo 'Bitte ausfüllen!';
		}

		if ( in_array( $input, array('message', 'Id') ) )
		{
			continue;
		}
	}

?>

	<br><input type="submit" value="Interessenten suchen">
<?php

if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_SUCCESS)
{
	$applicants = $pForm->getResponseFieldsValues();
	$rangeFields = array_keys($pForm->getSearchcriteriaRangeInfos());
	$umkreisFields = $pForm->getUmkreisFields();
	$countResults = count(array_keys($applicants)); // Anzahl Ergebnisse

	echo '<p>';
	echo '<br> <span><b>Anzahl Ergebnisse: '.$countResults.'</b></span><br>';

	foreach ($applicants as $address => $searchdata)	{

		echo '<br><span><b>Interessentprofil '.$address.'</b></span><br>';
		$umkreis = array();

		foreach ($searchdata as $name => $value)
		{
			if (in_array($name, $rangeFields))//von-bis felder
			{
				$realName = $pForm->getFieldLabel($name);

				if (is_array($value))
				{
					echo '<span>';
					if ($value[0] > 0)
					{
						echo $realName .' min. '.$value[0].'<br>';
					}

					if ($value[1] > 0)
					{
						echo $realName.' max. '.$value[1];
					}
					echo '</span><br>';
				}
			}
			elseif (in_array($name, array_keys($umkreisFields)))//Umkreissuche-felder
			{
				$typeCurrentInput = $umkreisFields[$name]['type'];
				if (in_array($typeCurrentInput, $selectTypes))
				{
					$permittedValues = $umkreisFields[$name]['permittedvalues'];
					$value = $permittedValues[$value];
				}
				$realName = $umkreisFields[$name]['label'];

				$umkreis[$realName] = $value;

				if ($name == 'range' && $value > 0)
				{
					$umkreis[$realName] .= 'km Umkreis';
				}
			}
			else //normale felder
			{
				$realName = $pForm->getFieldLabel($name);
				$typeCurrentInput = $pForm->getFieldType( $name );

				if ( in_array( $typeCurrentInput, $selectTypes, true ) )
				{
					$permittedValues = $pForm->getPermittedValues( $name, true );
					$value = $permittedValues[$value];
				}

				echo '<span>'.$realName.': '.$value.'</span><br>';
			}
		}

		if (count($umkreis) > 0)
		{
			echo '<span><i>'.implode(' ', array_values($umkreis)).'</i></span><br>';
		}
	}
	echo '</p>';

}
die;
?>
</form>