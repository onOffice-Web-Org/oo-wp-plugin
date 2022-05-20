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
	/** @var boolean */
	private $_multiple = true;

	/**
	 * @return void
	 */
	public function render()
	{
		$output = '<select name="'.esc_html($this->getName()).'"'
					.$this->renderAdditionalAttributes()
					.' id="'.esc_html($this->getGuiId()).'"'
					. ($this->getMultiple() ? ' multiple' : '')
					. ' style="width: 230px;">';
		$values = $this->getValue();
		if (array_key_exists('group', $values)) {
			foreach ($values['group'] as $k => $group) {
				$output .= '<optgroup label="' . esc_html($k) . '">';
				foreach ($group as $key => $label) {
					$selected = null;
					if ( is_array( $this->getSelectedValue() ) ) {
						if ( in_array( $key, $this->getSelectedValue(), true ) ) {
							$selected = 'selected="selected"';
						}
					} elseif ( $key == $this->getSelectedValue() ) {
						$selected = 'selected="selected"';
					}
					$output .= '<option value="'.esc_html($key).'" '.$selected.'>'.esc_html($label).'</option>';
				}
				$output .= '</optgroup>';
			}
		} else {
			$selectedValue = $this->getSelectedValue();
			foreach ($values as $key => $label) {
				$selected = null;
				if (
					(is_array($selectedValue) && in_array($key, $this->getSelectedValue()))
					|| $key === $selectedValue
				) {
					$selected = 'selected="selected"';
				}
				$output .= '<option value="'.esc_html($key).'" '.$selected.'>'.esc_html($label).'</option>';
			}
		}

		$output .= '</select>';

		echo $output;
	}

	/**
	 * @return bool
	 */
	public function getMultiple()
	{ return $this->_multiple; }

	/**
	 * @param bool $multiple
	 */
	public function setMultiple($multiple)
	{ $this->_multiple = $multiple; }

}