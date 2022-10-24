<?php

/**
 *
 *    Copyright (C) 2017-2019 onOffice GmbH
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

use onOffice\WPlugin\Utility\HtmlIdGenerator;
use DI\ContainerBuilder;
use Exception;
use onOffice\WPlugin\API\APIClientCredentialsException;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use const ONOFFICE_DI_CONFIG_PATH;
use function esc_html;
/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class InputFieldRemoveRenderer
	extends InputFieldRenderer
{


    /**
     *
     * @param string $name
     * @param mixed $value
     *
     */

    public function __construct($name, $value)
    {
        parent::__construct('buttonHandleField', $name, $value);
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
		} catch (APIClientCredentialsException $pEx) {}
		return $pFieldsCollection;
	}
	/**
	 *
	 */

	public function render()
	{
		$pFieldsCollection = $this->buildFieldsCollection();
		$textHtml = !empty($this->getHint()) ? '<p class="hint-text">' . $this->getHint() . '</p>' : "";
		if (is_array($this->getValue())) {
			foreach ($this->getValue() as $key => $label) {
				$inputId = 'label'.$this->getGuiId().'b'.$key;
				$onofficeMultipleSelect = $this->isMultipleSelect($key, $pFieldsCollection) ? '1' : '0';
				echo (is_array($this->getCheckedValues()) && in_array($key, $this->getCheckedValues()) ? 
				'<span name="'.esc_html($this->getName()).'"'
				.'class="inputFieldAddFieldButton dashicons dashicons-remove"'
				.' onoffice-multipleSelectType="'.$onofficeMultipleSelect.'"'
				.'check="2"'
				.' value="'.esc_html($key).'"'
				.'data-onoffice-category="'.esc_attr($this->getLabel()).'"'
				.' id="'.esc_html($inputId).'">'
				.' </span>' :
				'<span name="'.esc_html($this->getName()).'"'
				.'class="inputFieldAddFieldButton dashicons dashicons-insert"'
				.' onoffice-multipleSelectType="'.$onofficeMultipleSelect.'"'
				.' value="'.esc_html($key).'"'
				.'check="1"'
				.'data-onoffice-category="'.esc_attr($this->getLabel()).'"'
				.' id="'.esc_html($inputId).'">'
				.' </span>' );
				echo '<label for="'.esc_html($inputId).'">'.esc_html($label).'</label><br>'
					.$textHtml;
			}
		}
	}
}
