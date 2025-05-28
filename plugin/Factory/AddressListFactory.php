<?php
/**
 *
 *    Copyright (C) 2020 onOffice GmbH
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

namespace onOffice\WPlugin\Factory;

use onOffice\WPlugin\DataView\DataAddressDetailViewHandler;
use onOffice\WPlugin\AddressList;
use onOffice\WPlugin\AddressDetail;
use onOffice\WPlugin\Controller\AddressListEnvironment;
use onOffice\WPlugin\Filter\DefaultFilterBuilderDetailViewAddress;

class AddressListFactory
{

	/** @var DataAddressDetailViewHandler */
	private $_pDataAddressDetailViewHandler;

	/**
	 * @param AddressListEnvironment $pEnvironment
	 */
	public function __construct(DataAddressDetailViewHandler $pDataAddressDetailViewHandler)
	{
		$this->_pDataAddressDetailViewHandler = $pDataAddressDetailViewHandler;
	}

	/**
	 * @return AddressList
	 */
	public function create($pDataViewAddress)
	{
		return new AddressList($pDataViewAddress);
	}

	/**
	 *
	 * @param int $addressId
	 * @return AddressList
	 *
	 */

	 public function createAddressDetail(int $addressId): AddressList
	 {
			$pDataDetailView = $this->_pDataAddressDetailViewHandler->getAddressDetailView();
			$pDefaultFilterBuilder = new DefaultFilterBuilderDetailViewAddress();
			$pAddressList = new AddressDetail($pDataDetailView);
			$pAddressList->setDefaultFilterBuilder($pDefaultFilterBuilder);
			$pAddressList->setAddressId($addressId);
			return $pAddressList;
	 }
}
