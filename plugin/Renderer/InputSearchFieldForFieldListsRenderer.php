<?php

/**
 *
 *    Copyright (C) 2023 onOffice GmbH
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

use DI\ContainerBuilder;
use onOffice\WPlugin\API\APIClientCredentialsException;
use onOffice\WPlugin\API\APIEmptyResultException;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Utility\__String;
use function esc_html;
/**
 *
 * @url http://www.onoffice.de
 * @copyright 2023, onOffice(R) GmbH
 *
 */

class InputSearchFieldForFieldListsRenderer
	extends InputFieldRenderer
{
	/**
	 *
	 * @param string $name
	 * @param mixed $value
	 *
	 */

	public function __construct(string $name, $value)
	{
		parent::__construct('searchFieldForFieldLists', $name, $value);
	}

	/**
	 *
	 * @param string $key
	 * @param FieldsCollection $pFieldsCollection
	 * @return string
	 *	
	 */

	private function getModule(string $key, FieldsCollection $pFieldsCollection): string
	{
		try {
			return $pFieldsCollection->getFieldByKeyUnsafe($key)->getModule();
		} catch (UnknownFieldException $pEx) {
			return false;
		}
	}
 
 
	 /**
	  *
	  * @return FieldsCollection
	  *
	  * @throws Exception
	  */
 
	private function buildFieldsCollection(): FieldsCollection
	{
		$pDIContainerBuilder = new ContainerBuilder;
		$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pDIContainerBuilder->build();
		$pFieldsCollection = new FieldsCollection();

		/** @var FieldsCollectionBuilderShort $pFieldsCollectionBuilder */
		$pFieldsCollectionBuilder = $pContainer->get(FieldsCollectionBuilderShort::class);
		try {
			$pFieldsCollectionBuilder
				->addFieldsEstateDecoratorReadAddressBackend($pFieldsCollection)
				->addFieldsAddressEstate($pFieldsCollection)
				->addFieldsSearchCriteriaSpecificBackend($pFieldsCollection);
		} catch (APIClientCredentialsException $pCredentialsException) {
		} catch (APIEmptyResultException $pEmptyResultException) {}

		return $pFieldsCollection;
	}

	/**
	 *
	 */

	public function render()
	{
		if (is_array($this->getValue())) {
			$pFieldsCollection = new FieldsCollection();
			if(empty($this->getOoModule())){
				$pFieldsCollection = $this->buildFieldsCollection();
			}
			$label = __('Search the field list for the desired fields.', 'onoffice-for-wp-websites');
			$html = $this->generateInputSearchHtml($label);
			$html .= '<div class="field-lists">';
			$html .= '<div class="line-bottom-content"></div>';
			$html .= '<ul class="search-field-list">';
			foreach ($this->getValue() as $key => $item) {
				$module = empty($this->getOoModule()) ? $this->getModule($key, $pFieldsCollection) : $this->getOoModule();
				if (!__String::getNew($module)->isEmpty()) {
					$this->addAdditionalAttribute('data-onoffice-module', $module);
				}
				$html .= $this->generateItemSearch($key, $item);
			}
		
			$html .= '</ul>';
			$html .= '</div>';
			echo $html;
		}
	}

	/**
	 * @param string $label
	 * @return string
	 */

	private function generateInputSearchHtml(string $label): string
	{
		$html = '<div class="box-search">';
		$html .= '<input type="text" class="input-search">';
		$html .= '<span class="dashicons dashicons-search clear-icon" id="clear-input"></span>';
		$html .= '</div>';
		$html .= '<label>' . $label . '</label>';

		return $html;
	}

	/**
	 * @param string $key
	 * @param array $item
	 * @return string
	 */
	private function generateItemSearch(string $key, array $item): string
	{
		$actionFieldNameClass = 'labelButtonHandleField' . '-' . $key;
		$inputId = 'label' . $this->getGuiId() . 'b' . $key;
		$onofficeSelect = is_array($this->getCheckedValues()) && in_array($key, $this->getCheckedValues()) ?
			'class="dashicons dashicons-remove check-action action-remove" typeField="2"' :
			'class="dashicons dashicons-insert check-action" typeField="1"';
		$fieldItemCustomCss = (is_array($this->getCheckedValues()) && in_array($key, $this->getCheckedValues())) ?
			"opacity: 0.5;" :
			"opacity: 1;";

		$html = $this->generateItemSearchHtml($item, $key, $actionFieldNameClass, $inputId, $onofficeSelect, $fieldItemCustomCss);
	
		return $html;
	}

	/**
	 * @param array $item
	 * @param string $key
	 * @param string $actionFieldNameClass
	 * @param string $inputId
	 * @param string $actionIcons
	 * @param string $fieldItemCustomCss
	 * @return string
	 */

	private function generateItemSearchHtml(array $item, string $key, string $actionFieldNameClass, string $inputId, string $actionIcons, string $fieldItemCustomCss): string
	{
		return '<li class="search-field-item" data-label="' . esc_html($item['label']) . '" data-key="' . esc_html($key) . '" data-content="' . esc_html($item['content']) . '">'
			. '<span class="field-item inputFieldButton ' . $actionFieldNameClass . '"'
			. 'name="' . esc_html($this->getName()) . '"'
			. '' . $this->renderAdditionalAttributes()
			. (isset($item['action']) ? 'data-action-div="actionFor' . esc_html($item['action']) . '"' : '')
			. 'value="' . esc_html($key) . '"'
			. 'data-onoffice-category="' . esc_attr($item['content']) . '"'
			. 'data-onoffice-label="' . esc_attr($item['label']) . '"'
			. 'id="' . esc_html($inputId) . '">'
			. '<span ' . $actionIcons . '></span>'
			. '<div class="field-item-detail" style="' . $fieldItemCustomCss . '">'
			. '<span>' . esc_html($item["content"]) . '</span>'
			. '<h4>' . esc_html($item['label']) . ' (' . esc_html($key) . ')' . '</h4></span>'
			. '</div>'
			. '</li>';
	}
}
