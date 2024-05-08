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

use onOffice\WPlugin\AddressList;
use onOffice\WPlugin\AddressDetail;
use onOffice\WPlugin\Controller\AddressListEnvironment;
use onOffice\WPlugin\DataView\DataAddressDetailViewHandler;

class AddressListFactory
{
	/** @var AddressListEnvironment */
	private $_pEnvironment;

	/** @var DataAddressDetailViewHandler */
	private $_pDataAddressDetailViewHandler;

	/**
	 * @param AddressListEnvironment $pEnvironment
	 */
	public function __construct(DataAddressDetailViewHandler $pDataAddressDetailViewHandler, AddressListEnvironment $pEnvironment)
	{
		$this->_pDataAddressDetailViewHandler = $pDataAddressDetailViewHandler;
		$this->_pEnvironment = $pEnvironment;
	}

	/**
	 * @return AddressList
	 */
	public function create()
	{
		return new AddressList(null, $this->_pEnvironment);
	}


	/**
	 *
	 * @param int $addressId
	 * @return AddressDetail
	 *
	 */

	public function createAddressDetail(int $addressId): AddressDetail
	{
		$pDataAddressDetailView = $this->_pDataAddressDetailViewHandler->getAddressDetailView();

		$pEstateDetail = new AddressDetail($pDataAddressDetailView);
		$pEstateDetail->setAddressId($addressId);
		return $pEstateDetail;
	}
}