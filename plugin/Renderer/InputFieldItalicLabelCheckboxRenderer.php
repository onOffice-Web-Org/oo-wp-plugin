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

use Exception;
use function esc_html;


/**
 *
 */

class InputFieldItalicLabelCheckboxRenderer
	extends InputFieldRenderer
{
	/** @var string */
	private $_descriptionTextHTML;


	/**
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param mixed $description
	 *
	 */

	public function __construct($name, $value, $description = '')
	{
		$this->_descriptionTextHTML = $description;
		parent::__construct('checkbox', $name, $value);
	}

	/**
	 *
	 * @throws Exception
	 */

	public function render()
	{
		$textHtml = '';
		echo '<input type="' . esc_html($this->getType()) . '" name="' . esc_html($this->getName())
			. '" value="' . esc_html($this->getValue()) . '"'
			. ($this->getValue() == $this->getCheckedValues() ? ' checked="checked" ' : '')
			. $this->renderAdditionalAttributes()
			. ' id="' . esc_html($this->getGuiId()) . '">'
			. (!empty($this->_descriptionTextHTML) && is_string($this->_descriptionTextHTML) ? '<p class="description">' . $this->_descriptionTextHTML . '</p><br>' : '')
			. $textHtml;
	}
}
