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

$addressValues = array();
$estateValues = array();
$miscValues = array();

if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_SUCCESS) {
	echo 'SUCCESS!';
} else {
	if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_ERROR) {
		echo 'ERROR!';
	}

	/* @var $pForm \onOffice\WPlugin\Form */
	foreach ( $pForm->getInputFields() as $input => $table ) {
		if ( $pForm->isMissingField( $input )  &&
			$pForm->getFormStatus() == onOffice\WPlugin\FormPost::MESSAGE_REQUIRED_FIELDS_MISSING) {
			echo $pForm->getFieldLabel( $input ).' - Angabe fehlt, bitte ausfüllen!<br>';
		}

		$line = '';

		$selectTypes = array(
			\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_MULTISELECT,
			\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_SINGLESELECT,
		);

		$typeCurrentInput = $pForm->getFieldType( $input );
		$isRequired = $pForm->isRequiredField( $input );
		$addition = $isRequired ? '*' : '';
		$requiredAttribute = $isRequired ? ' required' : '';

		if ( in_array( $typeCurrentInput, $selectTypes, true ) ) {
			$line = $pForm->getFieldLabel( $input ).$addition.': ';

			$permittedValues = $pForm->getPermittedValues( $input, true );
			$selectedValue = $pForm->getFieldValue( $input, true );
			$line .= '<select size="1" name="'.esc_html($input).'"'.$requiredAttribute.'>';

			foreach ( $permittedValues as $key => $value ) {
				if ( is_array( $selectedValue ) ) {
					$isSelected = in_array( $key, $selectedValue, true );
				} else {
					$isSelected = $selectedValue == $key;
				}
				$line .= '<option value="'.esc_html($key).'"'.($isSelected ? ' selected' : '').'>'
					.esc_html($value).'</option>';
			}
			$line .= '</select>';
		} else {
			$inputType = 'text';
			$value = 'value="'.$pForm->getFieldValue( $input ).'"';

			if ($typeCurrentInput == onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_BOOLEAN) {
				$inputType = 'checkbox';
				$value = $pForm->getFieldValue( $input, true ) == 1 ? 'checked="checked"' : '';
				$value .= ' value="y"';
			}

			$line .= $pForm->getFieldLabel( $input ).$addition.': ';
			$line .= '<input type="'.$inputType.'" name="'.esc_attr($input).'" '.$value.$requiredAttribute.'><br>';
		}

		if ($table == 'address') {
			$addressValues []= $line;
		} elseif ($table == 'estate') {
			$estateValues []= $line;
		} else {
			$miscValues []= $line;
		}
	}
}
?>

<script>
	$(document).ready(function() {
		var oOPaging = new onOffice.paging('leadform');
		oOPaging.setFormId('leadgeneratorform');
		oOPaging.setup();
	});
</script>

<div id="onoffice-lead" style="display:none;">
	<p>
		<form name="leadgenerator" action="" method="post" id="leadgeneratorform">
			<input type="hidden" name="oo_formid" value="<?php echo $pForm->getFormId(); ?>">
			<input type="hidden" name="oo_formno" value="<?php echo $pForm->getFormNo(); ?>">
			<div id="leadform">
				<?php
					if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_ERROR) {
						echo 'ERROR!';
					}
				?>

				<div class="lead-lightbox lead-page-1">
					<h2><?php echo esc_html__('Your contact details', 'onoffice'); ?></h2>
					<p>
						<?php echo implode('<br>', $addressValues); ?>
					</p>
				</div>

				<div class="lead-lightbox lead-page-2">
					<h2><?php echo esc_html__('Information about your property', 'onoffice'); ?></h2>
					<p>
						<?php echo implode('<br>', $estateValues); ?>
					</p>
					<p>
						<?php echo implode('<br>', $miscValues); ?>
					</p>
					<p>
						<input type="submit" value="<?php echo esc_html__('Send', 'onoffice'); ?>" style="float:right;">
					</p>
				</div>

				<span class="leadform-back" style="float:left; cursor:pointer;">
					<?php echo esc_html__('Back', 'onoffice'); ?>
				</span>
				<span class="leadform-forward" style="float:right; cursor:pointer;">
					<?php echo esc_html__('Next', 'onoffice'); ?>
				</span>
			</div>
		</form>
	</p>
</div>

<a href="#TB_inline?width=700&height=650&inlineId=onoffice-lead" class="thickbox">
	<?php echo esc_html__('Open the Form', 'onoffice'); ?>
</a>