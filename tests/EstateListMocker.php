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

namespace onOffice\tests;

use onOffice\WPlugin\ArrayContainer;
use onOffice\WPlugin\Controller\EstateListBase;
use onOffice\WPlugin\DataView\DataView;
use onOffice\WPlugin\Filter\DefaultFilterBuilder;
use onOffice\WPlugin\Filter\GeoSearchBuilder;
use onOffice\WPlugin\Filter\GeoSearchBuilderEmpty;
use onOffice\WPlugin\Filter\GeoSearchBuilderSimilarEstates;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class EstateListMocker
	implements EstateListBase
{
	/** @var bool */
	private $_shuffleResult = false;

	/** @var DefaultFilterBuilder */
	private $_pDefaultFilterBuilder = null;

	/** @var DataView */
	private $_pDataView = null;

	/** @var array */
	private $_methodCalls = [];

	/** @var array */
	private $_estateData = [];

	/** @var int */
	private $_currentEstateId = 0;

	/** @var GeoSearchBuilder */
	private $_pGeoSearchBuilder = null;
	/** @var bool */
	private $_formatOutput = true;


	/**
	 *
	 * @param DataView $pDataView
	 *
	 */

	public function __construct(DataView $pDataView)
	{
		$this->registerMethodCall(__METHOD__);
		$this->_pDataView = $pDataView;
		$this->_pGeoSearchBuilder = new GeoSearchBuilderEmpty();
		$this->resetEstateIterator();
	}


	/**
	 *
	 * @return ArrayContainer|bool
	 *
	 */

	public function estateIterator()
	{
		$this->registerMethodCall(__METHOD__);

		if (!$this->wasMethodCalled('loadEstates')) {
			return false;
		}

		$estateValues = current($this->_estateData);
		$this->_currentEstateId = key($this->_estateData);
		next($this->_estateData);

		$pContainer = false;
		if ($estateValues !== false) {
			$pContainer = new ArrayContainer($estateValues);
		}

		return $pContainer;
	}


	/**
	 *
	 * @return DataView
	 *
	 */

	public function getDataView(): DataView
	{
		$this->registerMethodCall(__METHOD__);
		return $this->_pDataView;
	}


	/**
	 *
	 */

	public function loadEstates()
	{
		$this->registerMethodCall(__METHOD__);
	}


	/**
	 *
	 */

	public function resetEstateIterator()
	{
		$this->registerMethodCall(__METHOD__);
		reset($this->_estateData);
	}


	/**
	 *
	 * @return DefaultFilterBuilder
	 *
	 */

	public function getDefaultFilterBuilder(): DefaultFilterBuilder
	{
		$this->registerMethodCall(__METHOD__);
		return $this->_pDefaultFilterBuilder;
	}


	/**
	 *
	 * @param DefaultFilterBuilder $pDefaultFilterBuilder
	 *
	 */

	public function setDefaultFilterBuilder(DefaultFilterBuilder $pDefaultFilterBuilder)
	{
		$this->registerMethodCall(__METHOD__);
		$this->_pDefaultFilterBuilder = $pDefaultFilterBuilder;
	}


	/**
	 *
	 * @param bool $shuffleResult
	 *
	 */

	public function setShuffleResult(bool $shuffleResult)
	{
		$this->registerMethodCall(__METHOD__);
		$this->_shuffleResult = $shuffleResult;
	}


	/**
	 *
	 * @return bool
	 *
	 */

	public function getShuffleResult(): bool
	{
		$this->registerMethodCall(__METHOD__);
		return $this->_shuffleResult;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getEstateIds(): array
	{
		$this->registerMethodCall(__METHOD__);
		if (!$this->wasMethodCalled('loadEstates')) {
			return [];
		}
		return array_keys($this->_estateData);
	}


	/**
	 *
	 * @return int
	 *
	 */

	public function getCurrentEstateId(): int
	{
		$this->registerMethodCall(__METHOD__);
		return $this->_currentEstateId;
	}

	/**
	 * @return int
	 */
	public function getCurrentMultiLangEstateMainId(): int
	{
		$this->registerMethodCall(__METHOD__);
		return $this->_currentEstateId;
	}


	/**
	 *
	 * @return GeoSearchBuilder
	 *
	 */

	public function getGeoSearchBuilder(): GeoSearchBuilder
	{
		$this->registerMethodCall(__METHOD__);
		return $this->_pGeoSearchBuilder;
	}


	/**
	 *
	 * @param GeoSearchBuilder $pGeoSearchBuilder
	 *
	 */

	public function setGeoSearchBuilder(GeoSearchBuilder $pGeoSearchBuilder)
	{
		$this->registerMethodCall(__METHOD__);
		$this->_pGeoSearchBuilder = $pGeoSearchBuilder;
	}

	/**
	 *
	 * @param string $methodName
	 * @return bool
	 *
	 */

	public function assertAmountOfMethodCalls(string $methodName, int $amount): bool
	{
		$counts = array_count_values($this->_methodCalls);
		$calls = $counts[$methodName] ?? 0;
		return assert($calls === $amount);
	}


	/**
	 *
	 * @param string $methodName
	 * @return bool
	 *
	 */

	public function wasMethodCalled(string $methodName): bool
	{
		return in_array(static::class.'::'.$methodName, $this->_methodCalls);
	}


	/**
	 *
	 * @param string $methodName
	 *
	 */

	private function registerMethodCall(string $methodName)
	{
		$this->_methodCalls []= $methodName;
	}


	/**
	 *
	 * @param array $estateData An array of arrays of estate records
	 *
	 */

	public function setEstateData(array $estateData)
	{
		$this->_estateData = $estateData;
	}


	/** @return bool */
	public function getFormatOutput(): bool
		{ return $this->_formatOutput; }

	/** @param bool $formatOutput */
	public function setFormatOutput(bool $formatOutput)
		{ $this->_formatOutput = $formatOutput; }
}