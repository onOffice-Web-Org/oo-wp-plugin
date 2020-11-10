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
<form method="post" id="onoffice-form" data-applicant-form-id="<?php echo esc_attr($pForm->getFormId()); ?>">

	<input type="hidden" name="oo_formid" value="<?php echo $pForm->getFormId(); ?>">
	<input type="hidden" name="oo_formno" value="<?php echo $pForm->getFormNo(); ?>">
	<?php if ( isset( $currentEstate ) ) : ?>
	<input type="hidden" name="Id" value="<?php echo esc_attr($currentEstate['Id']); ?>">
	<?php endif; ?>

<?php

$selectTypes = array(
		\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_MULTISELECT,
		\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_SINGLESELECT,
	);

if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_ERROR) {
	echo esc_html__('ERROR!', 'onoffice');
}

/* @var $pForm \onOffice\WPlugin\Form */
foreach ( $pForm->getInputFields() as $input => $table ) {
	if ( in_array( $input, array('message', 'Id') ) ) {
		continue;
	}

	if ( $pForm->isMissingField( $input ) ) {
		echo '<span class="onoffice-pleasefill">'.esc_html__('Please fill in!', 'onoffice').'</span>';
	}

	$isRequired = $pForm->isRequiredField( $input );
	$addition = $isRequired ? '*' : '';
	$inputAddition = $isRequired ? ' required' : '';
	echo '<label for="'.$input.'">'.esc_html($pForm->getFieldLabel( $input )).$addition.': </label>';

	$permittedValues = $pForm->getPermittedValues( $input, true );

	if ($input === 'Umkreis') {
		echo '<fieldset>'
			.'<legend>'.esc_html__('search within distance of:', 'onoffice').'</legend>';

		foreach ($pForm->getUmkreisFields() as $key => $values) {
			echo esc_html($values['label']).':';

			if (in_array($values['type'], $selectTypes)) {
				$permittedValues = $values['permittedvalues'];

				echo '<select size="1" name="'.$key.'">';
				echo '<option value="">'.esc_html('not specified').'</option>';

				foreach ( $permittedValues as $countryCode => $countryName ) {
					echo '<option value="'.esc_attr($countryCode).'">'
						.esc_html($countryName).'</option>';
				}

				echo '</select>';
			} else {
				echo '<input type="text" name="'.esc_html($key).'" value="'
					.esc_attr($pForm->getFieldValue( $key )).'"'.$inputAddition.'>';
			}
		}

		echo '</fieldset>';
		continue;
	}

	if ($input === 'regionaler_zusatz') {
		echo '<select size="1" name="'.esc_html($input).'">';
		$pRegionController = new \onOffice\WPlugin\Region\RegionController();
		if ($permittedValues === null) {
			$regions = $pRegionController->getRegions();
		} else {
			$regions = $pRegionController->getParentRegionsByChildRegionKeys(array_keys($permittedValues));
		}
		$selectedValue = $pForm->getFieldValue( $input, true );
		foreach ($regions as $pRegion) {
			/* @var $pRegion Region */
			printRegion( $pRegion, [$selectedValue] );
		}
		echo '</select>';
	} else {
		echo renderFormField($input, $pForm, false);
	}

}

$pForm->setGenericSetting('submitButtonLabel', esc_html__('Search for Prospective Buyers', 'onoffice'));
include(ONOFFICE_PLUGIN_DIR.'/templates.dist/form/formsubmit.php');
echo '<svg viewBox="0 0 30 30" id="spinner"></svg>';

if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_SUCCESS) {
	$applicants = $pForm->getResponseFieldsValues();
	$rangeFields = array_keys($pForm->getSearchcriteriaRangeInfos());
	$umkreisFields = $pForm->getUmkreisFields();
	$countResults = $pForm->getCountAbsolutResults();

	echo '<div class="oo-form-result-wrapper">';
	echo '<h2>'.esc_html(
			sprintf(_n(
				/* translators: %s will be replaced with a number. */
				'%s Prospective Buyer', '%s Prospective Buyers', $countResults, 'onoffice'),
					number_format_i18n($countResults))).'</h2>';
			
					if($countResults > 0) {
						echo '<div>';

						
					}
					
				

			
	foreach ($applicants as $address => $searchdata) {

		/* translators: %s will be replaced with a customer reference number. */
		
		echo '<div class="oo-applicant">';
		echo '<div class="oo-customer">'.esc_html(sprintf(__('Customer ref. number %s', 'onoffice'), $address)).'<div> </div></div>';
		
	
		$umkreis = array();

		foreach ($searchdata as $name => $value) {
			if (in_array($name, $rangeFields)) {
				$realName = $pForm->getFieldLabel($name);

				if (is_array($value)) {
					if ($value[0] > 0) {
						
						//postTitle($realName);
						echo '<div class="oo-single_info"><div class="value">'.esc_html('ab', 'onoffice').' ';
						echo $value[0]. '</div><div class="name"><small>'.$realName.'</small></div></div>';
					}

					if ($value[1] > 0) {
					echo '<div class="oo-single_info"><div class="value">'.esc_html('bis', 'onoffice').' ';
					echo $value[0]. '</div><div class="name"><small>'.$realName.'</small></div></div>';

					}
					continue;
				}
			} elseif (in_array($name, array_keys($umkreisFields))) {
				$typeCurrentInput = $umkreisFields[$name]['type'];

				$realName = $umkreisFields[$name]['label'];
				$umkreis[$realName] = $value;

				if ($name == 'range' && $value > 0) {
					$umkreis[$realName] .= esc_html('km distance');
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

		
			echo '<div class="oo-single_info"><div class="value">'.(is_array($value) ? implode(', ', $value) : $value). '</div><div><small>'.esc_html($realName).'</small></div></div>';
		}

		if (count($umkreis) > 0) {
			echo '<span><i>'.implode(' ', array_values($umkreis)).'</i></span>';
		}
		echo '</div>';
	}
	if($countResults > 0) {
		echo '</div>';
		
	}
	echo '</div>';
}

?>
</form>