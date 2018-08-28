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

namespace onOffice\WPlugin\Renderer;

/**
 *
 */

abstract class InputFieldComplexSortableDetailListContentBase
{
	/** @var int */
	private static $_id = 0;

	/** @var InputModelBase[] */
	private $_extraInputModels = array();


	/**
	 *
	 */

	public function __construct()
	{
		self::$_id++;
	}


	/**
	 *
	 * @param string $key
	 * @param bool $dummy
	 *
	 */

	abstract public function render($key, $dummy);


	/** @return InputModelBase[] */
	public function getExtraInputModels()
		{ return $this->_extraInputModels; }

	/** @param InputModelBase $pInputModel */
	public function addExtraInputModel(InputModelBase $pInputModel)
		{ $this->_extraInputModels []= $pInputModel; }

	/** @var InputModelBase[] $extraInputModels */
	public function setExtraInputModels(array $extraInputModels)
		{ $this->_extraInputModels = $extraInputModels; }
}
