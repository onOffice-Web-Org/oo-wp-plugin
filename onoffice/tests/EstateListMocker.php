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


	/**
	 *
	 * @param DataView $pDataView
	 *
	 */

	public function __construct(DataView $pDataView)
	{
		$this->registerMethodCall(__METHOD__);
		$this->_pDataView = $pDataView;
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
		$estateValues = current($this->_estateData);
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
	 * @param int $amount
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
	 * @param string $methodName
	 * @return int
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
		return in_array($methodName, $this->_methodCalls);
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
}