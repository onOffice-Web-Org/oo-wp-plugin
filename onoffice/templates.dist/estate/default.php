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
 *
 *  Default template
 *
 */

use onOffice\WPlugin\Favorites;

// display search form
require 'SearchForm.php';

/* @var $pEstates onOffice\WPlugin\EstateList */


require('estatemap.php');

?>

<?php if (Favorites::isFavorizationEnabled()): ?>
<script>
	$(document).ready(function() {
		onofficeFavorites = new onOffice.favorites(<?php echo json_encode(Favorites::COOKIE_NAME); ?>);
		onOffice.addFavoriteButtonLabel = function(i, element) {
			var estateId = $(element).attr('data-onoffice-estateid');
			if (!onofficeFavorites.favoriteExists(estateId)) {
				$(element).text('<?php echo esc_js('Add to '.Favorites::getFavorizationLabel(), 'onoffice'); ?>');
				$(element).on('click', function() {
					onofficeFavorites.add(estateId);
					onOffice.addFavoriteButtonLabel(0, element);
				});
			} else {
				$(element).text('<?php echo esc_js('Remove from '.Favorites::getFavorizationLabel(), 'onoffice'); ?>');
				$(element).on('click', function() {
					onofficeFavorites.remove(estateId);
					onOffice.addFavoriteButtonLabel(0, element);
				});
			}
		};
		$('button.onoffice.favorize').each(onOffice.addFavoriteButtonLabel);
	});
</script>
<?php endif ?>
<h1>Übersicht der dargestellten Objekte</h1>

<p>Insgesamt <?php echo $pEstates->getEstateOverallCount(); ?> Objekte gefunden.</p>

<?php
$pEstates->resetEstateIterator();
while ( $currentEstate = $pEstates->estateIterator() ) : ?>

<p>
	<a href="<?php echo $pEstates->getEstateLink(); ?>">Zur Detailansicht</a><br>
	<?php foreach ( $currentEstate as $field => $value ) :
		if ( is_numeric( $value ) && 0 == $value ) {
			continue;
		}
	?>

		<?php echo $pEstates->getFieldLabel( $field ) .': '.(is_array($value) ? implode(', ', $value) : $value); ?><br>

	<?php endforeach; ?>


	<?php
	foreach ( $pEstates->getEstateContacts() as $contactData ) : ?>
	<p>
		<b>ASP: <?php echo $contactData['Vorname']; ?> <?php echo $contactData['Name']; ?></b><br>
		<img src="<?php echo $contactData['imageUrl']; ?>">
		<ul>
			<?php // either use the phone number flagged as default (add `default*` to config) ... ?>
			<!--<li>Telefon: <?php // echo $contactData['defaultphone']; ?></li>-->
			<!--<li>Telefax: <?php // echo $contactData['defaultfax']; ?></li>-->
			<!--<li>E-Mail: <?php // echo $contactData['defaultemail']; ?></li>-->


			<?php // ... or the specific one (add `mobile`, `phone`, `email` to config): ?>
			<?php
			$mobilePhoneNumbers = $contactData->offsetExists('mobile') ? $contactData->getValueRaw('mobile') : array();
			if (count($mobilePhoneNumbers) > 0) :
			?>
				<li>Mobil: <?php echo esc_html(array_shift($mobilePhoneNumbers)); ?></li>
			<?php endif; ?>
			<?php
			$businessPhoneNumbers = $contactData->offsetExists('phonebusiness') ?
				$contactData->getValueRaw('phonebusiness') : array();
			if (count($businessPhoneNumbers) > 0) :
			?>
				<li>phone business: <?php echo esc_html(array_shift($businessPhoneNumbers)); ?></li>
			<?php endif; ?>
			<?php
			$businessEmailAddresses = $contactData->offsetExists('emailbusiness') ?
				$contactData->getValueRaw('emailbusiness') : array();
			if (count($businessEmailAddresses) > 0) :
			?>
				<li>email business: <?php echo esc_html(array_shift($businessEmailAddresses)); ?></li>
			<?php endif; ?>
		</ul>
	</p>
	<?php endforeach; ?>

	<p><b>Kontaktformular:</b>
		<?php
			$pForm = new \onOffice\WPlugin\Form('Contactform', \onOffice\WPlugin\Form::TYPE_CONTACT);

			include( __DIR__ . "/../form/defaultform.php" );
		?>
	</p>


	<?php
	$estatePictures = $pEstates->getEstatePictures();

	foreach ( $estatePictures as $id ) : ?>
	<a href="<?php echo $pEstates->getEstatePictureUrl( $id ); ?>">
		<img src="<?php echo $pEstates->getEstatePictureUrl( $id, array('width' => 400, 'height' => 300) ); ?>">
	</a>
	<?php endforeach; ?>
</p>

<?php if (Favorites::isFavorizationEnabled()): ?>
	<button data-onoffice-estateid="<?php echo $pEstates->getCurrentMultiLangEstateMainId(); ?>" class="onoffice favorize">
		<?php esc_html_e('Add to '.Favorites::getFavorizationLabel(), 'onoffice'); ?>
	</button>
<?php endif ?>
<?php endwhile; ?>