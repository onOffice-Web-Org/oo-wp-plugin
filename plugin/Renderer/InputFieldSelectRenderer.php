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

class InputFieldSelectRenderer
	extends InputFieldRenderer
{
	/** @var bool */
	private $_multiple = false;

	/** @var array */
	private $_selectedValue = [];

	/** @var array  */
	private $_labelOnlyValues = [];

	/**
	 *
	 * @param string $name
	 * @param array $value
	 *
	 */

	public function __construct($name, $value = array())
	{
		parent::__construct('select', $name, $value);
	}


	/**
	 *
	 */

	public function render()
	{
		echo '<select name="'.esc_html($this->getName()).'" '
			 .($this->_multiple ? ' multiple = "multiple" ' : null)
			 .$this->renderAdditionalAttributes()
			 .' id="'.esc_html($this->getGuiId()).'">';

		foreach ($this->getValue() as $key => $label)
		{
			if (in_array($key, $this->_labelOnlyValues)) {
				echo '<optgroup label="'.esc_html($label).'" '
					.($key == $this->_selectedValue ? ' selected="selected" ' : null).'></optgroup>';
			} else {
				echo '<option value="'.esc_html($key).'" '
					.($key == $this->_selectedValue ? ' selected="selected" ' : null).'>'
					.esc_html($label)
					.'</option>';
			}
		}

		echo '</select>';
	}


	/**
	 *
	 * @param string $selectedValue
	 *
	 */

	public function setSelectedValue($selectedValue)
		{ $this->_selectedValue = $selectedValue; }


	/** @return string */
	public function getSelectedValue()
		{ return $this->_selectedValue; }


	/**
	 * @param array $labelOnlyValues
	 */
	public function setLabelOnlyValues(array $labelOnlyValues)
	{
		$this->_labelOnlyValues = $labelOnlyValues;
	}
}
