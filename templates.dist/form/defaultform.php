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
	echo esc_html__('SUCCESS!', 'onoffice-for-wp-websites');
} else {
	if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_ERROR) {
		echo esc_html__('ERROR!', 'onoffice-for-wp-websites');
	} elseif ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_RECAPTCHA_SPAM) {
		echo esc_html__('Spam detected!', 'onoffice-for-wp-websites');
	}

	/* @var $pForm \onOffice\WPlugin\Form */
	foreach ( $pForm->getInputFields() as $input => $table ) {
		if ( $pForm->isMissingField( $input ) ) {
			echo esc_html__('Please fill in!', 'onoffice-for-wp-websites');
		}

		if ( in_array( $input, array( 'Id' ) ) ) {
			continue;
		}
		if ( in_array( $input, array( 'gdprcheckbox' ) ) ) {
			echo renderFormField( 'gdprcheckbox', $pForm );
			echo $pForm->getFieldLabel( 'gdprcheckbox' ) . '<br>';
			continue;
		}
		if ( in_array( $input, array( 'message' ) ) ) {
			$isRequiredMessage = $pForm->isRequiredField( 'message' );
			$additionMessage   = $isRequiredMessage ? '*' : '';
			echo $pForm->getFieldLabel( 'message' );
			echo $additionMessage . ':<br>';
			echo '<textarea name="message">' . $pForm->getFieldValue( 'message' ) . '</textarea><br>';
			continue;
		}

		$isRequired = $pForm->isRequiredField( $input );
		$addition = $isRequired ? '*' : '';
		echo $pForm->getFieldLabel($input).$addition.': ';
		echo renderFormField($input, $pForm).'<br>';
	}
?>

<?php
	echo '<br>';

	include(ONOFFICE_PLUGIN_DIR.'/templates.dist/form/formsubmit.php');
}
?>
</form>