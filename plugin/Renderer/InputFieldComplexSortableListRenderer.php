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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Types\FieldsCollection;
use function esc_html;

/**
 *
 */

class InputFieldComplexSortableListRenderer
	extends InputFieldCheckboxRenderer
{
	/** @var array */
	private $_inactiveFields = null;


	/**
	 *
	 */

	public function render()
	{
		echo '<ul class="filter-fields-list">';
		$i = 1;

		$fields = [];
		$allFields = $this->getValue();
		$deactivatedFields = [];

		foreach ($this->getCheckedValues() as $name) {
			if (isset($allFields[$name])) {
				$fields[$name] = $allFields[$name];
			} else {
				$this->readInactiveFields();

				if (isset($this->_inactiveFields[$name])) {
					$fields[$name] = $this->_inactiveFields[$name];
					$deactivatedFields []= $name;
				}
			}
		}

		foreach ($fields as $key => $label) {
			$checked = '';
			$deactivatedStyle = '';
			$deactivatedInTheSoftware = '';

			if (in_array($key, $this->getCheckedValues())) {
				$checked = ' checked="checked" ';

				if (in_array($key, $deactivatedFields)) {
					$deactivatedStyle = ' style="color:red;" ';
					$deactivatedInTheSoftware = ' ('.esc_html__('Disabled in onOffice', 'onoffice-for-wp-websites').')';
				}
			}

			echo '<li class="sortable-item" '.$deactivatedStyle.'>'
					.'<input type="'.esc_html($this->getType()).'" name="'.esc_html($this->getName()).'[]'
						.'" value="'.esc_html($key).'"'
						.$checked
						.$this->renderAdditionalAttributes()
						.' id="'.esc_html('label'.$this->getGuiId().'b'.$key).'">'
						.esc_html($label)
						.$deactivatedInTheSoftware
					.'<input type="hidden" name="filter_fields_order'.$i.'[id]" value="'.$i.'">'
					.'<input type="hidden" name="filter_fields_order'.$i.'[name]" value="'.$label.'">'
					.'<input type="hidden" name="filter_fields_order'.$i.'[slug]" value="'.$key.'">'
				.'</li>';

			$i++;
		}

		echo '</ul>';
	}


	/**
	 *
	 */

	private function readInactiveFields()
	{
		if ($this->_inactiveFields === null) {
			$this->_inactiveFields = [];

			$pFieldnames = $this->getFieldnames(new FieldsCollection(), true);
			$pFieldnames->loadLanguage();

			$fieldnames = $pFieldnames->getFieldList(onOfficeSDK::MODULE_ESTATE);

			foreach ($fieldnames as $key => $properties) {
				$this->_inactiveFields[$key] = $properties['label'];
			}
		}
	}
}
