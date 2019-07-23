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

declare (strict_types=1);

namespace onOffice\WPlugin\Controller;

use DI\ContainerBuilder;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\DataViewToAPI\DataListViewAddressToAPIParameters;
use onOffice\WPlugin\DataView\DataListViewAddress;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\CompoundFieldsFilter;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorReadAddress;
use onOffice\WPlugin\Field\OutputFields;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Filter\FilterBuilderInputVariables;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\ViewFieldModifier\AddressViewFieldModifierTypes;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierHandler;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 *
 * Default Environment for AddressList
 *
 */

class AddressListEnvironmentDefault
	implements AddressListEnvironment
{
	/** @var Fieldnames */
	private $_pFieldnames = null;

	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var CompoundFieldsFilter */
	private $_pDataListViewAddressToAPIParameters = null;

	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsBuilderShort = null;


	/**
	 *
	 */

	public function __construct()
	{
		$pFieldCollection = new FieldModuleCollectionDecoratorReadAddress(new FieldsCollection());
		$this->_pFieldnames = new Fieldnames($pFieldCollection);
		$this->_pSDKWrapper = new SDKWrapper();

		$pContainerBuilder = new ContainerBuilder();
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();
		$this->_pDataListViewAddressToAPIParameters = $pContainer->get(DataListViewAddressToAPIParameters::class);
		$this->_pFieldsBuilderShort = $pContainer->get(FieldsCollectionBuilderShort::class);
	}


	/**
	 *
	 * @param FilterBuilderInputVariables $pFilterBuilder
	 * @return DataListViewAddressToAPIParameters
	 *
	 */

	public function getDataListViewAddressToAPIParameters(): DataListViewAddressToAPIParameters
	{
		return $this->_pDataListViewAddressToAPIParameters;
	}


	/**
	 *
	 * @return Fieldnames
	 *
	 */

	public function getFieldnames(): Fieldnames
	{
		return $this->_pFieldnames;
	}


	/**
	 *
	 * @return FieldsCollectionBuilderShort
	 *
	 */

	public function getFieldsCollectionBuilderShort(): FieldsCollectionBuilderShort
	{
		return $this->_pFieldsBuilderShort;
	}


	/**
	 *
	 * @param DataListViewAddress $pListView
	 * @return OutputFields
	 *
	 */

	public function getOutputFields(DataListViewAddress $pListView): OutputFields
	{
		return new OutputFields(
				$pListView,
				new GeoPositionFieldHandlerEmpty(),
				new CompoundFieldsFilter());
	}


	/**
	 *
	 * @return SDKWrapper
	 *
	 */

	public function getSDKWrapper(): SDKWrapper
	{
		return $this->_pSDKWrapper;
	}


	/**
	 *
	 * @param array $fields
	 * @return ViewFieldModifierHandler
	 *
	 */

	public function getViewFieldModifierHandler(array $fields): ViewFieldModifierHandler
	{
		return new ViewFieldModifierHandler($fields, onOfficeSDK::MODULE_ADDRESS,
			AddressViewFieldModifierTypes::MODIFIER_TYPE_DEFAULT);
	}
}