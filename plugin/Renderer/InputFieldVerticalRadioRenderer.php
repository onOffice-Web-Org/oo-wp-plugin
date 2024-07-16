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
use function esc_html;

class InputFieldVerticalRadioRenderer extends InputFieldRenderer
{
	/** @var int */
	private $_checkedValue = 0;

	/**
	 *
	 * @param string $name
	 * @param array $value
	 *
	 */
	public function __construct($name, $value)
	{
		parent::__construct('radio', $name, $value);
	}

	/**
	 *
	 */
	public function render() {
		$textHtml = '';
		if (!empty($this->getHint())) {
			$textHtml = '<p class="oo-information-text">' . esc_html($this->getHint()) . '</p>';
		}
		echo '<div class="oo-vertical-radio">';
		foreach ($this->getValue() as $key => $label)
		{
			$inputId = 'label'.$this->getGuiId().'b'.$key.$this->getName();
			echo '<input type="'.esc_html($this->getType()).'" name="'.esc_html($this->getName())
				 .'" value="'.esc_html($key).'"'
				 .($key == $this->getCheckedValue() ? ' checked="checked" ' : '')
				 .' id="'.esc_html($inputId).'">'
				 .'<label for="'.esc_html($inputId).'">'.esc_html($label).'</label><br>';
		}
		echo $textHtml .'</div>';
	}

	/** @param string $checkedValue */
	public function setCheckedValue(string $checkedValue)
	{ $this->_checkedValue = $checkedValue; }

	/** @return string */
	public function getCheckedValue(): string
	{ return $this->_checkedValue; }
}