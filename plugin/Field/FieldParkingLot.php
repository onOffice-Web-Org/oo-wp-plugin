<?php

/**
 *
 *    Copyright (C) 2023 onOffice GmbH
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace onOffice\WPlugin\Field;

use Exception;
use NumberFormatter;
use onOffice\WPlugin\Language;

class FieldParkingLot{
    
	
	/**
	 * @param array $currentEstate
	 * @param string $codeCurrency
	 * @return array
	 */
	public function renderParkingLot(array $currentEstate, string $codeCurrency): array
	{
		$language = new Language();
		$locale = $language->getLocale();
		$locale = !empty($locale) ? $locale : 'de_DE';
		$codeCurrency = !empty($codeCurrency) ? $codeCurrency : 'EUR';

		$parkingArray = $currentEstate['multiParkingLot'];
		$messages = [];
		foreach ( $parkingArray as $key => $parking ) {
			$parkingName   = '';
			$marketingType = '';
			$isCount         = $parking['Count'];
			if ( $isCount == 0 ) {
				continue;
			}
			$parking['Price'] = $parking['Price'] ?? 0;
			$parkingName = $this->getParkingName( $key, $parking['Count'] );
			if ( !empty( $parking['MarketingType'] ) && intval($parking['Price']) > 0 ) {
				$marketingType = $this->getMarketingType( $parking['MarketingType'] );
			}
			$price = $this->formatPriceParking( $parking['Price'], $locale, $codeCurrency );

			$element = $this->formatElement($parkingName, $price, $marketingType, $parking['Price'], $parking['Count']);
		
			array_push( $messages, $element );
		}

		return $messages;
	}
	
	/**
	 * @param string $parkingName
	 * @param string $price
	 * @param string $marketingType
	 * @param string $priceValue
	 * @param int $count
	 * @return string
	 */
	private function formatElement(string $parkingName, string $price, string $marketingType, string $priceValue, int $count): string
	{
		if (intval($priceValue) > 0) {
			/* translators: 1: Count and name of parking lot, 2: Price, 3: Marketing type */
			return sprintf(_n('%1$s, %2$s%3$s', '%1$s, %2$s %3$s', $count, 'onoffice-for-wp-websites'), $parkingName, $price, $marketingType);
		}
		
		return $parkingName;
	}

	/**
	 * @param string $str
	 * @param string $locale
	 * @param string $codeCurrency
	 * @return string
	 */
	public function formatPriceParking(string $str, string $locale, string $codeCurrency): string
	{
		$format = new NumberFormatter( $locale, NumberFormatter::CURRENCY );
		if ( intval( $str ) == $str ) {
			$format->setAttribute( NumberFormatter::MIN_SIGNIFICANT_DIGITS, 0 );
		}
		return str_replace( "\xc2\xa0", " ", $format->formatCurrency( $str, $codeCurrency ) );
	}

	/**
	 * @param string $marketingType
	 * @throws \Exception
	 * @return string
	 */
	public function getMarketingType( string $marketingType ): string
	{
		switch ( $marketingType ) {
			case 'buy':
				/* translators: %s is the marketing type of buy */
				$str = __( ' (purchase)', 'onoffice-for-wp-websites' );
				break;
			case 'rent':
				/* translators: %s is the marketing type of rent */
				$str = __( ' (rent)', 'onoffice-for-wp-websites' );
				break;
			default:
				throw new Exception('unrecognized marketing type');
		}
		return esc_html( $str );
	}

	/**
	 * @param string $parkingName
	 * @param int $count
	 * @return string
	 */
	public function getParkingName(string $parkingName, int $count): string
	{
		switch ($parkingName) {
			case 'carport':
				/* translators: %s is the amount of carports */
				$str = _n('%s carport', '%s carports', $count, 'onoffice-for-wp-websites');
				break;
			case 'duplex':
				/* translators: %s is the amount of duplexes */
				$str = _n('%s duplex', '%s duplexes', $count, 'onoffice-for-wp-websites');
				break;
			case 'parkingSpace':
				/* translators: %s is the amount of parking spaces */
				$str = _n('%s parking space', '%s parking spaces', $count, 'onoffice-for-wp-websites');
				break;
			case 'garage':
				/* translators: %s is the amount of garages */
				$str = _n('%s garage', '%s garages', $count, 'onoffice-for-wp-websites');
				break;
			case 'multiStoryGarage':
				/* translators: %s is the amount of multi story garages */
				$str = _n('%s multi-story garage', '%s multi-story garages', $count, 'onoffice-for-wp-websites');
				break;
			case 'undergroundGarage':
				/* translators: %s is the amount of underground garages */
				$str = _n('%s underground garage', '%s underground garages', $count, 'onoffice-for-wp-websites');
				break;
			case 'otherParkingLot':
				/* translators: %s is the amount of other parking lots */
				$str = _n('%s other parking lot', '%s other parking lots', $count, 'onoffice-for-wp-websites');
				break;
			default:
				$str = $parkingName;
		}
		return esc_html(sprintf($str, $count));
	}

}
