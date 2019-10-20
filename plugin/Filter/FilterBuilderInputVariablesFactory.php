<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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
use onOffice\WPlugin\Controller\InputVariableReaderConfig;


/**
 *
 */

class FilterBuilderInputVariablesFactory
{
	/** @var InputVariableReaderConfig */
	private $_pInputVariableReaderConfig = null;


	/**
	 *
	 * @param InputVariableReaderConfig $pInputVariableReaderConfig
	 *
	 */

	public function __construct(InputVariableReaderConfig $pInputVariableReaderConfig)
	{
		$this->_pInputVariableReaderConfig = $pInputVariableReaderConfig;
	}


	/**
	 *
	 * @return FilterBuilderInputVariables
	 *
	 */

	public function createForAddress()
	{
		return new FilterBuilderInputVariables(onOfficeSDK::MODULE_ADDRESS, false, $this->_pInputVariableReaderConfig);
	}
}
