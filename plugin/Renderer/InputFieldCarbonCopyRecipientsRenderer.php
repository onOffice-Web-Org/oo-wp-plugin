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

class InputFieldCarbonCopyRecipientsRenderer extends InputFieldSelectTwoRenderer {

	/**
	 *
	 * @param string $name
	 * @param mixed $value
	 *
	 */
	public function __construct($name, $value)
	{
		parent::__construct($name, $value);
	}

	/**
	 * @return void
	 */
	public function render()
	{
		parent::render();
		$textHtml = !empty($this->getHint()) ? '<p class="hint-text">' . $this->getHint() . '</p>' : "";
		echo '<div class="onoffice-cc-recipients-error-message">' . esc_html__('Invalid emails!', 'onoffice-for-wp-websites') . '</div>' . $textHtml;
	}
}