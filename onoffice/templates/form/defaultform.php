<?php

?>
<form method="post">

	<input type="hidden" name="oo_formid" value="<?php echo $pForm->getFormId(); ?>">
	<input type="hidden" name="oo_formno" value="<?php echo $pForm->getFormNo(); ?>">
	<?php if ( isset( $currentEstate ) ) : ?>
	<input type="hidden" name="Id" value="<?php echo $currentEstate['Id']; ?>">
	<?php endif; ?>

<?php

if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_SUCCESS) {
	echo 'SUCCESS!';
} else {
	if ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_ERROR) {
		echo 'ERROR!';
	}

	/* @var $pForm \onOffice\WPlugin\Form */
	foreach ( $pForm->getInputFields() as $input => $table ) {
		if ( $pForm->isMissingField( $input ) ) {
			echo 'Bitte ausfüllen!';
		}

		if ( in_array( $input, array('message', 'Id') ) ) {
			continue;
		}

		echo $pForm->getFieldLabel( $input ).': <input name="'.$input.'" value="'
			.$pForm->getFieldValue( $input ).'"><br>';
	}
?>
	Nachricht:<br>
	<textarea name="message"><?php echo $pForm->getFieldValue( 'message' ); ?></textarea><br>

	<input type="submit" value="GO!">
<?php
}
?>
</form>