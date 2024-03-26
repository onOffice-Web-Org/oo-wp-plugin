<?php

/**
 *
 *    Copyright (C) 2023 onOffice Software AG
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

class InputFieldToggleSwitchRenderer extends InputFieldRenderer
{
	/**
	 *
	 * @param string $type
	 * @param string $name
	 * @param mixed $value
	 *
	 */
	public function __construct(string $type, string $name, $value)
	{
		parent::__construct($type, $name, $value);
	}

	public function render()
	{
		echo '<label class="oo-toggle-switch"><input type="checkbox"'
			.' value="'.esc_html($this->getValue()).'"'.($this->getValue() == $this->getCheckedValues() ? ' checked="checked" ' : '')
			.' name="'.esc_html($this->getName()).'">'
			.'<span class="slider round"></span></label>';
	}
}