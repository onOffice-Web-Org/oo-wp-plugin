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

class InputModel
{
	/** */
	const HTML_TYPE_SELECT = 'select';

	/** */
	const HTML_TYPE_CHECKBOX = 'checkbox';

	/** */
	const HTML_TYPE_RADIO = 'radio';

	/** */
	const HTML_TYPE_TEXT = 'text';

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

	/** @var string */
	private $_name = null;

	/** @var string */
	private $_label = null;

	/** @var string */
	private $_type = 'string';

	/** @var string */
	private $_description = null;

	/** @var mixed */
	private $_default = null;

	/** @var bool */
	private $_showInRest = false;

	/** @var bool */
	private $_sanitizeCallback = null;

	/** @var bool */
	private $_isPassword = false;

	/** @var mixed */
	private $_value = null;

	/** @var string */
	private $_htmlType = self::HTML_TYPE_TEXT;

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
		$this->_optionGroup = $optionGroup;
		$this->_name = $name;
		$this->_label = $label;
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

	public function getOptionName()
	{
		return $this->getOptionGroup().'-'.$this->getName();
	}

	/** @return string */
	public function getLabel()
		{ return $this->_label; }

	/** @return string */
	public function getName()
		{ return $this->_name; }

	/** @param string $name */
	public function setName($name)
		{ $this->_name = $name; }

	/** @param string $label */
	public function setLabel($label)
		{ $this->_label = $label; }

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

	/** @return bool */
	public function getIsPassword()
		{ return $this->_isPassword; }

	/** @param bool $isPassword */
	public function setIsPassword($isPassword)
		{ $this->_isPassword = $isPassword; }

	/** @return string */
	public function getOptionGroup()
		{ return $this->_optionGroup; }

	/** @return mixed */
	public function getValue()
		{ return $this->_value; }

	/** @param mixed $value */
	public function setValue($value)
		{ $this->_value = $value; }

	/** @param string $htmlType */
	public function setHtmlType($htmlType)
		{ $this->_htmlType = $htmlType; }

	/** @return string */
	public function getHtmlType()
		{ return $this->_htmlType; }
}
