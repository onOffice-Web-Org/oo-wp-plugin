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

<form method="post" id="onoffice-form" class="oo-form oo-form-owner">

	<input type="hidden" name="oo_formid" value="<?php echo $pForm->getFormId(); ?>">
	<input type="hidden" name="oo_formno" value="<?php echo $pForm->getFormNo(); ?>">
<?php

$addressValues = array();
$estateValues = array();

if ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_SUCCESS) {
	echo esc_html__('SUCCESS!', 'onoffice-for-wp-websites');
} else {
	if ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_ERROR) {
		echo esc_html__('ERROR!', 'onoffice-for-wp-websites');
	} elseif ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_RECAPTCHA_SPAM) {
		echo esc_html__('Spam detected!', 'onoffice-for-wp-websites');
	}

	/* @var $pForm \onOffice\WPlugin\Form */
	foreach ( $pForm->getInputFields() as $input => $table ) {
		$isRequired = $pForm->isRequiredField($input);
		$addition = $isRequired ? '*' : '';
		$isHiddenField = $pForm->isHiddenField($input);
		$line = $pForm->getFieldLabel($input).$addition.': ';
		$line = !$isHiddenField ? $line : '';
		$line .= renderFormField($input, $pForm);

		if ( $pForm->isMissingField( $input ) ) {
			$line .= ' <span>'.esc_html__('Please fill in', 'onoffice-for-wp-websites').'</span>';
		}
		if ( in_array( $input, array( 'gdprcheckbox' ) ) ) {
			$isHiddenField = $pForm->isHiddenField('gdprcheckbox');
			$fieldLabel = $pForm->getFieldLabel('gdprcheckbox');
			$line = renderFormField( 'gdprcheckbox', $pForm );
			$line .= !$isHiddenField ? $fieldLabel : '';
		}
		if ( in_array( $input, array( 'message' )) ) {
			$isRequiredMessage = $pForm->isRequiredField( 'message' );
			$additionMessage = $isRequiredMessage ? '*' : '';
			$isHiddenField = $pForm->isHiddenField('message');
			$additionHidden = $isHiddenField ? 'class="hidden-field"' : '';

			$line = $pForm->getFieldLabel('message').$additionMessage.':<br>';
			$line = !$isHiddenField ? $line : '';
			$line .= '<textarea name="message" '.$additionHidden.'>'.$pForm->getFieldValue('message').'</textarea><br>';
		}
		if ($table == 'address') {
			$addressValues []= $line;
		}

		if ($table == 'estate') {
			$estateValues []= $line;
		}

		if ($table == '') {
			$addressValues []= $line;
		}
	}

	echo '<h2>'.esc_html__('Your contact details', 'onoffice-for-wp-websites').'</h2>'
		.'<p>';
	echo implode('<br>', $addressValues);
	echo '</p>
		<h2>'.esc_html__('Information about your property', 'onoffice-for-wp-websites').'</h2>
		<p>';
	echo implode('<br>', $estateValues);
	echo '</p>';

	include(ONOFFICE_PLUGIN_DIR.'/templates.dist/form/formsubmit.php');
}
?>

</form>