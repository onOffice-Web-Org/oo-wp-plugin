<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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

use onOffice\WPlugin\Model\InputModelLabel;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class InputFieldLabelRenderer
	extends InputFieldRenderer
{
	/** @var string */
	private $_label = null;

	/** @var string */
	private $_valueEnclosure = InputModelLabel::VALUE_ENCLOSURE_ITALIC;

	/** @var array */
	private $_enclosureConfig = array(
		InputModelLabel::VALUE_ENCLOSURE_ITALIC => '<span class="italic">%s</span>',
		InputModelLabel::VALUE_ENCLOSURE_CODE => '<code>%s</code>',
	);


	/**
	 *
	 */

	public function render()
	{
		$additional = '';
		if ($this->getValue() !== null) {
			$enclosure = $this->_enclosureConfig[$this->_valueEnclosure];
			$additional = sprintf($enclosure, esc_html($this->getValue()));
		}

		echo '<span class="viewusage" id="'.esc_html($this->getGuiId()).'" '
				.$this->renderAdditionalAttributes().'>'
				.esc_html($this->_label).$additional
			.'</span>';
	}


	/** @return string */
	public function getLabel()
		{ return $this->_label; }

	/** @param string $label */
	public function setLabel($label)
		{ $this->_label = $label; }

	/** @return string */
	public function getValueEnclosure()
		{ return $this->_valueEnclosure; }

	/** @param string $valueEnclosure */
	public function setValueEnclosure($valueEnclosure)
		{ $this->_valueEnclosure = $valueEnclosure; }
}
