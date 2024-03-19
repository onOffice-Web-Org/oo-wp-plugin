<?php

/**
 *
 *    Copyright (C) 2017-2024 onOffice GmbH
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

class InputFieldSelectTwoRenderer extends InputFieldRenderer {

	/** @var boolean */
	private $_multiple = true;

	/**
	 *
	 * @param string $name
	 * @param mixed $value
	 *
	 */
	public function __construct($name, $value)
	{
		parent::__construct('select2', $name, $value);
	}

	public function render()
	{
		$name = $this->getMultiple() ? $this->getName() . '[]' : $this->getName();
		$output = '<select name="'.esc_html($name).'"'
				  .$this->renderAdditionalAttributes()
				  .' id="'.esc_html($this->getGuiId()).'"'
				  .' multiple ="multiple" '
				  .'>';
		$selectedValues = $this->getSelectedValue();
		foreach ($selectedValues as $value) {
			$selected = 'selected="selected"';
			$output .= '<option value="'.esc_html($value).'" '.$selected.'>'.esc_html($value).'</option>';
		}
		$output .= '</select>';
		echo $output;
	}


	/**
	 * @return bool
	 */
	public function getMultiple(): bool
	{ return $this->_multiple; }

	/**
	 * @param bool $multiple
	 */
	public function setMultiple(bool $multiple)
	{ $this->_multiple = $multiple; }
}