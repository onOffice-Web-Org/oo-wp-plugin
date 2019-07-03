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

namespace onOffice\WPlugin\Model;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class InputModelLabel
	extends InputModelBase
{
	/** */
	const VALUE_ENCLOSURE_ITALIC = 'italic';

	/** */
	const VALUE_ENCLOSURE_CODE = 'code';

	/** @var string */
	private $_valueEnclosure = self::VALUE_ENCLOSURE_ITALIC;


	/**
	 *
	 * @param string $label
	 *
	 */

	public function __construct($label, $value)
	{
		$this->setLabel($label);
		$this->setValue($value);
		$this->setHtmlType(self::HTML_TYPE_LABEL);
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getIdentifier(): string
	{
		return '';
	}


	/** @return string */
	public function getValueEnclosure()
		{ return $this->_valueEnclosure; }

	/** @param string $valueEnclosure */
	public function setValueEnclosure($valueEnclosure)
		{ $this->_valueEnclosure = $valueEnclosure; }
}
