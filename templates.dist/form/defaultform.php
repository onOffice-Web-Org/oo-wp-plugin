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

<h3>
	<?php
   /** @var \onOffice\WPlugin\Form $pForm */
    echo $pForm->getEstateContextLabel();
    ?>
</h3>

<form method="post" id="onoffice-form">
	<input type="hidden" name="oo_formid" value="<?php echo $pForm->getFormId(); ?>">
	<input type="hidden" name="oo_formno" value="<?php echo $pForm->getFormNo(); ?>">
	<?php if ( isset( $estateId ) ) : ?>
	<input type="hidden" name="Id" value="<?php echo esc_attr($estateId); ?>">
	<?php endif; ?>

<?php

if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_SUCCESS) {
	echo esc_html__('SUCCESS!', 'onoffice');
} else {
	if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_ERROR) {
		echo esc_html__('ERROR!', 'onoffice');
	} elseif ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_RECAPTCHA_SPAM) {
		echo esc_html__('Spam detected!', 'onoffice');
	}

	/* @var $pForm \onOffice\WPlugin\Form */
	foreach ( $pForm->getInputFields() as $input => $table ) {
		if ( $pForm->isMissingField( $input ) ) {
			echo esc_html__('Please fill in!', 'onoffice');
		}

		if ( in_array( $input, array('message', 'Id') ) ) {
			continue;
		}

		$isRequired = $pForm->isRequiredField( $input );
		$addition = $isRequired ? '*' : '';
		$typeCurrentInput = $pForm->getFieldType($input);

		if ($typeCurrentInput == onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_BOOLEAN) {
			echo "<div class='oo-control'>";
			echo renderFormField($input, $pForm);
			echo "<label class='oo-control__label' for=".$input.">". $pForm->getFieldLabel($input).$addition."</label>";
			echo "</div>";
		}
		else {
			echo "<label for=".$input.">". $pForm->getFieldLabel($input).$addition.": </label>";
			echo renderFormField($input, $pForm);
		}
	}

	if (array_key_exists('message', $pForm->getInputFields())):
		$isRequiredMessage = $pForm->isRequiredField( 'message' );
		$additionMessage = $isRequiredMessage ? '*' : '';
?>

		<?php
		echo "<label for='message'>";
		esc_html_e('Message', 'onoffice');
		echo $additionMessage; ?>:</label>
		<textarea name="message"><?php echo $pForm->getFieldValue( 'message' ); ?></textarea><br>

<?php
	endif;
	include(ONOFFICE_PLUGIN_DIR.'/templates.dist/form/formsubmit.php');
}
?>
</form>