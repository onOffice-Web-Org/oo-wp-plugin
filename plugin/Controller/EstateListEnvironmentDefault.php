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

namespace onOffice\WPlugin\Controller;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\AddressList;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataListViewFactory;
use onOffice\WPlugin\DataView\UnknownViewException;
use onOffice\WPlugin\EstateFiles;
use onOffice\WPlugin\EstateUnits;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorGeoPositionFrontend;
use onOffice\WPlugin\Field\OutputFields;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Filter\DefaultFilterBuilder;
use onOffice\WPlugin\Filter\GeoSearchBuilder;
use onOffice\WPlugin\Filter\GeoSearchBuilderEmpty;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Types\EstateStatusLabel;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierHandler;

/**
 *
 */
class EstateListEnvironmentDefault
	implements EstateListEnvironment
{
	/** @var Fieldnames */
	private $_pFieldnames;

	/** @var AddressList */
	private $_pAddressList;

	/** @var DefaultFilterBuilder */
	private $_pDefaultFilterBuilder;

	/** @var EstateStatusLabel */
	private $_pEstateStatusLabel;

	/** @var Container */
	private $_pContainer;

	/**
	 * @param Container $pContainer
	 */
	public function __construct(Container $pContainer)
	{
		$pFieldsCollection = new FieldModuleCollectionDecoratorGeoPositionFrontend(new FieldsCollection());
		$this->_pFieldnames = new Fieldnames($pFieldsCollection);
		$this->_pAddressList = new AddressList();
		$this->_pEstateStatusLabel = new EstateStatusLabel();
		$this->_pContainer = $pContainer;
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
	 *
	 * @return AddressList
	 *
	 */

	public function getAddressList(): AddressList
	{
		return $this->_pAddressList;
	}

	/**
	 * @return EstateFiles
	 */

	public function getEstateFiles(): EstateFiles
	{
		return new EstateFiles;
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
	 * @return GeoSearchBuilder
	 *
	 */

	public function getGeoSearchBuilder(): GeoSearchBuilder
	{
		return new GeoSearchBuilderEmpty();
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
	 *
	 * @return DefaultFilterBuilder
	 * @throws UnknownViewException
	 *
	 */

	public function getDefaultFilterBuilder(): DefaultFilterBuilder
	{
		if ($this->_pDefaultFilterBuilder === null) {
			throw new UnknownViewException;
		}
		return $this->_pDefaultFilterBuilder;
	}


	/**
	 *
	 * @param DefaultFilterBuilder $pDefaultFilterBuilder
	 *
	 */

	public function setDefaultFilterBuilder(DefaultFilterBuilder $pDefaultFilterBuilder)
	{
		$this->_pDefaultFilterBuilder = $pDefaultFilterBuilder;
	}

	/**
	 * @return DataDetailViewHandler
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function getDataDetailViewHandler(): DataDetailViewHandler
	{
		return $this->_pContainer->get(DataDetailViewHandler::class);
	}

	/**
	 *
	 * @param string $name
	 * @return EstateUnits
	 *
	 * @throws UnknownViewException
	 */

	public function getEstateUnitsByName(string $name): EstateUnits
	{
		// @codeCoverageIgnoreStart
		$pDataListViewFactory = new DataListViewFactory();
		$pDataListView = $pDataListViewFactory->getListViewByName
			($name, DataListView::LISTVIEW_TYPE_UNITS);

		$pEstateUnits = new EstateUnits($pDataListView);

		return $pEstateUnits;
		// @codeCoverageIgnoreEnd
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
	 *
	 * @param array $values
	 *
	 */

	public function shuffle(array &$values)
	{
		shuffle($values);
	}


	/**
	 *
	 * @param array $fieldList
	 * @param string $modifier
	 * @return ViewFieldModifierHandler
	 *
	 */

	public function getViewFieldModifierHandler(array $fieldList, string $modifier): ViewFieldModifierHandler
	{
		return new ViewFieldModifierHandler($fieldList, onOfficeSDK::MODULE_ESTATE, $modifier);
	}


	/**
	 *
	 * @return EstateStatusLabel
	 *
	 */

	public function getEstateStatusLabel(): EstateStatusLabel
	{
		return $this->_pEstateStatusLabel;
	}

	/**
	 * @return Container
	 */
	public function getContainer(): Container
	{
		return $this->_pContainer;
	}
}
