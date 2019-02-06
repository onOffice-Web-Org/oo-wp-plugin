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

use onOffice\WPlugin\WP\WPScriptStyleBase;
use onOffice\WPlugin\WP\WPScriptStyleDefault;
use function json_decode;

/**
 *
 * dependency class for DistinctFieldsChecker
 *
 */

class DistinctFieldsCheckerEnvironmentDefault
	implements DistinctFieldsCheckerEnvironment
{

	/** @var WPScriptStyleBase */
	private $_pScriptStyle = null;


	/**
	 *
	 */

	public function __construct()
	{
		$this->_pScriptStyle = new WPScriptStyleDefault();
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getDistinctValues(): array
	{
		return filter_input
			(INPUT_POST, DistinctFieldsHandler::PARAMETER_DISTINCT_VALUES, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getInputValues(): array
	{
		return json_decode(filter_input
			(INPUT_POST, DistinctFieldsHandler::PARAMETER_INPUT_VALUES), true) ?? [];
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getModule(): string
	{
		return filter_input(INPUT_POST, DistinctFieldsHandler::PARAMETER_MODULE) ?? '';
	}


	/**
	 *
	 * @return WPScriptStyleBase
	 *
	 */

	public function getScriptStyle(): WPScriptStyleBase
		{ return $this->_pScriptStyle; }
}