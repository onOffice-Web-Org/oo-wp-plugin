<?php

/**
 *
 *    Copyright (C) 2017-2019 onOffice GmbH
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

class InputFieldButtonRenderer
	extends InputFieldRenderer
{
	/** @var string */
	private $_id = null;

	/** @var string */
	private $_label = null;


	/**
	 *
	 * @param  string  $name
	 * @param  mixed  $value
	 *
	 */

	/**
	 *
	 */

	public function render()
	{
		echo '<input type="button" class="button button-copy" data-clipboard-text="' . esc_html( $this->getValue() ) . '" value="' . esc_html__( 'Copy',
				'onoffice-for-wp-websites' ) . '" '
		     . $this->renderAdditionalAttributes() . '><script>if (navigator.clipboard) { jQuery(".button-copy").show(); }</script>';
	}
}
