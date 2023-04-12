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

declare (strict_types=1);

namespace onOffice\WPlugin\Field\DefaultValue;

/**
 *
 */
class DefaultValueModelDate
	extends DefaultValueModelBase
{
	/** @var string */
	private $_valueFrom = '';

	/** @var string */
	private $_valueTo = '';

	/**
	 * @return string
	 */
	public function getValueFrom(): string
	{
		return $this->_valueFrom;
	}

	/**
	 * @param string $valueFrom
	 */
	public function setValueFrom(string $valueFrom)
	{
		$this->_valueFrom = $valueFrom;
	}

	/**
	 * @return string
	 */
	public function getValueTo(): string
	{
		return $this->_valueTo;
	}

	/**
	 * @param string $valueTo
	 */
	public function setValueTo(string $valueTo)
	{
		$this->_valueTo = $valueTo;
	}
}