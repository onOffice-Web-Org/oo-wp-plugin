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

use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\DataViewToAPI\DataListViewAddressToAPIParameters;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorReadAddress;
use onOffice\WPlugin\Field\OutputFields;
use onOffice\WPlugin\Fieldnames;
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

	/** @var Container */
	private $_pContainer;

	/**
	 *
	 */
	public function __construct()
	{
		$pFieldCollection = new FieldModuleCollectionDecoratorReadAddress(new FieldsCollection());
		$this->_pFieldnames = new Fieldnames($pFieldCollection); // not injectable!

		$pContainerBuilder = new ContainerBuilder();
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();
	}

	/**
	 * @return DataListViewAddressToAPIParameters
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function getDataListViewAddressToAPIParameters(): DataListViewAddressToAPIParameters
	{
		return $this->_pContainer->get(DataListViewAddressToAPIParameters::class);;
	}

	/**
	 * @return Fieldnames
	 */
	public function getFieldnames(): Fieldnames
	{
		return $this->_pFieldnames;
	}

	/**
	 * @return FieldsCollectionBuilderShort
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function getFieldsCollectionBuilderShort(): FieldsCollectionBuilderShort
	{
		return $this->_pContainer->get(FieldsCollectionBuilderShort::class);
	}

	/**
	 * @return OutputFields
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function getOutputFields(): OutputFields
	{
		return $this->_pContainer->get(OutputFields::class);
	}

	/**
	 * @return SDKWrapper
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function getSDKWrapper(): SDKWrapper
	{
		return $this->_pContainer->get(SDKWrapper::class);
	}

	/**
	 * @param array $fields
	 * @return ViewFieldModifierHandler
	 */
	public function getViewFieldModifierHandler(array $fields): ViewFieldModifierHandler
	{
		return new ViewFieldModifierHandler($fields, onOfficeSDK::MODULE_ADDRESS,
			AddressViewFieldModifierTypes::MODIFIER_TYPE_DEFAULT);
	}
}