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

$pathComponents = [ONOFFICE_PLUGIN_DIR, 'templates.dist', 'fields.php'];
require(implode(DIRECTORY_SEPARATOR, $pathComponents));

?>
<form method="post" id="onoffice-form">

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

if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_ERROR) {
	echo 'ERROR!';
}

/* @var $pForm \onOffice\WPlugin\Form */
foreach ( $pForm->getInputFields() as $input => $table ) {
	if ( in_array( $input, array('message', 'Id') ) ) {
		continue;
	}

	if ( $pForm->isMissingField( $input ) ) {
		echo '<span class="onoffice-pleasefill">Bitte ausfüllen!</span>';
	}

	$isRequired = $pForm->isRequiredField( $input );
	$addition = $isRequired ? '*' : '';
	$inputAddition = $isRequired ? ' required' : '';
	echo $pForm->getFieldLabel( $input ).$addition.': <br>';

	$permittedValues = $pForm->getPermittedValues( $input, true );

	if ($input == 'Umkreis') {
		echo '<br>'
			.'<fieldset>'
			.'<legend>Umkreissuche:</legend>';

		foreach ($pForm->getUmkreisFields() as $key => $values) {
			echo esc_html($values['label']).':<br>';

			if (in_array($values['type'], $selectTypes)) {
				$permittedValues = $values['permittedvalues'];

				echo '<select size="1" name="'.$key.'">';
				echo '<option value="">'.esc_html('Keine Angabe').'</option>';

				foreach ( $permittedValues as $countryCode => $countryName ) {
					echo '<option value="'.esc_html($countryCode).'">'
						.esc_html($countryName).'</option>';
				}

				echo '</select><br>';
			} else {
				echo '<input type="text" name="'.esc_html($key).'" value="'
					.$pForm->getFieldValue( $key ).'"'.$inputAddition.'> <br>';
			}
		}

		echo '</fieldset>';
		continue;
	} elseif ($input === 'regionaler_zusatz') {
		echo '<select size="1" name="'.esc_html($input).'">';
		$pRegionController = new \onOffice\WPlugin\Region\RegionController();
		$regions = $pRegionController->getRegions();
		$selectedValue = $pForm->getFieldValue( $input, true );
		foreach ($regions as $pRegion) {
			/* @var $pRegion Region */
			printRegion( $pRegion, [$selectedValue] );
		}
		echo '</select><br>';
	} else {
		echo renderFormField($input, $pForm, false);
	}

	echo '<br>';
}

$pForm->setGenericSetting('submitButtonLabel', __('Search for Prospective Buyers', 'onoffice'));
include(ONOFFICE_PLUGIN_DIR.'/templates.dist/form/formsubmit.php');
echo '<br>';

if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_SUCCESS) {
	$applicants = $pForm->getResponseFieldsValues();
	$rangeFields = array_keys($pForm->getSearchcriteriaRangeInfos());
	$umkreisFields = $pForm->getUmkreisFields();
	$countResults = $pForm->getCountAbsolutResults();

	echo '<p>';
	echo '<br><span>'.esc_html(
			sprintf(_n(
				/* translators: %s will be replaced with a number. */
				'%s Prospective Buyer', '%s Prospective Buyers', $countResults, 'onoffice'),
					number_format_i18n($countResults))).'</span><br>';

	foreach ($applicants as $address => $searchdata) {
		echo '<br>';
		/* translators: %s will be replaced with a customer reference number. */
		echo '<span>'.esc_html(sprintf(__('Customer ref. number %s', 'onoffice'), $address)).'</span>';
		echo '<br>';
		$umkreis = array();

		foreach ($searchdata as $name => $value) {
			if (in_array($name, $rangeFields)) {
				$realName = $pForm->getFieldLabel($name);

				if (is_array($value)) {
					if ($value[0] > 0) {
						echo $realName.' min. '.$value[0].'<br>';
					}

					if ($value[1] > 0) {
						echo $realName.' max. '.$value[1];
					}
					echo '<br>';
					continue;
				}
			} elseif (in_array($name, array_keys($umkreisFields))) {
				$typeCurrentInput = $umkreisFields[$name]['type'];

				$realName = $umkreisFields[$name]['label'];
				$umkreis[$realName] = $value;

				if ($name == 'range' && $value > 0) {
					$umkreis[$realName] .= 'km Umkreis';
				}
			} else {
				$realName = $pForm->getFieldLabel($name);
				$typeCurrentInput = $pForm->getFieldType( $name );
			}

			if (in_array($pForm->getFieldType($name), $selectTypes) &&
				$name !== 'regionaler_zusatz') {
				if (in_array($typeCurrentInput, $selectTypes)) {
					$permittedValues = $pForm->getPermittedValues($name);

					if (!is_array($value)) {
						$value = $permittedValues[$value];
					} else {
						// multiple values selected in search criteria
						$value = implode(', ', array_intersect_key($permittedValues, array_flip($value)));
					}
				}
			} else if ($name === 'regionaler_zusatz') {
				$pRegionController = new \onOffice\WPlugin\Region\RegionController();

				$pRegion = $pRegionController->getRegionByKey(array_pop($value));
				/* @var $pRegion \onOffice\WPlugin\Region\Region */
				if ($pRegion !== null) {
					$value = esc_html($pRegion->getName());
				}
			}

			echo '<span>'.$realName.': '.(is_array($value) ? implode(', ', $value) : $value).'</span><br>';
		}

		if (count($umkreis) > 0) {
			echo '<span><i>'.implode(' ', array_values($umkreis)).'</i></span><br>';
		}
	}
	echo '</p>';
}

?>
</form>