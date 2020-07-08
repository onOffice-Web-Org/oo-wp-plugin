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

namespace onOffice\WPlugin\Utility;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class __String
{
	/** @var string */
	private $_string = null;


	/**
	 *
	 * @param string $string
	 *
	 */

	final public function __construct(string $string)
	{
		$this->_string = $string;
	}


	/**
	 *
	 * @param string|null $string
	 * @return static
	 *
	 */

	static public function getNew($string): self
	{
		return new static($string ?? '');
	}


	/**
	 *
	 * @param string $search
	 * @param string $replace
	 * @return string
	 *
	 */

	public function replace(string $search, string $replace): string
	{
		return str_replace($search, $replace, $this->_string);
	}


	/**
	 *
	 * @param array $chars
	 * @return string
	 *
	 */

	public function remove(array $chars): string
	{
		$charArrayInput = $this->split();
		$diff = array_diff($charArrayInput, $chars);
		return implode('', $diff);
	}


	/**
	 *
	 * @param string $chars
	 * @return string
	 *
	 */

	public function keep(string $chars): string
	{
		$charArrayInput = $this->split();
		$charArrayWhitelist = __String::getNew($chars)->split();
		$intersect = array_intersect($charArrayInput, $charArrayWhitelist);
		return implode('', $intersect);
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function split(): array
	{
		return str_split($this->_string);
	}


	/**
	 *
	 * @return int
	 *
	 */

	public function length(): int
	{
		return mb_strlen($this->_string);
	}


	/**
	 *
	 * @return bool
	 *
	 */

	public function isEmpty(): bool
	{
		return $this->_string === '';
	}


	/**
	 *
	 * @param string $subStr
	 * @return bool
	 *
	 */

	public function endsWith(string $subStr): bool
	{
		$pStringSub = __String::getNew($subStr);
		return mb_strrpos($this->_string, $subStr) === $this->length() - $pStringSub->length();
	}


	/**
	 *
	 * @param string $subStr
	 * @return bool
	 *
	 */

	public function startsWith(string $subStr): bool
	{
		return mb_strrpos($this->_string, $subStr) === 0;
	}


	/**
	 *
	 * @param int $start
	 * @param int $length
	 * @return string
	 *
	 */

	public function sub(int $start, int $length = null): string
	{
		return mb_substr($this->_string, $start, $length);
	}


	/**
	 *
	 * @param string $subString
	 * @param int $offset
	 * @return bool
	 *
	 */

	public function contains(string $subString, int $offset = 0): bool
	{
		return mb_strpos($this->_string, $subString, $offset) !== false;
	}


	/**
	 *
	 * @return int
	 *
	 */

	public function binLength(): int
	{
		return strlen($this->_string);
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function __toString(): string
	{
		return $this->_string;
	}
}
