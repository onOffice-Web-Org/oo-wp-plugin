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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\AddressList;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataListViewFactory;
use onOffice\WPlugin\DataView\DataViewFilterableFields;
use onOffice\WPlugin\DataView\UnknownViewException;
use onOffice\WPlugin\EstateFiles;
use onOffice\WPlugin\EstateUnits;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorGeoPositionFrontend;
use onOffice\WPlugin\Field\OutputFields;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Filter\DefaultFilterBuilder;
use onOffice\WPlugin\Filter\GeoSearchBuilder;
use onOffice\WPlugin\Filter\GeoSearchBuilderFromInputVars;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Types\EstateStatusLabel;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierHandler;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class EstateListEnvironmentDefault
	implements EstateListEnvironment
{
	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var Fieldnames */
	private $_pFieldnames = null;

	/** @var AddressList */
	private $_pAddressList = null;

	/** @var GeoSearchBuilderFromInputVars */
	private $_pGeoSearchBuilder = null;

	/** @var DefaultFilterBuilder */
	private $_pDefaultFilterBuilder = null;

	/** @var EstateStatusLabel */
	private $_pEstateStatusLabel = null;


	/**
	 *
	 */

	public function __construct()
	{
		$this->_pSDKWrapper = new SDKWrapper();
		$pFieldsCollection = new FieldModuleCollectionDecoratorGeoPositionFrontend(new FieldsCollection());
		$this->_pFieldnames = new Fieldnames($pFieldsCollection);
		$this->_pAddressList = new AddressList();
		$this->_pGeoSearchBuilder = new GeoSearchBuilderFromInputVars();
		$this->_pEstateStatusLabel = new EstateStatusLabel();
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
	 *
	 * @param array $fileTypes
	 * @return EstateFiles
	 *
	 */

	public function getEstateFiles(array $fileTypes): EstateFiles
	{
		return new EstateFiles($fileTypes);
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
		return $this->_pGeoSearchBuilder;
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
	 * @param GeoSearchBuilder $pGeoSearchBuilder
	 *
	 */

	public function setGeoSearchBuilder(GeoSearchBuilder $pGeoSearchBuilder)
	{
		$this->_pGeoSearchBuilder = $pGeoSearchBuilder;
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
	 *
	 * @return DataDetailView
	 *
	 */

	public function getDataDetailView(): DataDetailView
	{
		$pDataDetailViewHandler = new DataDetailViewHandler();
		return $pDataDetailViewHandler->getDetailView();
	}


	/**
	 *
	 * @param string $name
	 * @return EstateUnits
	 *
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
	 *
	 * @param DataViewFilterableFields $pDataView
	 * @return OutputFields
	 *
	 */

	public function getOutputFields(DataViewFilterableFields $pDataView): OutputFields
	{
		return new OutputFields($pDataView);
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
}
