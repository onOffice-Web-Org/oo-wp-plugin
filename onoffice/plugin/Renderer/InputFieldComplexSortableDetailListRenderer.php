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
 * Description of InputFieldComplexSortableDetailListRenderer
 *
 * @author ana
 */
class InputFieldComplexSortableDetailListRenderer
		extends InputFieldRenderer
{
	/** @var array */
	private $_inactiveFields = null;

	/** @var array */
	private $_allFields = array();


	/**
	 *
	 * @param string $name
	 * @param array $value
	 *
	 */

	public function __construct($name, $value)
	{
		parent::__construct('li', $name, $value);
	}


	/** @param array */
	public function setAllFields($allFields)
	{ $this->_allFields = $allFields; }


	/** */
	public function render()
	{
		echo '<ul class="filter-fields-list" id="sortableFieldsList">';

		$i = 1;

		$fields = array();
		$asd = $this->getValue();
		$allFields = $asd[0];
		$inactiveFields = $this->getInactiveFields();

		foreach ($allFields as $value)
		{
			$fields[$value] = $this->_allFields[$value];
		}

		foreach ($fields as $key => $label)
		{
			$deactivatedStyle = null;
			$deactivatedInTheSoftware = null;

			if ($label == null)
			{
				$label = $inactiveFields[$key];
				$deactivatedStyle = ' style="color:red;" ';
				$deactivatedInTheSoftware = ' ('.__('deactivated in onOffice', 'onoffice').')';
			}

			echo '<li class="sortable-item" id="menu-item-'.$key.'">'
					.'<div class="menu-item-bar">'
						.'<span class="item-title" '.$deactivatedStyle.'>'.esc_html(__($label, 'onoffice')).$deactivatedInTheSoftware.'</span>'
						.'<span class="item-controls">'
							.'<a class="item-edit-link">'.__('Edit', 'onoffice').'</a>'
						.'</span>'
						.'<input type="hidden" name="filter_fields_order'.$i.'[id]" value="'.$i.'">'
						.'<input type="hidden" name="filter_fields_order'.$i.'[name]" value="'.$label.'">'
						.'<input type="hidden" name="filter_fields_order'.$i.'[slug]" value="'.$key.'">'
						.'<input type="hidden" name="'.$this->getName().'[]" value="'.$key.'" '
							.' '.$this->renderAdditionalAttributes().'>'
					.'</div>'
					.'<div class="menu-item-settings" style="display:none">'
						.'<a class="item-delete-link">'.__('Delete', 'onoffice').'</a>'
						.'<span class="menu-item-settings-name">'.$key.'</span>'
					.'</div>'
				.'</li>';

			$i++;
		}

		// ein unsichtbares set zum klonen anlegen
		echo '<li class="sortable-item" id="menu-item-dummyField" style="display:none;">'
				.'<div class="menu-item-bar">'
					.'<span class="item-title">dummy_label</span>'
					.'<span class="item-controls">'
						.'<a class="item-edit-link">'.__('Edit', 'onoffice').'</a>'
					.'</span>'
					.'<input type="hidden" name="filter_fields_order'.$i.'[id]" value="'.$i.'">'
					.'<input type="hidden" name="filter_fields_order'.$i.'[name]" value="dummy_label">'
					.'<input type="hidden" name="filter_fields_order'.$i.'[slug]" value="dummy_key">'
					.'<input type="hidden" name="'.$this->getName().'[]" value="dummy_key" '
						.' class="onoffice-dummy-input">'
				.'</div>'
				.'<div class="menu-item-settings" style="display:none">'
					.'<a class="item-delete-link">'.__('Delete', 'onoffice').'</a>'
					.'<span class="menu-item-settings-name">dummy_key</span>'

				.'</div>'
			.'</li>';

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
	public function getInactiveFields()
	{
		if (null === $this->_inactiveFields)
		{
			$this->readInactiveFields();
		}

		return $this->_inactiveFields;
	}
}
