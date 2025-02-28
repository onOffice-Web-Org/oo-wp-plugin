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
use onOffice\WPlugin\EstateCostsChart;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypes;
/**
 *
 *  Default template
 *
 */

$dontEcho = array("objekttitel", "objektbeschreibung", "lage", "ausstatt_beschr", "sonstige_angaben", "MPAreaButlerUrlWithAddress", "MPAreaButlerUrlNoAddress");
$supportTypeLinkFields = array('Homepage', 'facebook', 'instagram', 'linkedin', 'pinterest', 'tiktok', 'twitter', 'xing', 'youtube', 'bewertungslinkWebseite');
/** @var EstateDetail $pEstates */

/*  responsive picture properties
 *  customizable widths and heights for individual layouts
 */
$image_width_xs = 382;
$image_width_sm = 740;
$image_width_md = 960;
$image_width_lg = 870;
$image_width_xl = 1020;
$image_width_xxl = 1170;
$image_width_xxxl = 1400;
$image_height_xs = null;
$image_height_sm = null;
$image_height_md = null;
$image_height_lg = null;
$image_height_xl = null;
$image_height_xxl = null;
$image_height_xxxl = null;

$dimensions = [
    '575' => [
        'w' => $image_width_xs,
        'h' => $image_height_xs
    ],
    '1600' => [
        'w' => $image_width_xxxl,
        'h' => $image_height_xxxl
    ],
    '1400' => [
        'w' => $image_width_xxl,
        'h' => $image_height_xxl
    ],
    '1200' => [
        'w' => $image_width_xl,
        'h' => $image_height_xl
    ],
    '992' => [
        'w' => $image_width_lg,
        'h' => $image_height_lg
    ],
    '768' => [
        'w' => $image_width_md,
        'h' => $image_height_md
    ],
    '576' => [
        'w' => $image_width_sm,
        'h' => $image_height_sm
    ]
];
?>

<div class="oo-detailview">
	<?php
	$pEstates->resetEstateIterator();
	while ($currentEstate = $pEstates->estateIterator(EstateViewFieldModifierTypes::MODIFIER_TYPE_DEFAULT)) {
		$estateId = $pEstates->getCurrentEstateId();
		$rawValues = $pEstates->getRawValues();
		$energyCertificateFields = ["baujahr","endenergiebedarf","energieverbrauchskennwert","energieausweistyp","energieausweis_gueltig_bis","energyClass","energietraeger"];
		?>
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
					echo '<div class="oo-detailspicture">';
                    echo '<picture class="oo-picture">';
                    /**
                     * getResponsiveImageSource(
                     * @param int $imageId
                     * @param int $mediaBreakPoint
                     * @param float|null $imageWidth for media breakpoint, optional
                     * @param float|null $imageHeight for media breakpoint, optional
                     * @param bool $maxWidth, default = false)
                     * @return string for image source on various media
                     */
                    echo $pEstates->getResponsiveImageSource($id, 575, $dimensions['575']['w'], $dimensions['575']['h'], true);
                    echo $pEstates->getResponsiveImageSource($id, 1600, $dimensions['1600']['w'], $dimensions['1600']['h']);
                    echo $pEstates->getResponsiveImageSource($id, 1400, $dimensions['1400']['w'], $dimensions['1400']['h']);
                    echo $pEstates->getResponsiveImageSource($id, 1200, $dimensions['1200']['w'], $dimensions['1200']['h']);
                    echo $pEstates->getResponsiveImageSource($id, 992, $dimensions['992']['w'], $dimensions['992']['h']);
                    echo $pEstates->getResponsiveImageSource($id, 768, $dimensions['768']['w'], $dimensions['768']['h']);
                    echo $pEstates->getResponsiveImageSource($id, 576, $dimensions['576']['w'], $dimensions['576']['h']);
                    echo '<img class="oo-responsive-image estate-status" ' .
                        'src="' . esc_url($pEstates->getEstatePictureUrl($id, isset($dimensions['1600']['w']) || isset($dimensions['1600']['h']) ? ['width'=> $dimensions['1600']['w'], 'height'=>$dimensions['1600']['h']] : null)) . '" ' .
                        'alt="' . esc_html($pEstates->getEstatePictureTitle($id) ?? __('Image of property', 'onoffice-for-wp-websites')) . '" ' .
                        'loading="lazy"/>';
                    echo '</picture>';;
					echo '</div>';
				}
				?>
			</div>
			<div class="oo-detailstable">
				<?php
				foreach ($currentEstate as $field => $value) {
					if ($pEstates->getShowEnergyCertificate() && in_array($field, $energyCertificateFields)) {
						continue;
					}
					if (is_numeric($value) && 0 == $value) {
						continue;
					}
					if (in_array($field, $dontEcho)) {
						continue;
					}
					if (empty($value)) {
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
					<h2><?php echo esc_html($pEstates->getFieldLabel('objektbeschreibung')); ?></h2>
					<?php echo nl2br($currentEstate["objektbeschreibung"]); ?>
				</div>
			<?php } ?>

			<?php if ($currentEstate["lage"] !== "") { ?>
				<div class="oo-detailsfreetext">
					<h2><?php echo esc_html($pEstates->getFieldLabel('lage')); ?></h2>
					<?php echo nl2br($currentEstate["lage"]); ?>
				</div>
			<?php } ?>

			<?php
			$areaButlerUrl = !empty($currentEstate['MPAreaButlerUrlWithAddress']) ? $currentEstate['MPAreaButlerUrlWithAddress'] : ($currentEstate['MPAreaButlerUrlNoAddress'] ?? '');
			if (!empty($areaButlerUrl)) { ?>
				<div class="oo-area-butler">
					<h2><?php esc_html_e('AreaButler', 'onoffice-for-wp-websites'); ?></h2>
					<iframe class="oo-area-butler-iframe" src="<?php echo esc_url($areaButlerUrl); ?>"></iframe>
				</div>
			<?php } ?>

			<?php
			ob_start();
			require('map/map.php');
			$mapContent = ob_get_clean();
			if ($mapContent != '') { ?>
				<div class="oo-detailsmap">
					<h2><?php esc_html_e('Map', 'onoffice-for-wp-websites'); ?></h2>
					<?php echo $mapContent; ?>
				</div>
			<?php } ?>
			<?php if ($pEstates->getShowEnergyCertificate()) {
				$energyClass = $rawValues->getValueRaw($estateId)['elements']['energyClass'] ?? '';
				$energyClassPermittedValues = $pEstates->getPermittedValues('energyClass');
				$energyCertificateType = $rawValues->getValueRaw($estateId)['elements']['energieausweistyp'] ?? '';
				$energyCertificateValueRanges = [
					"Endenergiebedarf" => ["0", "25", "50", "75", "100", "125", "150", "175", "200", ">200"],
					"Energieverbrauchskennwert" => ["0", "50", "100", "150", "200", "250", "300", "350", "400"]
				];
			?>
				<div class="oo-details-energy-certificate">
					<h2><?php echo esc_html($pEstates->getFieldLabel('energieausweistyp')); ?></h2>
					<?php
					function renderEnergyCertificate(string $energyCertificateType, array $energyClassPermittedValues, string $selectedEnergyClass, string $type, array $labels) {
						if ($energyCertificateType === $type) { ?>
							<div class="energy-certificate-container">
								<div class="segmented-bar">
									<?php
									foreach ($energyClassPermittedValues as $key => $label) {
										$labelIndex = array_keys($energyClassPermittedValues)[$key];
										echo '<div class="energy-certificate-label"><span>' . $labels[$labelIndex] . '</span></div>';
										echo '<div class="segment' . ($selectedEnergyClass == $label ? ' selected' : '') . '"><span>' . $label . '</span></div>';
									}
									if ($type === "Endenergiebedarf") {
										echo '<div class="energy-certificate-label"><span>'.end($labels).'</span></div>';
									}
									?>
								</div>
							</div>
							<?php
						}
					}
					if (!empty($energyClassPermittedValues) && !empty($energyClass) && !empty($energyCertificateType)) {
						foreach ($energyCertificateValueRanges as $type => $labels) {
							renderEnergyCertificate($energyCertificateType, $energyClassPermittedValues, $energyClass, $type, $labels);
						}
					}
					?>
					<div class="oo-detailstable">
						<?php
						$fields = [
							'baujahr',
							'energieausweistyp',
							'energieausweis_gueltig_bis',
							'energyClass',
							'energietraeger'
						];

						if ($energyCertificateType === "Endenergiebedarf") {
							$fields[] = 'endenergiebedarf';
						} elseif ($energyCertificateType === "Energieverbrauchskennwert") {
							$fields[] = 'energieverbrauchskennwert';
						}

						foreach ($fields as $field) {
							if (empty($currentEstate[$field])) {
								continue;
							}

							echo '<div class="oo-detailslisttd">' . esc_html($pEstates->getFieldLabel($field)) . '</div>' . "\n"
								. '<div class="oo-detailslisttd">'
								. (is_array($currentEstate[$field]) ? esc_html(implode(', ', $currentEstate[$field])) : esc_html($currentEstate[$field]))
								. '</div>' . "\n";
						}
						?>
					</div>
				</div>
			<?php } ?>

			<?php if ($currentEstate["ausstatt_beschr"] !== "") { ?>
				<div class="oo-detailsfreetext">
					<h2><?php echo esc_html($pEstates->getFieldLabel('ausstatt_beschr')); ?></h2>
					<?php echo nl2br($currentEstate["ausstatt_beschr"]); ?>
				</div>
			<?php } ?>

			<?php if ($currentEstate["sonstige_angaben"] !== "") { ?>
				<div class="oo-detailsfreetext">
					<h2><?php echo esc_html($pEstates->getFieldLabel('sonstige_angaben')); ?></h2>
					<?php echo nl2br($currentEstate["sonstige_angaben"]); ?>
				</div>
			<?php } ?>

			<?php if (!empty($pEstates->getTotalCostsData())) {
				$totalCostsData = $pEstates->getTotalCostsData();

				?>
				<div class="oo-detailspricecalculator">
					<h2><?php esc_html_e('Total costs', 'onoffice-for-wp-websites'); ?></h2>
					<div class="oo-costs-container">
						<div class="oo-donut-chart">
						<?php
							$values = [$totalCostsData['kaufpreis']['raw'], $totalCostsData['bundesland']['raw'], $totalCostsData['aussen_courtage']['raw'],$totalCostsData['notary_fees']['raw'], $totalCostsData['land_register_entry']['raw']];
							$valuesTitle = [$totalCostsData['kaufpreis']['default'], $totalCostsData['bundesland']['default'], $totalCostsData['aussen_courtage']['default'],$totalCostsData['notary_fees']['default'], $totalCostsData['land_register_entry']['default']];
							$chart = new EstateCostsChart($values, $valuesTitle);
							echo $chart->generateSVG(); ?>
						</div>
						<div class="oo-costs-overview">
							<h3><?php esc_html_e('Overview of costs', 'onoffice-for-wp-websites'); ?></h3>
							<div class="oo-costs-item">
								<span class="color-indicator oo-donut-chart-color0"></span>
								<div class="oo-price-label">
									<div><b><?php echo esc_html($pEstates->getFieldLabel('kaufpreis')); ?></b></div>
									<div><b><?php echo esc_html($totalCostsData['kaufpreis']['default']); ?></b></div>
								</div>
							</div>
							<div class="oo-costs-item">
								<span class="color-indicator oo-donut-chart-color1"></span>
								<div class="oo-price-label">
									<div><?php esc_html_e('Property transfer tax', 'onoffice-for-wp-websites'); ?></div>
									<div><?php echo esc_html($totalCostsData['bundesland']['default']); ?></div>
								</div>
							</div>
							<div class="oo-costs-item">
								<span class="color-indicator oo-donut-chart-color2"></span>
								<div class="oo-price-label">
									<div><?php esc_html_e('Broker commission', 'onoffice-for-wp-websites'); ?></div>
									<div><?php echo esc_html($totalCostsData['aussen_courtage']['default']); ?></div>
								</div>
							</div>
							<div class="oo-costs-item">
								<span class="color-indicator oo-donut-chart-color3"></span>
								<div class="oo-price-label">
									<div><?php esc_html_e('Notary Fees', 'onoffice-for-wp-websites'); ?></div>
									<div><?php echo esc_html($totalCostsData['notary_fees']['default']); ?></div>
								</div>
							</div>
							<div class="oo-costs-item">
								<span class="color-indicator oo-donut-chart-color4"></span>
								<div class="oo-price-label">
									<div><?php esc_html_e('Land Register Entry', 'onoffice-for-wp-websites'); ?></div>
									<div><?php echo esc_html($totalCostsData['land_register_entry']['default']); ?></div>
								</div>
							</div>
							<div>
								<div class="oo-price-label">
									<div class="oo-total-costs-label"><b><?php esc_html_e('Total costs', 'onoffice-for-wp-websites'); ?></b></div>
									<div><b><?php echo esc_html($totalCostsData['total_costs']['default']); ?></b></div>
								</div>
							</div>
						</div>
					</div>
					<div class="oo-costs-notice">
						<?php echo esc_html__('A standard value of 1.5% and 0.5% is usually used to calculate notary and land registry costs.', 'onoffice-for-wp-websites'); ?>
					</div>
				</div>
			<?php } ?>

			<div class="oo-units">
				<?php echo $pEstates->getEstateUnits(); ?>
			</div>
		</div>
		<div class="oo-details-sidebar">
			<div class="oo-asp">
				<h2><?php echo esc_html__('Contact person', 'onoffice-for-wp-websites'); ?></h2>
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
					<div class="oo-aspinfo-wrapper">
					<?php
					$imageUrl      = $contactData['imageUrl'];
					$imageAlt      = !empty($contactData['imageAlt']) ? $contactData['imageAlt'] : esc_html__('Contact person', 'onoffice-for-wp-websites');
					$formOfAddress = $contactData['Anrede'];
					$title         = $contactData['Titel'];
					$firstName     = $contactData['Vorname'];
					$lastName      = $contactData['Name'];
					$company       = $contactData['Zusatz1'];
					$street        = $contactData['Strasse'];
					$postCode      = $contactData['Plz'];
					$town          = $contactData['Ort'];

					if ($imageUrl) {
						echo '<div class="oo-aspinfo oo-contact-info"><img src="' . esc_html($imageUrl) . '" height="150px" alt="' . esc_html($imageAlt) . '"></div>';
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
							echo '<div class="oo-field-label">'. esc_html($pEstates->getFieldLabel($field)) .'</div>';
							echo '<div class="oo-aspinfo oo-contact-info">';
							foreach ($contactData[$field] as $item) {
								echo '<p>' . esc_html($item) . '</p>';
							}
							echo '</div>';
						} else if (in_array($field, $supportTypeLinkFields)) {
							echo '<div class="oo-field-label">'. esc_html($pEstates->getFieldLabel($field)) .'</div>';
							echo '<div class="oo-aspinfo oo-contact-info"><a href="' . esc_url($contactData[$field]) . '" target="_blank" rel="nofollow noopener" aria-label="Link to ' . esc_attr($pEstates->getFieldLabel($field)) . '">' . esc_html($contactData[$field]) . '</a></div>';
						} else {
							echo '<div class="oo-field-label">'. esc_html($pEstates->getFieldLabel($field)) .'</div>';
							echo '<div class="oo-aspinfo oo-contact-info"><p>' . esc_html($contactData[$field]) . '</p></div>';
						}
					} ?>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="oo-asp oo-detailsexpose">
				<?php if ($pEstates->getDocument() != '') : ?>
					<h2><?php esc_html_e('Documents', 'onoffice-for-wp-websites'); ?></h2>
					<a href="<?php echo $pEstates->getDocument(); ?>">
						<?php esc_html_e('PDF expose', 'onoffice-for-wp-websites'); ?>
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
								<svg width="0.7em" id="Ebene_1" xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 24 24" xml:space="preserve">
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
				echo '<h2>' . esc_html__('Videos', 'onoffice-for-wp-websites') . '</h2>';

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
				echo '<h2>' . esc_html__('360° tours', 'onoffice-for-wp-websites') . '</h2>';

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
				echo '<h2>' . esc_html__('Objects', 'onoffice-for-wp-websites') . '</h2>';

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
				echo '<h2>' . esc_html__('Links', 'onoffice-for-wp-websites') . '</h2>';

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
