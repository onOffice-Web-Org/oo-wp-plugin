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

namespace onOffice\WPlugin\Field;

use onOffice\WPlugin\Field\DistinctFieldsCheckerEnvironment;
use onOffice\WPlugin\WP\WPScriptStyleTest;
use onOffice\WPlugin\WP\WPScriptStyleBase;


/**
 *
 * test configuration for DistinctFieldsChecker
 *
 */
class DistinctFieldsCheckerEnvironmentTest
	implements DistinctFieldsCheckerEnvironment
{

	/** @var string */
	private $_module = null;

	/** @var array */
	private $_distinctValues = [];

	/** @var array */
	private $_inputValues = [];

	/** @var WPScriptStyleTest */
	private $_pScriptSytle = null;


	/**
	 *
	 * @return array
	 *
	 */

	public function getDistinctValues(): array {
		return $this->_distinctValues;
	}


	/**
	 *
	 * @param array $distinctValues
	 *
	 */

	public function setDistictValues(array $distinctValues)
		{ $this->_distinctValues = $distinctValues; }


	/**
	 *
	 * @return array
	 *
	 */

	public function getInputValues(): array {
		return $this->_inputValues;
	}


	/**
	 *
	 * @param array $inputValues
	 *
	 */

	public function setInputValues(array $inputValues)
		{ $this->_inputValues = $inputValues; }


	/**
	 *
	 * @return string
	 *
	 */

	public function getModule(): string {
		return $this->_module;
	}


	/**
	 *
	 * @param string $module
	 *
	 */

	public function setModule(string $module)
		{ $this->_module = $module; }


	/**
	 *
	 * @param WPScriptStyleBase $pScriptStyle
	 *
	 */

	public function setScriptStyle(WPScriptStyleBase $pScriptStyle)
		{ $this->_pScriptSytle = $pScriptStyle; }


	/**
	 *
	 * @return WPScriptStyleBase
	 *
	 */

	public function getScriptStyle(): WPScriptStyleBase {
		return $this->_pScriptSytle;
	}

}