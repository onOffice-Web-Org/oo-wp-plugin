<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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

declare(strict_types=1);

namespace onOffice\WPlugin\Filter;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\InputVariableReader;
use onOffice\WPlugin\Region\RegionController;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class DefaultFilterBuilderListViewEnvironmentDefault
	implements DefaultFilterBuilderListViewEnvironment
{
	/** @var FilterBuilderInputVariables */
	private $_pFilterBuilderInputVariables = null;

	/** @var InputVariableReader */
	private $_pInputVariableReader = null;

	/** @var RegionController */
	private $_pRegionController = null;


	/**
	 *
	 */

	public function __construct()
	{
		$module = onOfficeSDK::MODULE_ESTATE;
		$this->_pFilterBuilderInputVariables = new FilterBuilderInputVariables($module, true);
		$this->_pInputVariableReader = new InputVariableReader($module);
		$this->_pRegionController = new RegionController(false);
	}


	/**
	 *
	 * @return FilterBuilderInputVariables
	 *
	 */

	public function getFilterBuilderInputVariables(): FilterBuilderInputVariables
	{
		return $this->_pFilterBuilderInputVariables;
	}


	/**
	 *
	 * @return InputVariableReader
	 *
	 */

	public function getInputVariableReader(): InputVariableReader
	{
		return $this->_pInputVariableReader;
	}


	/**
	 *
	 * @return RegionController
	 *
	 */

	public function getRegionController(): RegionController
	{
		return $this->_pRegionController;
	}
}
