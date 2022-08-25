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

?>
<form method="post" id="onoffice-form">

	<input type="hidden" name="oo_formid" value="<?php echo $pForm->getFormId(); ?>">
	<input type="hidden" name="oo_formno" value="<?php echo $pForm->getFormNo(); ?>">

<?php

$addressValues = array();
$searchcriteriaValues = array();

if ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_SUCCESS) {
	echo '<p>'.esc_html__('SUCCESS!', 'onoffice-for-wp-websites').'</p>';
} elseif ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_ERROR) {
	echo '<p>'.esc_html__('ERROR!', 'onoffice-for-wp-websites').'</p>';
} elseif ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_REQUIRED_FIELDS_MISSING) {
	echo '<p>'.esc_html__('Missing Fields!', 'onoffice-for-wp-websites').'</p>';
}

/* @var $pForm \onOffice\WPlugin\Form */
foreach ( $pForm->getInputFields() as $input => $table ) {
	$isRequired = $pForm->isRequiredField( $input );
	$addition = $isRequired ? '*' : '';
	$line = $pForm->getFieldLabel( $input ).$addition.': ';
	$line .= renderFormField($input, $pForm);

	if ( $pForm->isMissingField( $input ) ) {
		$line .= '<span>'.esc_html__('Please fill in', 'onoffice-for-wp-websites').'</span>';
	}

	if ($table == 'address') {
		$addressValues []= $line;
	}

	if ($table == 'searchcriteria') {
		$searchcriteriaValues []= $line;
	}

	if ($table == '') {
		$addressValues []= $line;
	}

}

if ($pForm->getFormStatus() !== \onOffice\WPlugin\FormPost::MESSAGE_SUCCESS) {

?>
	<p>
	<h1><?php esc_html_e('Your contact details', 'onoffice'); ?></h1>
		<div>
			<?php echo implode('<br>', $addressValues); ?>
		</div>
	</p>
	<p>
	<h1>Ihre Suchkriterien</h1>
		<div>
			<?php echo implode('<br>', $searchcriteriaValues) ?>
		</div>
	</p>
	<div>
		<?php
			include(ONOFFICE_PLUGIN_DIR.'/templates.dist/form/formsubmit.php');
		 ?>
	</div>
<?php
}
?>
</form>
