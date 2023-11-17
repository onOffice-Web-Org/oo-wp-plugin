<?php

/**
 *
 *    Copyright (C) 2023 onOffice GmbH
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
 */
class InputFieldPasswordRenderer extends InputFieldRenderer
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
		if (!in_array($type, array('password'))) {
			throw new Exception(' wrong type!');
		}
		parent::__construct($type, $name, $value);
	}


	/**
	 *
	 */

	public function render()
	{
		$iconShowPassword = '';
		if ($this->getName() === 'onoffice-settings-captcha-secretkey') {
			$iconShowPassword = '<button type="button" class="button" data-toggle="0">
				<span class="dashicons dashicons-visibility oo-icon-eye-secret-key" aria-hidden="true"></span> 
				</button>';
		} elseif ($this->getName() === 'onoffice-settings-captcha-sitekey') {
			$iconShowPassword = '<button type="button" class="button" data-toggle="0">
				<span class="dashicons dashicons-visibility oo-icon-eye-site-key" aria-hidden="true"></span> 
				</button>';
		}
		echo '<div class="oo-google-recaptcha-key">';
		echo '<input type="' . esc_html($this->getType()) . '" name="' . esc_html($this->getName())
			. '" value="' . esc_html($this->getValue()) . '" id="' . esc_html($this->getGuiId()) . '"'
			. ' ' . $this->renderAdditionalAttributes()
			. '>' . $iconShowPassword;
		echo '</div>';
	}
}