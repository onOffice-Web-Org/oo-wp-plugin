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

use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\SDKWrapper;

class CostsCalculator
{
	/** @var SDKWrapper */
	private $_pSDKWrapper;

	public function __construct(SDKWrapper $_pSDKWrapper)
	{
		$this->_pSDKWrapper = $_pSDKWrapper;
	}

	
	/**
	 * @param array $recordRaw
	 * @param array $propertyTransferTax
	 * @param float $externalCommission
	 * @return array
	 */
	public function getTotalCosts(array $recordRaw, array $propertyTransferTax, float $externalCommission): array
	{
		$totalCostsData = $this->calculateRawCosts($recordRaw, $propertyTransferTax, $externalCommission);
		$currencySymbol = $this->getCurrencySymbol();

		if (empty($currencySymbol)) {
			return [];
		}

		if (!isset($recordRaw['waehrung'])) {
			return [];
		}

		if (!isset($currencySymbol[$recordRaw['waehrung']])) {
			return [];
		}

		$currency = $currencySymbol[$recordRaw['waehrung']];

		return $this->formatPrice($totalCostsData, $currency);
	}

	/**
	 * @param array $recordRaw
	 * @param array $propertyTransferTax
	 * @param float $externalCommission
	 * @return array
	 */
	private function calculateRawCosts(array $recordRaw, array $propertyTransferTax, float $externalCommission): array
	{
		$purchasePriceRaw = $recordRaw['kaufpreis'];

		$othersCosts = [
			'bundesland' => $propertyTransferTax[$recordRaw['bundesland']],
			'aussen_courtage' => $externalCommission,
			'notary_fees' => DataDetailView::NOTARY_FEES,
			'land_register_entry' => DataDetailView::LAND_REGISTER_ENTRY
		];

		$rawAllCosts = ['kaufpreis' => ['raw' => $purchasePriceRaw]];
		$totals = 0;

		foreach ($othersCosts as $key => $value) {
			$calculatePrice = $this->calculatePrice($purchasePriceRaw, $value);
			$rawAllCosts[$key] = ['raw' => $calculatePrice];
			$totals += $calculatePrice;
		}

		$rawAllCosts['total_costs'] = ['raw' => $purchasePriceRaw + $totals];

		return $rawAllCosts;
	}

	/**
	 * @param float $price
	 * @param float $rate
	 * @return float
	 */
	private function calculatePrice(float $price, float $rate): float
	{
		return round($price * $rate / 100);
	}

	/**
	 * @param array $totalCostsData
	 * @param string $currency
	 * @return array
	 */
	private function formatPrice(array $totalCostsData, string $currency): array
	{
		foreach ($totalCostsData as $key => $value) {
			$totalCostsData[$key]['default'] = $this->formatCurrency($value['raw'], $currency);
		}

		return $totalCostsData;
	}

	/**
	 * @param float $amount
	 * @param string $currency
	 * @return string
	 */
	private function formatCurrency(float $amount, string $currency): string
	{
		$decimalPlaces = floor($amount) == $amount ? 0 : 2;

		return number_format($amount, $decimalPlaces, ',', '.') . ' ' . $currency;
	}

	/**
	 * @return array
	 */

	private function getCurrencySymbol(): array
	{
		$parameters = [
			'labels' => true,
			'fieldList' => ['waehrung'],
			'language' => 'DEU',
			'modules' => [onOfficeSDK::MODULE_ESTATE],
		];

		$pAPIClientAction = new APIClientActionGeneric
			($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'fields');
		$pAPIClientAction->setParameters($parameters);
		$pAPIClientAction->addRequestToQueue();
		$this->_pSDKWrapper->sendRequests();
		$result = $pAPIClientAction->getResultRecords();

		if (
			!isset($result[0]) ||
			!isset($result[0]['elements']) ||
			!isset($result[0]['elements']['waehrung']) ||
			!isset($result[0]['elements']['waehrung']['permittedvalues'])
		) {
			return [];
		}
			return $result[0]['elements']['waehrung']['permittedvalues'] ?? [];
	}
}