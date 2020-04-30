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

use onOffice\WPlugin\API\DataViewToAPI\DataListViewAddressToAPIParameters;
use onOffice\WPlugin\DataView\DataListViewAddress;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\OutputFields;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Filter\FilterBuilderInputVariables;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierHandler;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 *
 * Environment for AddressList
 *
 */

interface AddressListEnvironment
{
	/**
	 * @return SDKWrapper
	 */
	public function getSDKWrapper(): SDKWrapper;

	/**
	 * @return Fieldnames
	 */
	public function getFieldnames(): Fieldnames;

	/**
	 * @return OutputFields
	 */
	public function getOutputFields(): OutputFields;

	/**
	 * @return DataListViewAddressToAPIParameters
	 */
	public function getDataListViewAddressToAPIParameters(): DataListViewAddressToAPIParameters;

	/**
	 * @param array $fields
	 * @return ViewFieldModifierHandler
	 */
	public function getViewFieldModifierHandler(array $fields): ViewFieldModifierHandler;

	/**
	 * @return FieldsCollectionBuilderShort
	 */
	public function getFieldsCollectionBuilderShort(): FieldsCollectionBuilderShort;
}
