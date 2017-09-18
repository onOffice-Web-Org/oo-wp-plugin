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
			$elements = $address['elements'];
			$mobilePhoneNumbers = array();
			$privatePhoneNumbers = array();
			$businessPhoneNumbers = array();
			$genericPhoneNumbers = array();
			$privateEmailAddresses = array();
			$businessEmailAddresses = array();
			$genericEmailAddresses = array();

			foreach ($elements as $key => $value) {
				// comes into response if `mobile` was requested
				if (strpos($key, 'mobile') === 0) {
					$mobilePhoneNumbers []= $value;
				}

				// kinds of phone numbers if `phone` was requested
				if (strpos($key, 'phoneprivate') === 0) {
					$privatePhoneNumbers []= $value;
				}

				if (strpos($key, 'phonebusiness') === 0) {
					$businessPhoneNumbers []= $value;
				}

				if (strpos($key, 'phone') === 0) {
					$genericPhoneNumbers []= $value;
				}

				// kinds of email addresses if `email` was requested
				if (strpos($key, 'emailprivate') === 0) {
					$privateEmailAddresses []= $value;
				}

				if (strpos($key, 'emailbusiness') === 0) {
					$businessEmailAddresses []= $value;
				}

				if (strpos($key, 'email') === 0) {
					$genericEmailAddresses []= $value;
				}
			}

			// phone
			if (count($mobilePhoneNumbers) > 0) {
				$elements['mobile'] = $mobilePhoneNumbers;
			}

			if (count($businessPhoneNumbers) > 0) {
				$elements['phonebusiness'] = $businessPhoneNumbers;
			}

			if (count($privatePhoneNumbers) > 0) {
				$elements['phoneprivate'] = $privatePhoneNumbers;
			}

			if (count($genericPhoneNumbers) > 0) {
				$elements['phone'] = $genericPhoneNumbers;
			}

			// email
			if (count($genericEmailAddresses) > 0) {
				$elements['email'] = $genericEmailAddresses;
			}

			if (count($businessEmailAddresses) > 0) {
				$elements['emailbusiness'] = $businessEmailAddresses;
			}

			if (count($privateEmailAddresses) > 0) {
				$elements['emailprivate'] = $privateEmailAddresses;
			}

			$this->_adressesById[$address['id']] = $elements;
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
