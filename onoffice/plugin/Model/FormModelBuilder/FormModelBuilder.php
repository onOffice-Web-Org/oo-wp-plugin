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

namespace onOffice\WPlugin\Model\FormModelBuilder;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class FormModelBuilder
{
	/** @var string */
	private $_pageSlug = null;

	/** @var array */
	private $_values = array();


	/**
	 *
	 * @param string $pageSlug
	 *
	 */

	public function __construct($pageSlug)
	{
		$this->_pageSlug = $pageSlug;
	}


	/**
	 *
	 * @param string $key
	 * @return mixed
	 *
	 */

	protected function getValue($key)
	{
		if (array_key_exists($key, $this->_values))
		{
			return $this->_values[$key];
		}

		return null;
	}


	/** @return string */
	public function getPageSlug()
		{ return $this->_pageSlug; }


	/** @param array $values */
	protected function setValues(array $values) {
		$this->_values = $values;
	}
}
