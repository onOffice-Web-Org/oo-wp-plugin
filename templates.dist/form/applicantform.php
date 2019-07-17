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
$searchcriteriaValues = array();

if ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_SUCCESS)
{
	echo '<p>SUCCESS!</p>';
}

if ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_ERROR)
{
	echo '<p>ERROR!</p>';
}

if ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_REQUIRED_FIELDS_MISSING)
{
	echo '<p>Missing Fields!</p>';
}

/* @var $pForm \onOffice\WPlugin\Form */
foreach ( $pForm->getInputFields() as $input => $table ) {
	$isRequired = $pForm->isRequiredField( $input );
	$addition = $isRequired ? '*' : '';
	$line = $pForm->getFieldLabel( $input ).$addition.': ';
	$line .= renderFormField($input, $pForm);

	if ( $pForm->isMissingField( $input ) ) {
		$line .= sprintf('<span>%s</span>', esc_html__('Bitte ausfüllen!', 'onoffice'));
	}

	if ($table == 'address') {
		$addressValues []= $line;
	}

	if ($table == 'searchcriteria') {
		$searchcriteriaValues []= $line;
	}
}


?>
	<p>
	<h1>Ihre Kontaktdaten</h1>
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
		<?php include(ONOFFICE_PLUGIN_DIR.'/templates.dist/form/formsubmit.php'); ?>
	</div>
</form>