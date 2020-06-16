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

namespace onOffice\WPlugin\Gui;

use Exception;

/**
 *
 * Format a date/datetime string according to locale
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class DateTimeFormatter
{
	/** if set: short, otherwise long */
	const SHORT = 1;

	/** */
	const ADD_DATE = 2;

	/** */
	const ADD_TIME = 4;

	/** */
	const ADD_TIMEZONE = 8;

	/** @var int bitmask */
	private $_format = 0;

	/** @var int */
	private $_timestamp = 0;


	/** @var array */
	private $_formatCombinations = [
		(self::SHORT|self::ADD_DATE|self::ADD_TIME) => 'Y/m/d g:i:s a',
		self::SHORT|self::ADD_DATE => 'Y/m/d',
		self::SHORT|self::ADD_TIME => 'g:i:s a',
	];

	/**
	 *
	 * @param int $format bitmask
	 * @param int $timestamp GMT
	 * @return string
	 *
	 * @throws Exception
	 */

	public function formatByTimestamp(int $format, int $timestamp): string
	{
		$this->_format = $format;
		$this->_timestamp = $timestamp;

		return $this->format();
	}


	/**
	 *
	 * @return string
	 * @throws Exception
	 *
	 */

	private function format(): string
	{
		$format = $this->_formatCombinations[$this->_format] ?? null;

		if ($format === null) {
			throw new Exception('Not Implemented');
		}

		return date_i18n(__($format, 'onoffice'), $this->_timestamp, true);
	}
}
