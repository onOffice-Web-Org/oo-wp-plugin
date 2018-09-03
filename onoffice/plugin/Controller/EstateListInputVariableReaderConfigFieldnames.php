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

use onOffice\WPlugin\Fieldnames;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class EstateListInputVariableReaderConfigFieldnames
	implements EstateListInputVariableReaderConfig
{
	/** @var Fieldnames */
	private $_pFieldnames = null;


	/**
	 *
	 */

	public function __construct()
	{
		$this->_pFieldnames = new Fieldnames();
		$this->_pFieldnames->loadEstateSearchGeoPositionFields();
		$this->_pFieldnames->loadLanguage();
	}


	/**
	 *
	 * @param string $field
	 * @param string $module
	 * @return string
	 *
	 */

	public function getFieldType(string $field, string $module): string
	{
		$fieldInformation = $this->_pFieldnames->getFieldInformation($field, $module);
		return $fieldInformation['type'];
	}


	/**
	 *
	 * @param string $name
	 * @param int $filters
	 * @param int $options
	 * @return mixed
	 *
	 */

	public function getValue(string $name, int $filters, int $options)
	{
		$getValue = filter_input(INPUT_GET, $name, $filters, $options);
		$postValue = filter_input(INPUT_POST, $name, $filters, $options);
		$value = $getValue ? $getValue : $postValue;
		if (is_array($value) && count($value) === 1 && key($value) === 0 &&
			!is_array($_REQUEST[$name])) {
			$value = $value[0];
		}

		return $value;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getTimezoneString(): string
	{
		return get_option('timezone_string');
	}
}
