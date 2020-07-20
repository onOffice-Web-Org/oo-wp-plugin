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

use DI\DependencyException;
use DI\NotFoundException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use function __;
use function esc_attr;
use function esc_html;


/**
 *
 */

class InputFieldComplexSortableDetailListRenderer
	extends InputFieldRenderer
{
	/** @var array */
	private $_inactiveFields = [];

	/** @var array */
	private $_allFields = [];

	/** @var InputFieldComplexSortableDetailListContentDefault */
	private $_pContentRenderer = null;

	/** @var array */
	private $_extraInputModels = [];


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


	/**
	 *
	 */

	public function render()
	{
		$this->readInactiveFields();
		echo '<ul class="filter-fields-list attachSortableFieldsList" id="sortableFieldsList">';

		$i = 1;

		$fields = [];
		$values = $this->getValue();
		$allFields = $values[0] ?? [];

		foreach ($allFields as $value) {
			$fields[$value] = $this->_allFields[$value] ?? [];
		}

		foreach ($fields as $key => $properties) {
			$label = $properties['label'] ?? null;
			$category = $properties['content'] ?? null;
			$type = $properties['type'] ?? null;
			$this->generateSelectableElement($key, $label, $category, $i, $type,
				false, $this->_extraInputModels);
			$i++;
		}

		// create hidden element for cloning
		$this->generateSelectableElement('dummy_key', 'dummy_label',
			'dummy_category', $i, null, true, $this->_extraInputModels);
		echo '</ul>';
	}

	/**
	 *
	 * @param string $key
	 * @param string $label
	 * @param string $category
	 * @param int $iteration
	 * @param string $type
	 * @param bool $isDummy for javascript-side copying
	 * @param array $extraInputModels
	 * @throws DependencyException
	 * @throws NotFoundException
	 */

	private function generateSelectableElement($key, $label, $category,
		$iteration, $type, $isDummy = false, array $extraInputModels = [])
	{
		$inactiveFields = $this->_inactiveFields;

		$deactivatedStyle = null;
		$deactivatedInOnOffice = null;
		$dummyText = $isDummy ? 'data-onoffice-ignore="true"' : '';

		if ($label == null) {
			$label = $inactiveFields[$key] ?? null;
			$deactivatedStyle = ' style="color:red;" ';
			$deactivatedInOnOffice = ' ('.__('Disabled in onOffice', 'onoffice').')';
		}

		echo '<li class="sortable-item" id="menu-item-'.esc_html($key).'">'
			.'<div class="menu-item-bar">'
				.'<div class="menu-item-handle ui-sortable-handle">'
					.'<span class="item-title" '.$deactivatedStyle.'>'
						.esc_html($label)
						.esc_html($deactivatedInOnOffice)
					.'</span>'
					.'<span class="item-controls">'
						.'<span class="item-type">'.esc_html($category).'</span>'
						.'<a class="item-edit"><span class="screen-reader-text">'.esc_html__('Edit', 'onoffice').'</span></a>'
					.'</span>'
					.'<input type="hidden" name="filter_fields_order'.esc_html($iteration).'[id]" value="'.esc_html($iteration).'">'
					.'<input type="hidden" name="filter_fields_order'.esc_html($iteration).'[name]" value="'.esc_html($label).'">'
					.'<input type="hidden" name="filter_fields_order'.esc_html($iteration).'[slug]" value="'.esc_html($key).'">'
					.'<input type="hidden" name="'.esc_attr($this->getName()).'[]" value="'.esc_html($key).'" '
						.' '.$this->renderAdditionalAttributes().' '.$dummyText.'>'
				.'</div>'
			.'</div>'
			.'<div class="menu-item-settings submitbox" style="display:none;">';

			if ($this->_pContentRenderer !== null) {
				$this->_pContentRenderer->render($key, $isDummy, $type, $extraInputModels);
			}

		echo '</div>';
		echo '</li>';
	}


	/**
	 *
	 */

	private function readInactiveFields()
	{
		$pFieldnames = new Fieldnames(new FieldsCollection(), true);
		$pFieldnames->loadLanguage();
		$fieldnames = $pFieldnames->getFieldList(onOfficeSDK::MODULE_ESTATE);
		$oldKeys = array_keys($fieldnames);
		$this->_inactiveFields = array_combine($oldKeys, array_column($fieldnames, 'label'));
	}


	/** @param array $allFields */
	public function setAllFields(array $allFields)
		{ $this->_allFields = $allFields; }

	/** @param InputFieldComplexSortableDetailListContentDefault $pContentRenderer */
	public function setContentRenderer(InputFieldComplexSortableDetailListContentDefault $pContentRenderer)
		{ $this->_pContentRenderer = $pContentRenderer; }

	/**
	 * @param array $extraInputModels
	 */
	public function setExtraInputModels(array $extraInputModels)
		{ $this->_extraInputModels = $extraInputModels; }
}