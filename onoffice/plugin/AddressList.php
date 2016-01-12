<?php

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
