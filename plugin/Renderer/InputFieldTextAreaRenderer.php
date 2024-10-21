<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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

use Exception;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2024, onOffice(R) GmbH
 *
 */

class InputFieldTextAreaRenderer
	extends InputFieldRenderer
{
	/**
	 *
	 * @param string $type
	 * @param string $name
	 * @param string $value
	 *
	 * @throws Exception
	 */

	public function __construct($type, $name, $value = null)
	{
		parent::__construct($type, $name, $value);
	}


	/**
	 *
	 */

	public function render()
	{
		echo '<textarea name="'.esc_html($this->getName()).'" id="'.esc_html($this->getGuiId()).'"'
			.' '.$this->renderAdditionalAttributes().'>'.esc_html($this->getValue()).'</textarea>';
	}
}
