<?php

/**
 *
 *    Copyright (C) 2020  onOffice GmbH
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

use onOffice\WPlugin\EstateDetail;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypes;

/**
 *
 *  Default template
 *
 */

$dontEcho = array("objekttitel", "objektbeschreibung", "lage", "ausstatt_beschr", "sonstige_angaben");
/** @var EstateDetail $pEstates */
?>
<div class="oo-detailview">
	<?php
	$pEstates->resetEstateIterator();
	while ($currentEstate = $pEstates->estateIterator(EstateViewFieldModifierTypes::MODIFIER_TYPE_DEFAULT)) { ?>
		<div class="oo-detailsheadline">
			<h1><?php echo $currentEstate["objekttitel"]; ?></h1>
			<?php if (!empty($currentEstate['vermarktungsstatus'])) { ?>
				<span style="padding:0 15px"><?php echo ucfirst($currentEstate['vermarktungsstatus']); ?></span>
				<?php unset($currentEstate['vermarktungsstatus']); ?>
			<?php } ?>
		</div>
		<div class="oo-details-main">
			<div class="oo-detailsgallery" id="oo-galleryslide">
				<?php
				$estatePictures = $pEstates->getEstatePictures();
				foreach ($estatePictures as $id) {
					printf(
						'<div class="oo-detailspicture" style="background-image: url(\'%s\');"></div>' . "\n",
						esc_url($pEstates->getEstatePictureUrl($id))
					);
				}
				?>
			</div>
			<div class="oo-detailstable">
				<?php
				foreach ($currentEstate as $field => $value) {
					if (is_numeric($value) && 0 == $value) {
						continue;
					}
					if (in_array($field, $dontEcho)) {
						continue;
					}
					if ($value == "") {
						continue;
					}
					echo '<div class="oo-detailslisttd">' . esc_html($pEstates->getFieldLabel($field)) . '</div>' . "\n"
						. '<div class="oo-detailslisttd">'
						. (is_array($value) ? esc_html(implode(', ', $value)) : esc_html($value))
						. '</div>' . "\n";
				} ?>
			</div>

			<?php if ($currentEstate["objektbeschreibung"] !== "") { ?>
				<div class="oo-detailsfreetext">
					<h2><?php esc_html_e('Description', 'onoffice'); ?></h2>
					<?php echo nl2br($currentEstate["objektbeschreibung"]); ?>
				</div>
			<?php } ?>

			<?php if ($currentEstate["lage"] !== "") { ?>
				<div class="oo-detailsfreetext">
					<h2><?php esc_html_e('Location', 'onoffice'); ?></h2>
					<?php echo nl2br($currentEstate["lage"]); ?>
				</div>
			<?php }

			ob_start();
			require('map/map.php');
			$mapContent = ob_get_clean();
			if ($mapContent != '') { ?>
				<div class="oo-detailsmap">
					<h2><?php esc_html_e('Map', 'onoffice'); ?></h2>
					<?php echo $mapContent; ?>
				</div>
			<?php } ?>

			<?php if ($currentEstate["ausstatt_beschr"] !== "") { ?>
				<div class="oo-detailsfreetext">
					<h2><?php esc_html_e('Equipment', 'onoffice'); ?></h2>
					<?php echo nl2br($currentEstate["ausstatt_beschr"]); ?>
				</div>
			<?php } ?>

			<?php if ($currentEstate["sonstige_angaben"] !== "") { ?>
				<div class="oo-detailsfreetext">
					<h2><?php esc_html_e('Other Information', 'onoffice'); ?></h2>
					<?php echo nl2br($currentEstate["sonstige_angaben"]); ?>
				</div>
			<?php } ?>

			<div class="oo-units">
				<?php echo $pEstates->getEstateUnits(); ?>
			</div>
		</div>
		<div class="oo-details-sidebar">
			<div class="oo-asp">
				<h2><?php echo esc_html__('Contact person', 'onoffice'); ?></h2>
				<?php
				$configuredAddressFields = $pEstates->getAddressFields();
				// Remove the fields that receive special treatment.
				// These fields will be handled separately, so that they are displayed grouped together.
				$addressFields = array_diff($configuredAddressFields, [
					'imageUrl',
					'Anrede',
					'Titel',
					'Vorname',
					'Name',
					'Zusatz1', // Company
					'Strasse',
					'Plz',
					'Ort'
				]);

				foreach ($pEstates->getEstateContacts() as $contactData) : ?>
					<?php
					$imageUrl      = $contactData['imageUrl'];
					$formOfAddress = $contactData['Anrede'];
					$title         = $contactData['Titel'];
					$firstName     = $contactData['Vorname'];
					$lastName      = $contactData['Name'];
					$company       = $contactData['Zusatz1'];
					$street        = $contactData['Strasse'];
					$postCode      = $contactData['Plz'];
					$town          = $contactData['Ort'];

					if ($imageUrl) {
						echo '<div class="oo-aspinfo oo-contact-info"><img src="' . esc_html($imageUrl) . '" height="150px"></div>';
					}

					// Output name, depending on available fields.
					$nameComponents = [];
					if ($formOfAddress) {
						$nameComponents[] = $formOfAddress;
					}
					if ($title) {
						$nameComponents[] = $title;
					}
					if ($firstName) {
						$nameComponents[] = $firstName;
					}
					if ($lastName) {
						$nameComponents[] = $lastName;
					}
					$nameOutput = join(" ", $nameComponents);
					if ($nameOutput) {
						echo '<div class="oo-aspinfo oo-contact-info"><p>' . esc_html($nameOutput) . '</p></div>';
					}

					// Output company
					if ($company) {
						echo '<div class="oo-aspinfo oo-contact-info"><p>' . esc_html($company) . '</p></div>';
					}

					// Output address, depending on available fields.
					$streetOutput = "";
					if ($street) {
						$streetOutput = $street;
					}
					$cityComponents = [];
					if ($postCode) {
						$cityComponents[] = $postCode;
					}
					if ($town) {
						$cityComponents[] = $town;
					}
					$cityOutput = join(" ", $cityComponents);
					if ($streetOutput && $cityOutput) {
						echo '<div class="oo-aspinfo oo-contact-info"><p>' . esc_html($streetOutput) . "<br>" . esc_html($cityOutput) . '</p></div>';
					} else if ($streetOutput) {
						echo '<div class="oo-aspinfo oo-contact-info"><p>' . esc_html($streetOutput) . '</p></div>';
					} else if ($cityOutput) {
						echo '<div class="oo-aspinfo oo-contact-info"><p>' . esc_html($cityOutput) . '</p></div>';
					}

					// Output all other configured fields.
					foreach ($addressFields as $field) {
						if (empty($contactData[$field])) {
							continue;
						} elseif (is_array($contactData[$field])) {
							echo '<div class="oo-aspinfo oo-contact-info">';
							foreach ($contactData[$field] as $item) {
								echo '<p>' . esc_html($item) . '</p>';
							}
							echo '</div>';
						} else {
							echo '<div class="oo-aspinfo oo-contact-info"><p>' . esc_html($contactData[$field]) . '</p></div>';
						}
					} ?>
				<?php endforeach; ?>
			</div>
			<div class="oo-asp oo-detailsexpose">
				<?php if ($pEstates->getDocument() != '') : ?>
					<h2><?php esc_html_e('Documents', 'onoffice'); ?></h2>
					<a href="<?php echo $pEstates->getDocument(); ?>">
						<?php esc_html_e('PDF expose', 'onoffice'); ?>
					</a>
				<?php endif; ?>
			</div>

			<?php

			/**
			 * The heading above an embed that links directly to the embedded page.
			 */
			function headingLink($url, $title)
			{
				if ($title) {
					return
						'<a class="player-title" target="_blank" href="' . esc_attr($url) . '">
							<div>' . esc_html($title) . '
								<svg width="0.7em" version="1.1" id="Ebene_1" xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 24 24" xml:space="preserve">
									<style>.st1{fill:none;stroke:#000;stroke-width:2;stroke-linejoin:round;stroke-miterlimit:10}</style>
									<path class="st1" d="M23 13.05V23H1V1h9.95M8.57 15.43L23 1M23 9.53V1h-8.5"/>
								</svg>
							</div>
						</a>';
				} else {
					return '';
				}
			}

			// Videos
			$movieOptions = array('width' => 500); // optional
			$estateMoviePlayers = $pEstates->getMovieEmbedPlayers($movieOptions);
			$estateMovieLinks = $pEstates->getEstateMovieLinks();
			if (!empty($estateMoviePlayers) || !empty($estateMovieLinks)) {
				echo '<div class="oo-asp oo-videos">';
				echo '<h2>' . esc_html__('Videos', 'onoffice') . '</h2>';

				foreach ($estateMoviePlayers as $movieInfos) {
					echo '<div class="oo-video">';
					echo headingLink($movieInfos['url'], $movieInfos['title']);
					echo $movieInfos['player'];
					echo '</div>';
				}

				foreach ($estateMovieLinks as $movieLink) {
					echo '<div class="oo-video">
							<a href="' . esc_attr($movieLink['url']) . '" title="' . esc_attr($movieLink['title']) . '" style="color: #0073aa">'
						. esc_html(!empty($movieLink['title']) ? $movieLink['title'] : $movieLink['type'])
						. '</a>
						</div>';
				}
				echo '</div>';
			}

			// 360° tours (aka Ogulo)
			$oguloOptions = array('width' => 560, 'height' => 315); // optional
			$estateOguloEmbeds = $pEstates->getLinkEmbedPlayers('ogulo', $oguloOptions);
			$estateOguloLinks = $pEstates->getEstateLinks('ogulo');
			if (!empty($estateOguloEmbeds) || !empty($estateOguloLinks)) {
				echo '<div class="oo-asp oo-tours">';
				echo '<h2>' . esc_html__('360° tours', 'onoffice') . '</h2>';

				foreach ($estateOguloEmbeds as $linkInfos) {
					echo '<div class="oo-video">';
					echo headingLink($linkInfos['url'], $linkInfos['title']);
					echo $linkInfos['player'];
					echo '</div>';
				}

				foreach ($estateOguloLinks as $oguloLink) {
					echo '<div class="oo-video">
							<a href="' . esc_attr($oguloLink['url']) . '" title="' . esc_attr(!empty($oguloLink['title']) ? $oguloLink['title'] : $oguloLink['type']) . '" style="color: #0073aa">'
						. esc_html(!empty($oguloLink['title']) ? $oguloLink['title'] : $oguloLink['type'])
						. '</a>
						</div>';
				}
				echo '</div>';
			}

			// Objects
			$objectOptions = array('width' => 560, 'height' => 315); // optional
			$estateObjectEmbeds = $pEstates->getLinkEmbedPlayers('object', $objectOptions);
			$estateObjectLinks = $pEstates->getEstateLinks('object');
			if (!empty($estateObjectEmbeds) || !empty($estateObjectLinks)) {
				echo '<div class="oo-asp oo-objects">';
				echo '<h2>' . esc_html__('Objects', 'onoffice') . '</h2>';

				foreach ($estateObjectEmbeds as $linkInfos) {
					echo '<div class="oo-video">';
					echo headingLink($linkInfos['url'], $linkInfos['title']);
					echo $linkInfos['player'];
					echo '</div>';
				}

				foreach ($estateObjectLinks as $objectLink) {
					echo '<div class="oo-video">
							<a href="' . esc_attr($objectLink['url']) . '" title="' . esc_attr(!empty($objectLink['title']) ? $objectLink['title'] : $objectLink['type']) . '" style="color: #0073aa">'
						. esc_html(!empty($objectLink['title']) ? $objectLink['title'] : $objectLink['type'])
						. '</a>
						</div>';
				}
				echo '</div>';
			}

			// Links
			$linkOptions = array('width' => 560, 'height' => 315); // optional
			$estateLinkEmbeds = $pEstates->getLinkEmbedPlayers('link', $linkOptions);
			$estateLinks = $pEstates->getEstateLinks('link');
			if (!empty($estateLinkEmbeds) || !empty($estateLinks)) {
				echo '<div class="oo-asp oo-links">';
				echo '<h2>' . esc_html__('Links', 'onoffice') . '</h2>';

				foreach ($estateLinkEmbeds as $linkInfos) {
					echo '<div class="oo-video">';
					echo headingLink($linkInfos['url'], $linkInfos['title']);
					echo $linkInfos['player'];
					echo '</div>';
				}

				foreach ($estateLinks as $link) {
					echo '<div class="oo-video">
							<a href="' . esc_attr($link['url']) . '" title="' . esc_attr(!empty($link['title']) ? $link['title'] : $link['type']) . '" style="color: #0073aa">'
						. esc_html(!empty($link['title']) ? $link['title'] : $link['type'])
						. '</a>
						</div>';
				}
				echo '</div>';
			}
			?>

		</div>
		<?php
		if (get_option('onoffice-pagination-paginationbyonoffice')) { ?>
			<div>
				<?php
				wp_link_pages();
				?>
			</div>
		<?php } ?>
		<div class="oo-similar">
			<?php echo $pEstates->getSimilarEstates(); ?>
		</div>
	<?php } ?>

</div>

<?php
$shortCodeForm = $pEstates->getShortCodeForm();
if (!empty($shortCodeForm)) {
?>
	<div class="detail-contact-form">
		<?php echo do_shortcode($shortCodeForm); ?>
	</div>
<?php } ?>
<style>
	.oo-video .player-title {
		text-decoration: none;
		color: #000;
	}
</style>