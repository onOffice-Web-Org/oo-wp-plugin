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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class InputFieldNumberRenderer
	extends InputFieldRenderer
{
	/**
	 *
	 * @param string $name
	 * @param string $value
	 *
	 */

	public function __construct($name, $value = null)
	{
		parent::__construct('number', $name, $value);
	}


	/**
	 *
	 */

	public function render()
	{
		$textHtml = '';
		if ( ! empty( $this->getHint() ) ) {
			$textHtml = '<p class="memssageRecordsPerPage">' . esc_html( $this->getHint() ) . '</p>';
		}
		$max = '';
		if ( ! empty( $this->getMaxValue() ) ) {
			$max = 'max="' . esc_html( $this->getMaxValue() ) . '"';
		}
		$min = '';
		if ( ! empty( $this->getMinValue() ) ) {
			$min = ' min="' . esc_html( $this->getMinValue() ) . '"';
		}
		echo '<input type="' . esc_html( $this->getType() ) . '" name="' . esc_html( $this->getName() )
		     . '" value="' . esc_html( $this->getValue() ) . '" id="' . esc_html( $this->getGuiId() ) . '"'
		     . ' ' . $this->renderAdditionalAttributes() . $max . $min
		     . '>' . $textHtml;
	}
}
