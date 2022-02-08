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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class InputFieldRadioRenderer
	extends InputFieldRenderer
{
	/** @var array */
	private $_checkedValue = null;

	/** @var string */
	private $_descriptionTextHtmls = [];

	/**
	 *
	 * @param string $name
	 * @param string $value
	 * @param array  $descriptions
	 */

	public function __construct($name, $value,$descriptions = [])
	{
		$this->_descriptionTextHtmls = $descriptions;
		parent::__construct('radio', $name, $value);
	}


	/** @param array $checkedValue */
	public function setCheckedValue($checkedValue)
		{ $this->_checkedValue = $checkedValue; }


	/** @return array */
	public function getCheckedValue()
		{ return $this->_checkedValue; }

	/**
	 *
	 */

	public function render()
	{
		if (is_array($this->getValue()))
		{
			foreach ($this->getValue() as $key => $label)
			{
				$inputId = 'label'.$this->getGuiId().'b'.$key;
				echo '<input type="'.esc_html($this->getType()).'" name="'.esc_html($this->getName())
					.'" value="'.esc_html($key).'"'
					.($key == $this->_checkedValue ? ' checked="checked" ' : '')
					.$this->renderAdditionalAttributes()
					.' id="'.esc_html($inputId).'">'
					.'<label for="'.esc_html($inputId).'">'.esc_html($label).'</label> '
					.( !empty($this->_descriptionTextHtmls[$key]) ? '<p class="description">'.$this->_descriptionTextHtmls[$key].'</p><br>' : '');
			}
		}
		else
		{
			echo '<input type="'.esc_html($this->getType()).'" name="'.esc_html($this->getName())
				.'" value="'.esc_html($this->getValue()).'"'
				.$this->renderAdditionalAttributes()
				.'>';
		}
	}
}
