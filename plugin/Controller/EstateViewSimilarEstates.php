<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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
 *
 */

namespace onOffice\WPlugin\Controller;

use DI\DependencyException;
use DI\NotFoundException;
use Generator;
use onOffice\WPlugin\DataView\DataView;
use onOffice\WPlugin\DataView\DataViewSimilarEstates;
use onOffice\WPlugin\Filter\DefaultFilterBuilderSimilarEstates;
use onOffice\WPlugin\Filter\FilterConfigurationSimilarEstates;
use onOffice\WPlugin\Filter\GeoSearchBuilderSimilarEstates;
use onOffice\WPlugin\Types\GeoCoordinates;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypes;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class EstateViewSimilarEstates
	implements EstateMiniatureSubList
{
	/** @var FilterConfigurationSimilarEstates */
	private $_pFilterConfiguration = null;


	/** @var EstateViewSimilarEstatesEnvironment */
	private $_pEnvironment = null;

	/** @var EstateListBase[] */
	private $_estateListsByMainId = [];


	/**
	 *
	 * @param DataViewSimilarEstates $pDataDetailView
	 * @param EstateViewSimilarEstatesEnvironment $pEnvironment
	 *
	 */

	public function __construct(DataViewSimilarEstates $pDataDetailView,
		EstateViewSimilarEstatesEnvironment $pEnvironment = null)
	{
		$this->_pFilterConfiguration = new FilterConfigurationSimilarEstates($pDataDetailView);
		$this->_pEnvironment = $pEnvironment ?? new EstateViewSimilarEstatesEnvironmentDefault
			($pDataDetailView);
	}


	/**
	 *
	 * @return DataView
	 *
	 */

	public function getDataView(): DataView
	{
		return $this->_pFilterConfiguration->getDataViewSimilarEstates();
	}


	/**
	 *
	 * @param EstateListBase $pEstateList
	 * @return Generator
	 *
	 */

	private function buildEstateParameters(EstateListBase $pEstateList): Generator
	{
		while ($pValuesCurrentEstate = $pEstateList->estateIterator
			(EstateViewFieldModifierTypes::MODIFIER_TYPE_DETAIL_SIMILAR_ESTATES)) {
			$this->_pFilterConfiguration->setEstateKind($pValuesCurrentEstate->getValueRaw('objektart') ?? '');
			$this->_pFilterConfiguration->setMarketingMethod($pValuesCurrentEstate->getValueRaw('vermarktungsart') ?? '');
			$this->_pFilterConfiguration->setStreet($pValuesCurrentEstate->getValueRaw('strasse') ?? '');
			$this->_pFilterConfiguration->setCountry($pValuesCurrentEstate->getValueRaw('land') ?? '');
			$this->_pFilterConfiguration->setPostalCode($pValuesCurrentEstate->getValueRaw('plz') ?? '');
			$this->_pFilterConfiguration->setShowArchive($pValuesCurrentEstate->getValueRaw('status2') ?? '');
			$this->_pFilterConfiguration->setShowReference($pValuesCurrentEstate->getValueRaw('referenz') ?? '');
			$longitude = $pValuesCurrentEstate->getValueRaw('laengengrad');
			$latitude = $pValuesCurrentEstate->getValueRaw('breitengrad');
			$estateId = $pEstateList->getCurrentEstateId();

			if ($longitude != .0 && $latitude != .0) {
				$pGeoCoordinates = new GeoCoordinates($latitude, $longitude);
				$this->_pFilterConfiguration->setGeoCoordinates($pGeoCoordinates);
			}

			$pDefaultFilterBuilder = new DefaultFilterBuilderSimilarEstates($this->_pFilterConfiguration);
			$pDefaultFilterBuilder->setExcludeIds([$estateId]);
			$pGeoRangeSearch = new GeoSearchBuilderSimilarEstates($this->_pFilterConfiguration);

			$pEstateListSub = clone $this->_pEnvironment->getEstateList();
			$pEstateListSub->setDefaultFilterBuilder($pDefaultFilterBuilder);
			$pEstateListSub->setGeoSearchBuilder($pGeoRangeSearch);

			yield $estateId => $pEstateListSub;
		}
	}

	/**
	 *
	 * @param int $mainEstateId
	 * @return string
	 * @throws DependencyException
	 * @throws NotFoundException
	 */

	public function generateHtmlOutput(int $mainEstateId): string
	{
		$templateName = $this->_pFilterConfiguration->getDataViewSimilarEstates()->getTemplate();

		if (!isset($this->_estateListsByMainId[$mainEstateId]) || $templateName === '') {
			return '';
		}

		$pEstateList = $this->_estateListsByMainId[$mainEstateId];
		return $this->_pEnvironment
			->getTemplate()
			->withTemplateName($templateName)
			->withEstateList($pEstateList)
			->render();
	}


	/**
	 *
	 * @param int $estateId
	 * @return int
	 *
	 */

	public function getSubEstateCount(int $estateId): int
	{
		return count($this->getSubEstateIds($estateId));
	}


	/**
	 *
	 * @param int $estateId
	 * @return array
	 *
	 */

	public function getSubEstateIds(int $estateId): array
	{
		$estateIds = [];

		if (isset($this->_estateListsByMainId[$estateId])) {
			$pEstateList = $this->_estateListsByMainId[$estateId];
			$estateIds = $pEstateList->getEstateIds();
		}

		return $estateIds;
	}


	/**
	 *
	 * @param EstateListBase $pEstateList
	 *
	 */

	public function loadByMainEstates(EstateListBase $pEstateList)
	{
		$pGenerator = $this->buildEstateParameters($pEstateList);

		foreach ($pGenerator as $id => $pEstateListSub) {
			$pEstateListSub->setFormatOutput(true);
			$pEstateListSub->loadEstates();
			$this->_estateListsByMainId[$id] = $pEstateListSub;
		}
	}
}
