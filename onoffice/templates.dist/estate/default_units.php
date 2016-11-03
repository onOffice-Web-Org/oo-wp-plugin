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

/**
 *  Default template
 */

/* @var $pEstates onOffice\WPlugin\EstateList */

?>
<h2>Einheiten!</h2>
<?php while ( $currentEstate = $pEstates->estateIterator() ) : ?>
	<?php foreach ( $currentEstate as $field => $value ) :
		if ( is_numeric( $value ) && 0 == $value ) {
			continue;
		}
	?>
		<?php echo $pEstates->getFieldLabel( $field ) .': '.$value; ?><br>

	<?php endforeach; ?>
	<h3>Einheit-ASP:</h3>
	<?php foreach ( $pEstates->getEstateContacts() as $contactData ) : ?>
	<ul>
		<b>ASP: <?php echo $contactData['Vorname']; ?> <?php echo $contactData['Name']; ?></b>
		<li>Telefon: <?php echo $contactData['defaultphone']; ?></li>
		<li>Telefax: <?php echo $contactData['defaultfax']; ?></li>
		<li>E-Mail: <?php echo $contactData['defaultemail']; ?></li>
	</ul>
<?php endforeach; ?>
<br>

<?php endwhile; ?>
<br>
