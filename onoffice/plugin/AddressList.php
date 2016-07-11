<?php

/**
 *
 *    Copyright (C) 2016 onOffice Software AG
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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

use onOffice\WPlugin\SDKWrapper;
use onOffice\SDK\onOfficeSDK;

/**
 *
 */

class AddressList {
	/** @var array */
	private $_adressesById = array();

	/** @var \onOffice\WPlugin\SDKWrapper */
	private $_pSDKWrapper = null;


	/**
	 *
	 */

	public function __construct() {
		$this->_pSDKWrapper = new SDKWrapper();
	}


	/**
	 *
	 * @param array $addressIds
	 * @param array $fields
	 *
	 */

	public function loadAdressesById( array $addressIds, array $fields ) {
		$handleReadAddresses = $this->_pSDKWrapper->addRequest( onOfficeSDK::ACTION_ID_READ, 'address', array(
				'recordids' => $addressIds,
				'data' => $fields,
			)
		);

		$this->_pSDKWrapper->sendRequests();
		$responseRaw = $this->_pSDKWrapper->getRequestResponse( $handleReadAddresses );

		$this->fillAddressesById( $responseRaw );
	}


	/**
	 *
	 * @param array $responseRaw
	 * @return null
	 *
	 */

	private function fillAddressesById( $responseRaw ) {
		if ( empty( $responseRaw['data']['records'] ) ) {
			return;
		}

		$data = $responseRaw['data']['records'];

		foreach ( $data as $address ) {
			$this->_adressesById[$address['id']] = $address['elements'];
		}
	}


	/**
	 *
	 * @param int $id
	 * @return array
	 *
	 */

	public function getAddressById( $id ) {
		if ( ! array_key_exists( $id, $this->_adressesById ) ) {
			return array();
		}

		return $this->_adressesById[$id];
	}
}
