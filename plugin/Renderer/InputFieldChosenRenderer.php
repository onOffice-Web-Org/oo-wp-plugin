<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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


class InputFieldChosenRenderer
	extends InputFieldSelectRenderer
{
	/**
	 * @return void
	 */
	public function render()
	{
		$output = '<select name="'.esc_html($this->getName()).'"'
					.$this->renderAdditionalAttributes()
					.' id="'.esc_html($this->getGuiId()).'"'
					.' multiple >';

		foreach ($this->getValue() as $key => $label) {
			$selected = null;
			if (in_array($key, $this->getSelectedValue())) {
				$selected = 'selected="selected"';
			}
			$output .= '<option value="'.esc_html($key).'" '.$selected.'>'.esc_html($label).'</option>';
		}

		$output .= '</select>';

		echo $output;
	}
}