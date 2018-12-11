<?php

/**
 *
 *    Copyright (C) 2018  onOffice Software AG
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

require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'fields.php';

$visible = $pAddressList->getVisibleFilterableFields();

if (count($visible) === 0) {
	return;
}

?>

<form method="get">

<?php

foreach ($visible as $inputName => $properties) :
	echo '<p>';
	echo esc_html($properties['label']).': ';
	echo '<br>';
	renderFieldEstateSearch($inputName, $properties);
	echo '</p>';
endforeach;
?>

	<input type="submit" value="<?php echo esc_attr__('Send', 'onoffice'); ?>">
</form>
<br>