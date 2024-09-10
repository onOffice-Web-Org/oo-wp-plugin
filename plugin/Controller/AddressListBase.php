<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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

use onOffice\WPlugin\ArrayContainer;
use onOffice\WPlugin\DataView\DataViewAddress;
use onOffice\WPlugin\Filter\DefaultFilterBuilder;
use onOffice\WPlugin\Filter\GeoSearchBuilder;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2024, onOffice(R) GmbH
 *
 */


interface AddressListBase
{
	/**
	 *
	 * @param DataViewAddress $pDataViewAddress
	 *
	 */

	public function __construct(DataViewAddress $pDataViewAddress);


	/**
	 *
	 * performs the request
	 *
	 */

	public function loadAddresses();


	/**
	 *
	 * @return int[]
	 *
	 */

	public function getAddressIds(): array;


	/** @return DataViewAddress */
	public function getDataViewAddress(): DataViewAddress;
}
