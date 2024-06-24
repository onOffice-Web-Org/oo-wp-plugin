<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataView;

class CostCalculator
{
	/** @var DataView */
	private $_pDataView;

	/** @var string */
	private $_currencyCode;

	/** @var float */
	private $_externalCommission;

	/**
	 * @param DataView $dataView
	 * @param string $currencyCode
	 * @param float $externalCommission
	 */
	public function __construct(DataView $dataView, string $currencyCode, float $externalCommission)
	{
		$this->_pDataView = $dataView;
		$this->_currencyCode = $currencyCode;
		$this->_externalCommission = $externalCommission;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function getTotalCosts(array $data): array
	{
		$language = new Language();
		$locale = !empty($language->getLocale()) ? $language->getLocale() : 'de_DE';

		$format = new NumberFormatter($locale, NumberFormatter::CURRENCY);
		$costDetails = $this->calculateRawCosts($data);

		return $this->formatCosts($format, $costDetails);
	}

	/**
	 * @param array $data
	 * @return array
	 */
	private function calculateRawCosts(array $data): array
	{
		$purchasePrice = $data['kaufpreis'];
		$propertyTransferTax = $this->_pDataView->getPropertyTransferTax()[$data['bundesland']];
		$costsRate = [
			'bundesland' => $propertyTransferTax,
			'aussen_courtage' => $this->_externalCommission,
			'notary_fees' => DataDetailView::NOTARY_FEES,
			'land_register_entry' => DataDetailView::LAND_REGISTER_ENTRY
		];

		$costDetails = ['kaufpreis' => ['raw' => $purchasePrice]];
		$totalCosts = 0;

		foreach ($costsRate as $key => $value) {
			$cost = $this->calculateCostByRate($purchasePrice, $value);
			$costDetails[$key] = ['raw' => $cost];
			$totalCosts += $cost;
		}

		$costDetails['total_costs'] = ['raw' => $purchasePrice + $totalCosts];

		return $costDetails;
	}

	/**
	 * @param float $price
	 * @param float $rate
	 * @return float
	 */
	private function calculateCostByRate(float $price, float $rate): float
	{
		return round($price * $rate / 100);
	}

	/**
	 * @param NumberFormatter $format
	 * @param array $costDetails
	 * @return array
	 */
	private function formatCosts(NumberFormatter $format, array $costDetails): array
	{
		foreach ($costDetails as $key => $value) {
			if (isset($value['raw'])) {
				$costDetails[$key]['default'] = $this->formatCurrency($format, $value['raw']);
			}
		}

		return $costDetails;
	}

	/**
	 * @param NumberFormatter $format
	 * @param float $amount
	 * @return string
	 */
	private function formatCurrency(NumberFormatter $format, float $amount): string
	{
		if (intval($amount) == $amount) {
			$format->setAttribute(NumberFormatter::MIN_SIGNIFICANT_DIGITS, 0);
		}

		return str_replace("\xc2\xa0", " ", $format->formatCurrency($amount, $this->_currencyCode));
	}
}