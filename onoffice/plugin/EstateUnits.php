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

namespace onOffice\WPlugin;

use onOffice\SDK\Exception\HttpFetchNoResultException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Controller\EstateListBase;
use onOffice\WPlugin\Controller\EstateMiniatureSubList;
use onOffice\WPlugin\Controller\EstateUnitsConfigurationBase;
use onOffice\WPlugin\Controller\EstateUnitsConfigurationDefault;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Filter\DefaultFilterBuilderPresetEstateIds;

/**
 *
 */

class EstateUnits
	implements EstateMiniatureSubList
{
	/** @var array */
	private $_estateUnits = [];

	/** @var EstateUnitsConfigurationBase */
	private $_pEstateUnitsConfiguration = null;


	/**
	 *
	 * @param DataListView $pDataListView
	 * @param EstateUnitsConfigurationBase $pEstateUnitsConfiguration
	 *
	 */

	public function __construct(DataListView $pDataListView,
		EstateUnitsConfigurationBase $pEstateUnitsConfiguration = null)
	{
		$this->_pEstateUnitsConfiguration = $pEstateUnitsConfiguration ??
			new EstateUnitsConfigurationDefault($pDataListView);
		assert($pDataListView === $this->_pEstateUnitsConfiguration->getEstateList()->getDataView());
	}


	/**
	 *
	 * @param EstateListBase $pEstateList
	 * @throws HttpFetchNoResultException
	 *
	 */

	public function loadByMainEstates(EstateListBase $pEstateList)
	{
		$pSDKWrapper = $this->_pEstateUnitsConfiguration->getSDKWrapper();

		$pAPIClientAction = new APIClientActionGeneric
			($pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'idsfromrelation');
		$pAPIClientAction->setParameters([
			'relationtype' => onOfficeSDK::RELATION_TYPE_COMPLEX_ESTATE_UNITS,
			'parentids' => $pEstateList->getEstateIds(),
		]);
		$pAPIClientAction->addRequestToQueue()->sendRequests();

		if (!$pAPIClientAction->getResultStatus()) {
			throw new HttpFetchNoResultException();
		}
		$this->evaluateEstateUnits($pAPIClientAction->getResultRecords());
	}


	/**
	 *
	 * @param array $records
	 *
	 */

	private function evaluateEstateUnits(array $records)
	{
		foreach ($records as $properties) {
			$this->_estateUnits = $properties['elements'];
		}
	}


	/**
	 *
	 * @param int $estateId
	 * @return int[]
	 *
	 */

	public function getSubEstateIds(int $estateId): array
	{
		return $this->_estateUnits[$estateId] ?? [];
	}


	/**
	 *
	 * @param int $estateId
	 * @return int
	 *
	 */

	public function getSubEstateCount(int $estateId): int
	{
		$units = $this->getSubEstateIds($estateId);
		return count($units);
	}


	/**
	 *
	 * @param int $mainEstateId
	 * @return string
	 *
	 */

	public function generateHtmlOutput(int $mainEstateId): string
	{
		$units = $this->getSubEstateIds($mainEstateId);
		$pDataView = $this->_pEstateUnitsConfiguration->getEstateList()->getDataView();
		$random = $pDataView->getRandom();

		if ($random) {
			// shuffle() twice: once here and once in EstateList
			shuffle($units);
		}

		$pEstateList = $this->_pEstateUnitsConfiguration->getEstateList();
		$pEstateList->setShuffleResult($random);
		$pDefaultFilterBuilder = new DefaultFilterBuilderPresetEstateIds($units);
		$pEstateList->setDefaultFilterBuilder($pDefaultFilterBuilder);
		$pEstateList->loadEstates(1);

		$templateName = $pDataView->getTemplate();
		$pTemplate = $this->_pEstateUnitsConfiguration->getTemplate($templateName);
		$pTemplate->setEstateList($pEstateList);
		$htmlOutput = $pTemplate->render();

		return $htmlOutput;
	}
}
