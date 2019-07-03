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

namespace onOffice\WPlugin\Model;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class InputModelOption
	extends InputModelBase
{
	/**
	 *
	 * Setting types
	 * @see https://developer.wordpress.org/reference/functions/register_setting/
	 *
	 */

	/** */
	const SETTING_TYPE_STRING = 'string';

	/** */
	const SETTING_TYPE_BOOLEAN = 'boolean';

	/** */
	const SETTING_TYPE_INTEGER = 'integer';

	/** */
	CONST SETTING_TYPE_NUMBER = 'number';


	/** @var string */
	private $_optionGroup = null;

	/** @var string WP-specific type */
	private $_type = 'string';

	/** @var string */
	private $_description = null;

	/** @var mixed */
	private $_default = null;

	/** @var bool */
	private $_showInRest = false;

	/** @var bool */
	private $_sanitizeCallback = null;


	/**
	 *
	 * @param string $optionGroup
	 * @param string $name
	 * @param string $label
	 * @param string $type
	 *
	 */

	public function __construct($optionGroup, $name, $label, $type)
	{
		$this->setName($name);
		$this->setLabel($label);
		$this->_optionGroup = $optionGroup;
		$this->_type = $type;

		if ($type === self::SETTING_TYPE_BOOLEAN)
		{
			$this->_default = false;
		}
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getIdentifier(): string
	{
		return $this->getOptionGroup().'-'.$this->getName();
	}


	/** @return string */
	public function getType()
		{ return $this->_type; }

	/** @return string */
	public function getDescription()
		{ return $this->_description; }

	/** @return mixed */
	public function getDefault()
		{ return $this->_default; }

	/** @return bool */
	public function getShowInRest()
		{ return $this->_showInRest; }

	/** @return callable */
	public function getSanitizeCallback()
		{ return $this->_sanitizeCallback; }

	/** @param string $type */
	public function setType($type)
		{ $this->_type = $type; }

	/** @param string $description */
	public function setDescription($description)
		{ $this->_description = $description; }

	/** @param mixed $default */
	public function setDefault($default)
		{ $this->_default = $default; }

	/** @param bool $showInRest */
	public function setShowInRest($showInRest)
		{ $this->_showInRest = $showInRest; }

	/** @param callable $sanitizeCallback */
	public function setSanitizeCallback($sanitizeCallback)
		{ $this->_sanitizeCallback = $sanitizeCallback; }

	/** @return string */
	public function getOptionGroup()
		{ return $this->_optionGroup; }
}
