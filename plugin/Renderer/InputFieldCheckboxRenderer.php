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

use DI\ContainerBuilder;
use Exception;
use onOffice\WPlugin\API\APIClientCredentialsException;
use onOffice\WPlugin\API\APIEmptyResultException;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use const ONOFFICE_DI_CONFIG_PATH;
use function esc_html;


/**
 *
 */

class InputFieldCheckboxRenderer
	extends InputFieldRenderer
{
	/** @var string */
	private $_descriptionTextHTML;


	/**
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param mixed $description
	 *
	 */

	public function __construct($name, $value, $description = '')
	{
		$this->_descriptionTextHTML = $description;
		parent::__construct('checkbox', $name, $value);
	}


	/**
	 *
	 * @param string $key
	 * @param FieldsCollection $pFieldsCollection
	 * @return bool
	 */

	public function isMultipleSelect(string $key, FieldsCollection $pFieldsCollection): bool
	{
		$module = $this->getOoModule();

		try {
			$type = $pFieldsCollection->getFieldByModuleAndName($module, $key)->getType();
			return FieldTypes::isMultipleSelectType($type);
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

	public function buildFieldsCollection(): FieldsCollection
	{
		$pDIContainerBuilder = new ContainerBuilder;
		$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pDIContainerBuilder->build();
		$pFieldsCollection = new FieldsCollection();

		/** @var FieldsCollectionBuilderShort $pFieldsCollectionBuilder */
		$pFieldsCollectionBuilder = $pContainer->get(FieldsCollectionBuilderShort::class);
		try {
			$pFieldsCollectionBuilder
				->addFieldsAddressEstate($pFieldsCollection)
				->addFieldsSearchCriteria($pFieldsCollection);
		} catch (APIClientCredentialsException $pCredentialsException) {
		} catch (APIEmptyResultException $pEmptyResultException) {}
		return $pFieldsCollection;
	}


	/**
	 *
	 * @throws Exception
	 */

	public function render()
	{
		$pFieldsCollection = $this->buildFieldsCollection();
		$textHtml = !empty($this->getHint()) ? '<p class="hint-text">' . $this->getHint() . '</p>' : "";
		if (is_array($this->getValue())) {
			foreach ($this->getValue() as $key => $label) {
				$inputId = 'label'.$this->getGuiId().'b'.$key;
				$onofficeMultipleSelect = $this->isMultipleSelect($key, $pFieldsCollection) ? '1' : '0';

				echo '<input type="'.esc_html($this->getType()).'" name="'.esc_html($this->getName())
					.'" value="'.esc_html($key).'"'
					.(is_array($this->getCheckedValues()) && in_array($key, $this->getCheckedValues()) ? ' checked="checked" ' : '')
					.$this->renderAdditionalAttributes()
					.' onoffice-multipleSelectType="'.$onofficeMultipleSelect.'"'
					.' id="'.esc_html($inputId).'">'
					.'<label for="'.esc_html($inputId).'">'.esc_html($label).'</label><br>'
					.$textHtml;
			}
		} else {
			echo '<input type="'.esc_html($this->getType()).'" name="'.esc_html($this->getName())
				.'" value="'.esc_html($this->getValue()).'"'
				.($this->getValue() == $this->getCheckedValues() ? ' checked="checked" ' : '')
				.$this->renderAdditionalAttributes()
				.' id="'.esc_html($this->getGuiId()).'">'
				.(!empty($this->_descriptionTextHTML) && is_string($this->_descriptionTextHTML) ? '<p class="description">'.$this->_descriptionTextHTML.'</p><br>' : '')
				.$textHtml;
		}
	}
}
