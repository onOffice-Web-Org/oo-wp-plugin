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

include(ONOFFICE_PLUGIN_DIR.'/templates.dist/fields.php');

?>

<form method="post" id="onoffice-form">

	<input type="hidden" name="oo_formid" value="<?php echo $pForm->getFormId(); ?>">
	<input type="hidden" name="oo_formno" value="<?php echo $pForm->getFormNo(); ?>">
<?php

$addressValues = array();
$estateValues = array();

if ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_SUCCESS) {
	echo esc_html__('The form was sent successfully.', 'onoffice');
} else {
	if ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_ERROR) {
		echo esc_html__('There was an error sending the form.', 'onoffice');
	}

	/* @var $pForm \onOffice\WPlugin\Form */
	foreach ( $pForm->getInputFields() as $input => $table ) {
		$isRequired = $pForm->isRequiredField($input);
		$addition = $isRequired ? '*' : '';
		$typeCurrentInput = $pForm->getFieldType($input);

		if ($typeCurrentInput == onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_BOOLEAN) {
			$line = "<div class='oo-control'>";
			$line .= "<label class='oo-control__label' for=".$input.">";
			$line .= renderFormField($input, $pForm);
			$line .= $pForm->getFieldLabel($input).$addition."</label>";
			$line .= "</div>";
		}
		else {
			$line = "<label for=".$input.">". $pForm->getFieldLabel( $input ).$addition.':</label>';
			$line .= renderFormField($input, $pForm);
		}

		if ( $pForm->isMissingField( $input ) ) {
			$line .= ' <span>'.esc_html__('Please fill in', 'onoffice').'</span>';
		}

		if ($table == 'address') {
			$addressValues []= $line;
		}

		if ($table == 'estate') {
			$estateValues []= $line;
		}
	}

	if (array_key_exists('message', $pForm->getInputFields())) {
		$isRequiredMessage = $pForm->isRequiredField( 'message' );
		$additionMessage = $isRequiredMessage ? '*' : '';
		

		$messageInput = "<label for='message'>".esc_html__('Message', 'onoffice').$additionMessage.':</label>
		<textarea name="message" placeholder="'.esc_html__('Message', 'onoffice').'">'.$pForm->getFieldValue('message').'</textarea><br>';
		$addressValues []= $messageInput;
	}
	echo '<div class="oo-formfieldwrap">';
	echo '<h2>'.esc_html__('Your contact details', 'onoffice').'</h2>'
		.'<p>';
	echo implode('', $addressValues);
	echo '</p></div><div class="oo-formfieldwrap">
		<h2>'.esc_html__('Information about your property', 'onoffice').'</h2>
		<p>';
	echo implode('', $estateValues);
	echo '</p></div>';
	echo '<div class="oo-formfieldwrap">';
	include(ONOFFICE_PLUGIN_DIR.'/templates.dist/form/formsubmit.php');
	echo '</div>';
}
?>

</form>