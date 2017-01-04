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

add_thickbox();

$pages = $pForm->getPages();

$addressValues = array();
$estateValues = array();

if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_SUCCESS)
{
	echo 'SUCCESS!';
}
else
{
	if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_ERROR)
	{
		echo 'ERROR!';
	}

	/* @var $pForm \onOffice\WPlugin\Form */
	foreach ( $pForm->getInputFields() as $input => $table )
	{

		if ( $pForm->isMissingField( $input )  && $pForm->getFormStatus() == onOffice\WPlugin\FormPost::MESSAGE_REQUIRED_FIELDS_MISSING)
		{
			echo $pForm->getFieldLabel( $input ).' - Angabe fehlt, bitte ausfüllen!<br>';
		}

		$line = null;

		$selectTypes = array(
			onOffice\WPlugin\FieldType::FIELD_TYPE_MULTISELECT,
			onOffice\WPlugin\FieldType::FIELD_TYPE_SINGLESELECT,
		);

		$typeCurrentInput = $pForm->getFieldType( $input );

		if ( in_array( $typeCurrentInput, $selectTypes, true ) )
		{
			$line = $pForm->getFieldLabel( $input ).': ';

			$permittedValues = $pForm->getPermittedValues( $input, true );
			$selectedValue = $pForm->getFieldValue( $input, true );
			$line .= '<select size="1" name="'.$input.'>';

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
				$line .=  '<option value="'.esc_html($key).'"'.($isSelected ? ' selected' : '').'>'.esc_html($value).'</option>';
			}
			$line .= '</select>';
		}
		else
		{
			$line .= $pForm->getFieldLabel( $input ).': <input name="'.$input.'" value="'
					.$pForm->getFieldValue( $input ).'">';
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
}
?>

<script>
function weiter(pages)
{
	for (i=1; i<= pages; i++)
	{
		if ($('#'+i).is(':visible'))
		{
			if (i < pages)
			{
				$('#'+i).hide();
				i++;
				$('#'+i).show();
				break;
			}
		}
	}
}

function zurueck(pages)
{
	for (i=pages; i>= 1; i--)
	{
		if ($('#'+i).is(':visible'))
		{
			if (i > 1)
			{
				$('#'+i).hide();
				i--;
				$('#'+i).show();
				break;
			}
		}
	}
}
</script>

<div id="my-content-id" style="display:none;">
	<p>
		<form name="leadgenerator" action="" method="post">
			<input type="hidden" name="oo_formid" value="<?php echo $pForm->getFormId(); ?>">
			<input type="hidden" name="oo_formno" value="<?php echo $pForm->getFormNo(); ?>">
			<div id="inhalt">
				<?php
					if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_ERROR)
					{
						echo 'ERROR!';
					}

					for ($i = 1; $i <= $pages; $i++)
					{
						if ($i == 1)
						{
							$displayValue = 'block';
						}
						else
						{
							$displayValue = 'none';
						}
						echo '<div id="'.$i.'" style="display:'.$displayValue.'">';
							include('includes/ownerleadgeneratorform_'.$i.'.php');
						echo '</div>';
					}
				?>
			</div>
			<br/>
			<div style="width:500">
				<div id="back"  style="float:left; cursor:pointer;" onclick="zurueck(<?php echo $pages; ?>)">Zur&uuml;ck</div>
				<div id="vor"  style="float:right; cursor:pointer;" onclick="weiter(<?php echo $pages; ?>)">Weiter</div>
			</div>
			<p>
			<div id="buttonSubmit" style="clear:both"><input type="submit" value="GO!"></div>
		   </p>
		</form>
     </p>
</div>

<a href="#TB_inline?width=700&height=650&inlineId=my-content-id" class="thickbox">Zum Formular...</a>