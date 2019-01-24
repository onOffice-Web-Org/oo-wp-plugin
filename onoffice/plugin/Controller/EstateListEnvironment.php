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

use onOffice\WPlugin\AddressList;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataViewFilterableFields;
use onOffice\WPlugin\EstateFiles;
use onOffice\WPlugin\EstateUnits;
use onOffice\WPlugin\Field\OutputFields;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Filter\DefaultFilterBuilder;
use onOffice\WPlugin\Filter\GeoSearchBuilder;
use onOffice\WPlugin\SDKWrapper;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

interface EstateListEnvironment
{
	/**
	 *
	 * @return SDKWrapper
	 *
	 */

	public function getSDKWrapper(): SDKWrapper;


	/**
	 *
	 * @return Fieldnames preconfigured with FieldModuleCollectionDecoratorGeoPosition
	 *
	 */

	public function getFieldnames(): Fieldnames;


	/**
	 *
	 * @return AddressList
	 *
	 */

	public function getAddressList(): AddressList;


	/**
	 *
	 * @return GeoSearchBuilder
	 *
	 */

	public function getGeoSearchBuilder(): GeoSearchBuilder;


	/**
	 *
	 * @param GeoSearchBuilder $pGeoSearchBuilder
	 *
	 */

	public function setGeoSearchBuilder(GeoSearchBuilder $pGeoSearchBuilder);


	/**
	 *
	 * @param array $fileTypes
	 * @return EstateFiles
	 *
	 */

	public function getEstateFiles(array $fileTypes): EstateFiles;


	/**
	 *
	 * @return DefaultFilterBuilder
	 *
	 */

	public function getDefaultFilterBuilder(): DefaultFilterBuilder;


	/**
	 *
	 * @param DefaultFilterBuilder $pDefaultFilterBuilder
	 *
	 */

	public function setDefaultFilterBuilder(DefaultFilterBuilder $pDefaultFilterBuilder);


	/**
	 *
	 * @return DataDetailView
	 *
	 */

	public function getDataDetailView(): DataDetailView;


	/**
	 *
	 * @param string $name
	 * @return EstateUnits
	 *
	 */

	public function getEstateUnitsByName(string $name): EstateUnits;


	/**
	 *
	 * @param DataViewFilterableFields $pDataView
	 * @return OutputFields
	 *
	 */

	public function getOutputFields(DataViewFilterableFields $pDataView): OutputFields;


	/**
	 *
	 * @param array $values
	 *
	 */

	public function shuffle(array &$values);
}
