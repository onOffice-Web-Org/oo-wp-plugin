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
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

abstract class InputFieldRenderer
{
	/** @var string */
	private $_type = null;

	/** @var string */
	private $_name = null;

	/** @var mixed */
	private $_value = null;

	/** @var array  */
	private $_additionalAttributes = array();


	/**
	 *
	 * @param string $type
	 * @param string $name
	 * @param mixed $value
	 *
	 */

	public function __construct($type, $name, $value = null)
	{
		$this->_type = $type;
		$this->_name = $name;
		$this->_value = $value;
	}


	/**
	 *
	 */

	abstract public function render();


	/**
	 *
	 * @param string $name
	 * @param string $value
	 *
	 */

	public function addAdditionalAttribute($name, $value)
	{
		$this->_additionalAttributes[$name] = $value;
	}


	/**
	 *
	 * @param array $attributes
	 *
	 */

	public function setAdditionalAttributes(array $attributes)
	{
		$this->_additionalAttributes = $attributes;
	}


	/** @return string */
	public function getName()
		{ return $this->_name; }


	/** @return mixed */
	public function getValue()
		{ return $this->_value; }

	/** @param mixed $value */
	public function setValue($value)
		{ $this->_value = $value; }


	/** @return string */
	public function getType()
		{ return $this->_type;}

	/**
	 *
	 * @return string
	 *
	 */

	protected function renderAdditionalAttributes()
	{
		$outputValues = array();

		if (count($this->_additionalAttributes) > 0)
		{
			foreach ($this->_additionalAttributes as $name => $value)
			{
				$outputValues []= esc_html($name).'="'.esc_html($value).'"';
			}
		}

		return implode(' ', $outputValues);
	}
}
