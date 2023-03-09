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

use DI\ContainerBuilder;
use onOffice\WPlugin\API\APIClientCredentialsException;
use onOffice\WPlugin\API\APIEmptyResultException;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Utility\__String;
use function esc_html;
/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class InputFieldButtonAddRemoveRenderer
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
	 * @return string
	 */

	public function getModule(string $key, FieldsCollection $pFieldsCollection): string
	{
		return $pFieldsCollection->getFieldByKeyUnsafe($key)->getModule();
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
				->addFieldsSearchCriteria($pFieldsCollection)
				->addFieldsSearchCriteriaSpecificBackend($pFieldsCollection)
				->addFieldsAddressEstateWithRegionValues($pFieldsCollection)
				->addFieldsFormFrontend($pFieldsCollection);
		} catch (APIClientCredentialsException $pCredentialsException) {
		} catch (APIEmptyResultException $pEmptyResultException) {}
		return $pFieldsCollection;
	}

	/**
	 *
	 */

	public function render()
	{
		$textHtml = ! empty( $this->getHint() ) ? '<p class="hint-text">' . $this->getHint() . '</p>' : "";
		$pFieldsCollection = $this->buildFieldsCollection();
		if ( is_array( $this->getValue() ) ) {
			foreach ( $this->getValue() as $key => $label ) {
				$module = empty($this->getOoModule()) ? $this->getModule($key, $pFieldsCollection) : $this->getOoModule();
				if (!__String::getNew($module)->isEmpty()) {
					$this->addAdditionalAttribute('data-onoffice-module', $module);
				}
				$inputId                 = 'label' . $this->getGuiId() . 'b' . $key;
				$actionFieldName         = 'labelButtonHandleField' . '-' . $key;
				$checkDataField          = is_array( $this->getCheckedValues() ) && in_array( $key,
						$this->getCheckedValues() );
				$onofficeSelect          = $checkDataField ? 'class="dashicons dashicons-remove" typeField="2"' : 'class="dashicons dashicons-insert" typeField="1"';
				$handleLabelButtonChange = $checkDataField ? "opacity: 0.5;" : "opacity: 1;";
				echo '<span class="inputFieldButton ' . $actionFieldName . '"'
				     . '' . 'name="' . esc_html( $this->getName() ) . '"'
				     . '' . $this->renderAdditionalAttributes()
				     . 'value="' . esc_html( $key ) . '"'
				     . 'data-onoffice-category="' . esc_attr( $this->getLabel() ) . '"'
				     . 'id="' . esc_html( $inputId ) . '">';
				echo '<span ' . $onofficeSelect . '></span>';
				echo '<label style="margin-left:5px;' . $handleLabelButtonChange . '">' . esc_html( $label ) . '</label></span><br>'
				     . $textHtml;
			}
		}
	}
}
