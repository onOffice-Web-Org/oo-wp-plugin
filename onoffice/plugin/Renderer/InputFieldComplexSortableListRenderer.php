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
use onOffice\WPlugin\Language;

/**
 * Description of InputFieldComplexSortableListRenderer
 *
 * @author ana
 */
class InputFieldComplexSortableListRenderer
	extends InputFieldCheckboxRenderer
{

	/** @var array */
	private $_inactiveFields = null;


	/**
	 *
	 * @param string $name
	 * @param array $value
	 *
	 */

	public function __construct($name, $value)
	{
		parent::__construct($name, $value);
	}


	/**
	 *
	 */

	public function render()
	{
		echo '<ul class="filter-fields-list">';
		$i = 1;

		$fields = array();
		$allFields = $this->getValue();
		$deactivatedFields = array();

		foreach ($this->getCheckedValues() as $name)
		{
			if (array_key_exists($name, $allFields))
			{
				$fields[$name] = $allFields[$name];
			}
			else
			{
				if ($this->_inactiveFields == null)
				{
					$this->readInactiveFields();
				}

				if (array_key_exists($name, $this->_inactiveFields))
				{
					$fields[$name] = $this->_inactiveFields[$name];
					$deactivatedFields []= $name;
				}
			}
		}

		foreach ($allFields as $val => $title)
		{
			if (!in_array($val, $this->getCheckedValues()))
			{
				$fields[$val] =  $title;
			}
		}

		foreach ($fields as $key => $label)
		{
			$checked = null;
			$deactivatedStyle = null;
			$deactivatedInTheSoftware = null;

			if (in_array($key, $this->getCheckedValues()))
			{
				$checked = ' checked = "checked" ';

				if (in_array($key, $deactivatedFields))
				{
					$deactivatedStyle = ' style="color:red;" ';
					$deactivatedInTheSoftware = ' ('.__('Disabled in onOffice', 'onoffice').')';
				}
			}

			$inputId = 'label'.$this->getGuiId().'b'.$key;
			echo '<li class="sortable-item" '.$deactivatedStyle.'>'
					.'<input type="'.esc_html($this->getType()).'" name="'.esc_html($this->getName()).'[]'
						.'" value="'.esc_html($key).'"'
						.$checked
						.$this->renderAdditionalAttributes()
						.' id="'.esc_html($inputId).'">'
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

	protected function readInactiveFields()
	{
		$this->_inactiveFields = array();

		$pFieldnames = new \onOffice\WPlugin\Fieldnames();
		$pFieldnames->loadLanguage(true);

		$fieldnames = $pFieldnames->getFieldList(onOfficeSDK::MODULE_ESTATE);

		foreach ($fieldnames as $key => $properties)
		{
			$this->_inactiveFields[$key] = $properties['label'];
		}
	}


	/** @return array */
	public function getInactiveFilds()
	{ return $this->_inactiveFields; }

}
