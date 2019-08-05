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

namespace onOffice\WPlugin\Field;

/**
 *
 */

class DistinctFieldsHandlerModel
{
	/** @var string */
	private $_module = null;

	/** @var array */
	private $_distinctFields = [];

	/** @var array */
	private $_inputValues = [];

	/** @var array */
	private $_geoPositionFields = [];


	/**
	 *
	 * @param string $module
	 *
	 */

	public function setModule(string $module)
		{ $this->_module = $module; }


	/**
	 *
	 * @return string
	 *
	 */
	public function getModule(): string
		{ return $this->_module; }


	/**
	 *
	 * @param array $distinctFields
	 *
	 */

	public function setDistinctFields(array $distinctFields)
		{ $this->_distinctFields = $distinctFields; }


	/**
	 *
	 * @return array
	 *
	 */

	public function getDistinctFields(): array
		{return $this->_distinctFields ;}


	/**
	 *
	 * @param array $inputValues
	 *
	 */

	public function setInputValues(array $inputValues)
		{ $this->_inputValues = $inputValues ;}


	/**
	 *
	 * @return array
	 *
	 */

	public function getInputValues(): array
		{ return $this->_inputValues; }


	/**
	 *
	 * @param array $geoPositionFields
	 *
	 */

	public function setGeoPositionFields(array $geoPositionFields)
		{ $this->_geoPositionFields = $geoPositionFields; }


	/**
	 *
	 * @return array
	 *
	 */

	public function getGeoPositionFields(): array
		{ return $this->_geoPositionFields; }

}