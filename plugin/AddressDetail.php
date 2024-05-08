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

namespace onOffice\WPlugin;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2024, onOffice(R) GmbH
 *
 */

class AddressDetail
	extends AddressList
{
	/** @var int */
	private $_addressId = null;

	/**
	 *
	 * @return int
	 *
	 */

	protected function getNumEstatePages()
	{
		return 1;
	}


	/**
	 *
	 * @param int $id
	 *
	 */

	public function loadSingleAddress($id)
	{
		$this->_addressId = $id;
		$this->loadAddresses(1);
	}

	/**
	 *
	 * @return string
	 *
	 */

	public function getShortCodeForm(): string
	{
		$result = '';

		if ($this->getAddressDataView()->getShortCodeForm() == '') {
			return '';
		}

		$result = $this->getAddressDataView()->getShortCodeForm();

		return  '[oo_address view="' . $result . '"]';

	}

	/**
	 *
	 * @return int
	 *
	 */

	protected function getRecordsPerPage()
	{
		return 1;
	}


	/**
	 *
	 * @return array
	 *
	 */

	protected function addExtraParams(): array
	{
		return [];
	}


	/**
	 *
	 * @return int
	 *
	 */

	public function getAddressId(): int
	{
		return $this->_addressId;
	}


	/**
	 *
	 * @param int $addressId
	 *
	 */

	public function setAddressId(int $addressId)
	{
		$this->_addressId = $addressId;
	}
}
