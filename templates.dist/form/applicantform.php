<?php
/**
 *
 *    Copyright (C) 2016-2019 onOffice GmbH
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
$displayError = false;
?>
<form method="post" id="onoffice-form" class="oo-form oo-form-applicant" novalidate>

	<input type="hidden" name="oo_formid" value="<?php echo $pForm->getFormId(); ?>">
	<input type="hidden" name="oo_formno" value="<?php echo $pForm->getFormNo(); ?>">

<?php

$addressValues = array();
$searchcriteriaValues = array();
$hiddenValues  = array();
$otherValues = [];

if ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_SUCCESS) {
	echo '<p role="status">'.esc_html__('Thank you for your inquiry. We will get back to you as soon as possible.', 'onoffice-for-wp-websites').'</p>';
} elseif ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_ERROR) {
	echo '<p role="status">'.esc_html__('An error has occurred. Please check your details.', 'onoffice-for-wp-websites').'</p>';
} elseif ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_REQUIRED_FIELDS_MISSING) {
	echo '<p role="status">'.esc_html__('Not all mandatory fields have been filled out. Please check your entries.', 'onoffice-for-wp-websites').'</p>';
	$displayError = true;
} elseif ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_RECAPTCHA_SPAM) {
	echo '<p role="status">'.esc_html__('Spam recognized!', 'onoffice-for-wp-websites').'</p>';
}

	$firstRequired = false;
	$hasRequiredFields = false;

	foreach ($pForm->getInputFields() as $input => $table) {
		if (
			$pForm->isRequiredField($input)
		) {
			$hasRequiredFields = true;
			break;
		}
	}
	if ($hasRequiredFields) {
		echo '<div class="oo-form-required" aria-hidden="true">' . esc_html__('* Mandatory fields', 'onoffice-for-wp-websites') . '</div>';
	}


/* @var $pForm \onOffice\WPlugin\Form */
foreach ( $pForm->getInputFields() as $input => $table ) {
	if ($pForm->isHiddenField($input)) {
		$hiddenValues []= renderFormField($input, $pForm);
		continue;
	}
	$isRequired = $pForm->isRequiredField( $input );
	$addition   = $isRequired ? '<span class="oo-visually-hidden">'.esc_html__('Pflichtfeld', 'onoffice-for-wp-websites').'</span><span aria-hidden="true">*</span>' : '';
	$searchcriteriaLine = '';

	if ( in_array( $input, array( 'kaufpreis','kaltmiete','wohnflaeche','anzahl_zimmer' ) ) ) {
		$searchcriteriaLine .= '<div class="oo-input-wrapper">';
		$searchcriteriaLine .= renderFormField($input, $pForm).'</div>';
	}

	$isRequired = $pForm->isRequiredField($input);
	$addition   = $isRequired ? '<span class="oo-visually-hidden">'.esc_html__('Pflichtfeld', 'onoffice-for-wp-websites').'</span><span aria-hidden="true">*</span>' : '';
	$line = '<label>'.$pForm->getFieldLabel($input).' '.$addition;
	$line .= renderFormField($input, $pForm).'</span></label>';



	
	if (
        in_array($input, ['gdprcheckbox', 'Id']) ||
        in_array($input, ['newsletter', 'Id']) ||
        in_array($input, ['krit_bemerkung_oeffentlich', 'Id']) ||
        in_array($input, ['message', 'Id']) ||
        in_array($input, ['AGB_akzeptiert', 'Id']) ||
        in_array($input, ['Rueckruf_akzeptiert', 'Id'])
    ) {
        $table = 'other';
    }

	if ( in_array( $input, array( 'gdprcheckbox' ) ) ) {
		$line = '<label><span class="oo-label-text ' . ($displayError && $isRequired ? ' displayerror' : '') . '">'.renderFormField( 'gdprcheckbox', $pForm );
		$line .= $pForm->getFieldLabel( 'gdprcheckbox' ) .' '. $addition.'</span></label>';
	}

	if ( in_array( $input, array( 'message' )) ) {
		$isRequiredMessage = $pForm->isRequiredField( 'message' );
		$additionMessage = $isRequiredMessage ? '<span class="oo-visually-hidden">'.esc_html__('Pflichtfeld', 'onoffice-for-wp-websites').'</span><span aria-hidden="true">*</span>' : '';
		$isHiddenField = $pForm->isHiddenField('message');
		if (!$isHiddenField) {
			$line = '<label class="' . ($displayError && $isRequired ? ' displayerror' : '') . '">'.$pForm->getFieldLabel( 'message' );
			$line .= $additionMessage;
			$line .= '<textarea name="message" autocomplete="off"' . ($isRequiredMessage ? ' required aria-required="true" aria-invalid="false"' : '') . '>' . $pForm->getFieldValue('message') . '</textarea></label>';

		} else {
			$line = '<input type="hidden" name="message" value="' . $pForm->getFieldValue('message') . '">';
		}
	}

	if ($table == 'address') {
		$addressValues []= $line;
	}

	if ($table == 'searchcriteria') {
		$searchcriteriaValues []= $searchcriteriaLine;
	}

	if ($table == '') {
		$addressValues []= $line;
	}

	if ($table == 'other') {
        $otherValues[] = $line;
    }

}
if ($pForm->getFormStatus() !== \onOffice\WPlugin\FormPost::MESSAGE_SUCCESS) {

?>
	<h2><?php esc_html_e('Your contact details', 'onoffice-for-wp-websites'); ?></h2>
		<?php if (is_array($addressValues)) {
            echo implode($addressValues);
        } ?>
	<h2><?php esc_html_e('Your search criteria', 'onoffice-for-wp-websites'); ?></h2>
		<?php if (is_array($searchcriteriaValues)) {
            echo implode($searchcriteriaValues);
        } ?>
			<?php if (is_array($otherValues)) {
				echo implode($otherValues);
			} ?>
		<?php if (is_array($hiddenValues)) {
            echo implode($hiddenValues);
        } ?>
		<?php
			include(ONOFFICE_PLUGIN_DIR.'/templates.dist/form/formsubmit.php');
		 ?>
	</div>
<?php
}
?>
</form>
