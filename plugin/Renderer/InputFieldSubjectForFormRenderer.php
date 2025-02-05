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

class InputFieldSubjectForFormRenderer
	extends InputFieldRenderer
{
	/**
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @throws Exception
	 */

	public function __construct($name, $value = null)
	{
		parent::__construct('subject', $name, $value);
	}


	/**
	 *
	 */

	public function render()
	{
		$textHtml = '';
		if (!empty($this->getHint())) {
			$textHtml = '<p class="hint-text">' . esc_html($this->getHint()) . '</p>';
		}
		echo '<div class="oo-email-subject-container">'
        	. '<button class="oo-insert-variable-button">'.__('Insert variable', 'onoffice-for-wp-websites').'</button>'
			. '<div class="oo-email-subject-title" contenteditable="true"></div>'
			. '<div class="oo-email-subject-suggestions"></div>'
			. '<input type="hidden" class="oo-email-subject-output" name="' . esc_html($this->getName()) . '" value="' . esc_html($this->getValue()) . '">'
			. '</div>'.$textHtml;
	}
}
