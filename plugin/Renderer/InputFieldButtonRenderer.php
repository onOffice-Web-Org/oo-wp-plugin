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

use onOffice\WPlugin\Utility\HtmlIdGenerator;

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
	 * @param string $name
	 * @param mixed $value
	 *
	 */

	/**
	 *
	 */

	public function render()
	{
		$id = HtmlIdGenerator::generateByString($this->_id);
		echo '<input type="button" class="button" id="button-copy" name="'
			.esc_attr($id).'" value="'.esc_html__('Copy !', 'onoffice-for-wp-websites').'" '
			.'data-onoffice-category="'.esc_attr($this->_label).'"'
			. $this->renderAdditionalAttributes().'>';
	}


	/** @param string $id */
	public function setId($id)
	{ $this->_id = $id; }

	/** @return string */
	public function getId()
	{ return $this->_id; }

	/** @return string */
	public function getLabel()
	{ return $this->_label; }

	/** @param string $label */
	public function setLabel($label)
	{ $this->_label = $label; }
}
