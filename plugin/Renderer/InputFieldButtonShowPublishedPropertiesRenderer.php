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


/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2024, onOffice(R) GmbH
 *
 */

class InputFieldButtonShowPublishedPropertiesRenderer
	extends InputFieldRenderer
{
	/**
	 *
	 * @param string $type
	 * @param string $name
	 * @param mixed $value
	 *
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
		echo '<p class="wp-clearfix custom-input-field">'
			. '<span><span class="spinner"></span></span>'
			. '<button id="show-published-properties" class="button action">' . esc_html($this->getLabel()) . '</button>'
			. '<div class="message-show-published-properties" style="display:none">' . $this->getHint() . '</div>'
			. '</p>';
	}
}
