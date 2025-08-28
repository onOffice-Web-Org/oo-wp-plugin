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
use onOffice\WPlugin\Gui\AdminPageAjax;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Types\FieldsCollection;
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

	/** @var bool */
	private $_isMultiPage = false;

	/** @var string */
	private $_template = '';

	/** @var array */
	private $_titlesPerMultipage = [];

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

	 private function isOwnerLeadGeneratorForm(): bool
	{
    	return strpos($this->_template, 'ownerleadgeneratorform.php') !== false;
	}

	public function render()
	{
		$this->readInactiveFields();
		$values = $this->getValue();
		$allFields = $values[0] ?? [];

		if ($this->_isMultiPage) {
			$isOwnerLeadGeneratorForm = $this->isOwnerLeadGeneratorForm();
			echo '<div id="single-page-container" style="display: ' . ($isOwnerLeadGeneratorForm ? 'none' : 'block') . ';">';
			$this->renderSinglePage($allFields);
			echo '</div>';
			echo '<div id="multi-page-container" style="display: ' . ($isOwnerLeadGeneratorForm ? 'block' : 'none') . ';">';
			$this->renderMultiPage($allFields);
			echo '</div>';
			echo '<p class="add-page-container"><button class="add-page-button" type="button" style="display: ' . ($isOwnerLeadGeneratorForm ? 'block' : 'none') . ';">' . __( 'Add Page', 'onoffice-for-wp-websites' ) . '</button><p>';
		} else {
			$this->renderSinglePage($allFields);
		}
	}

	private function renderSinglePage(array $allFields): void
	{
		echo '<ul class="filter-fields-list attachSortableFieldsList sortableFieldsListForForm" id="sortableFieldsList">';
		$i = 1;
		$fields = [];
		foreach ($allFields as $value) {
			$fields[$value] = $this->_allFields[$value] ?? [];
		}
		foreach ($fields as $key => $properties) {
			$label = $properties['label'] ?? null;
			$category = $properties['content'] ?? null;
			$type = $properties['type'] ?? null;
			$this->generateSelectableElement($key, $label, $category, $i, $type, false, $this->_extraInputModels);
			$i++;
		}
		$this->generateSelectableElement('dummy_key', 'dummy_label', 'dummy_category', $i, null, true, $this->_extraInputModels);
		echo '</ul>';
	}

	private function renderMultiPage(array $allFields): void
	{
		$fieldsByPage = [];
		foreach ($allFields as $properties) {
			$page = $this->_allFields[$properties]['page'] ?? 1;
			$fieldsByPage[$page][$properties] = $this->_allFields[$properties];
		}
		$titleInputModels = [];
		$extraInputModels = array_values(array_filter($this->_extraInputModels, function($pInputModel) use (&$titleInputModels) {
			if ($pInputModel->getTable() === 'oo_plugin_form_multipage_title') {
				$titleInputModels[] = $pInputModel;
				return false;
			}
			return true;
		}));

		$page = 1;
		foreach ($fieldsByPage as $fields) {
			echo '<div class="list-fields-for-each-page">';
			echo '<div class="multi-page-title" data-page="'.$page.'">';
			echo '<span class="multi-page-counter">'.sprintf(esc_html__('Page %s', 'onoffice-for-wp-websites'), $page).'</span>';
			$this->_pContentRenderer->renderTitlesForMultiPage($titleInputModels, $this->getLocalizedTitlesPerPage($page));
			echo '</div>';
			echo '<ul class="filter-fields-list attachSortableFieldsList multi-page-list fieldsListPage-' . esc_attr($page) . ' sortableFieldsListForForm">';
			$i = 1;

			foreach ($fields as $key => $properties) {
				$label = $properties['label'] ?? null;
				$category = $properties['content'] ?? null;
				$type = $properties['type'] ?? null;
				$this->generateSelectableElement($key, $label, $category, $i, $type, false, $extraInputModels, $page);
				$i++;
			}

			$this->generateSelectableElement('dummy_key', 'dummy_label', 'dummy_category', $i, null, true, $extraInputModels, $page);
			echo '</ul>';
			echo '<div class="item-remove-page"><a class="item-remove-page-link submitdelete">'.esc_html__('Remove Page', 'onoffice-for-wp-websites').'</a></div>';
			echo '</div>';
			$page++;
		}

		$this->generateSelectableElement('dummy_key', 'dummy_label', 'dummy_category', 100, null, true, $extraInputModels, 7);
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
	 * @param int $page
	 * @throws DependencyException
	 * @throws NotFoundException
	 */

	private function generateSelectableElement($key, $label, $category,
		$iteration, $type, $isDummy = false, array $extraInputModels = [], int $page = 1)
	{
		$inactiveFields = $this->_inactiveFields;

		$deactivatedStyle = null;
		$deactivatedInOnOffice = null;
		$dummyText = $isDummy ? 'data-onoffice-ignore="true"' : '';
		$name = $isDummy ? AdminPageAjax::EXCLUDE_FIELD . $this->getName() : $this->getName();
		$isHighlighted = false;
		if(gettype($extraInputModels[0]->getValueCallback()) == 'array') {
			$formModelBuilder = $extraInputModels[0]->getValueCallback()[0];
			if(str_contains(get_class($formModelBuilder), 'FormModelBuilderDBEstateListSettings')) {
				$isHighlighted = $formModelBuilder->isHightlightedField($key);
			}
			else if(
				str_contains(get_class($formModelBuilder),'FormModelBuilderEstateDetailSettings') ||
				str_contains(get_class($formModelBuilder),'FormModelBuilderSimilarEstateSettings')
			) {
				$isHighlighted = $formModelBuilder->isHightlightedField($key);
			}
		}

		if ($label == null) {
			$label = $inactiveFields[$key] ?? null;
			$deactivatedStyle = ' style="color:red;" ';
			$deactivatedInOnOffice = ' ('.__('Disabled in onOffice', 'onoffice-for-wp-websites').')';
			$type = InputModelBase::HTML_TYPE_TEXT;
		}

		echo '<li class="sortable-item item' . ($this->_isMultiPage ? ' page-' . esc_attr($page) : '') . '" id="menu-item-' . esc_attr($key) . '" action-field-name="labelButtonHandleField-' . esc_attr($key) . '">'
			.'<div class="menu-item-bar">'
				.'<div class="menu-item-handle ui-sortable-handle" style="display: flex; align-items: center; gap: 8px;">'
					.'<span class="oo-sortable-checkbox-wrapper">'
						.'<input type="checkbox" class="oo-sortable-checkbox" value="'.$key.'" onchange="ooHandleChildCheckboxChange(event)"/>'
					.'</span>'
					.'<span class="item-title oo-sortable-title" '.$deactivatedStyle.'>'
						.($isHighlighted ? '★ ' : '')
						.esc_html($label)
						.esc_html($deactivatedInOnOffice)
					.'</span>'
					.'<span class="item-controls">'
						.'<span class="item-type">'.esc_html($category).'</span>'
						.'<a class="item-edit"><span class="screen-reader-text">'.esc_html__('Edit', 'onoffice-for-wp-websites').'</span></a>'
					.'</span>'
					.'<input type="hidden" name="filter_fields_order'.esc_html($iteration).'[id]" value="'.esc_html($iteration).'">'
					.'<input type="hidden" name="filter_fields_order'.esc_html($iteration).'[name]" value="'.esc_html($label).'">'
					.'<input type="hidden" name="filter_fields_order'.esc_html($iteration).'[slug]" value="'.esc_html($key).'">'
					.'<input type="hidden" name="'.esc_attr($name).'[]" value="'.esc_html($key).'" '
						.' '.$this->renderAdditionalAttributes().' '.$dummyText.'>'
				.'</div>'
			.'</div>'
			.'<div class="menu-item-settings submitbox" style="display:none;">';

			if ($this->_pContentRenderer !== null) {
				$this->_pContentRenderer->render($key, $isDummy, $type, $extraInputModels, $this->_isMultiPage);
			}

		echo '</div>';
		echo '</li>';
	}


	/**
	 *
	 */

	private function readInactiveFields()
	{
		$pFieldnames = $this->getFieldnames(new FieldsCollection(), true);
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

	/** @param bool $isMultiPage */
	public function setIsMultiPage(bool $isMultiPage)
		{ $this->_isMultiPage = $isMultiPage; }

	/** @param string $template */
	public function setTemplate(string $template)
		{ $this->_template = $template; }

	/**
	 * @param int $page
	 * @param string $locale
	 * @return array
	 */
	public function getLocalizedTitlesPerPage(int $page):array
		{
			$titles = [];
			foreach ($this->_titlesPerMultipage as $title) {
				if (isset($title['page']) && (int) $title['page'] === $page) {
					$titles[] = $title;
				}
			}
			//Fallback for new leadgenerator forms
			if (empty($titles)) {
				$titles[] = [
					'page' => 1,
					'locale' => 'native',
					'value' => ''
				];
			}
			return $titles;
		}
	public function setTitlesPerMultipage(array $titlesPerMultipage)
		{ $this->_titlesPerMultipage = $titlesPerMultipage; }
}
