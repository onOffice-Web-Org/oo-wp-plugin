<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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

declare(strict_types=1);

namespace onOffice\WPlugin\Filter;

use Exception;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class DefaultFilterBuilderDetailView
	implements DefaultFilterBuilder
{
	/** @var int */
	private $_addressId = 0;


	/**
	 *
	 * @return array
	 *
	 */

	public function buildFilter(): array
	{
		if ($this->_addressId === 0) {
			throw new Exception('AddressId must not be 0');
		}

		return [
		];
	}

	/** @param int $addressId */
	public function setAddressId(int $addressId)
		{ $this->_addressId = $addressId; }

	/** @return int $addressId */
	public function getAddressId(): int
		{ return $this->_addressId; }
}
