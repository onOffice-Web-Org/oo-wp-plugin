<?php

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

use onOffice\SDK\onOfficeSDK;

/**
 *
 */

class AddressList {
	/** @var array */
	private $_config = null;

	/** @var array */
	private $_adressesById = array();

	/** @var \onOffice\SDK\onOfficeSDK */
	private $_pSDK = null;


	/**
	 *
	 * @param array $config
	 *
	 */

	public function __construct( array $config ) {
		$this->_config = $config;
		$this->_pSDK = new onOfficeSDK();
		$this->_pSDK->setCaches( $config['cache'] );
		$this->_pSDK->setApiVersion( $config['apiversion'] );
	}


	/**
	 *
	 * @param array $addressIds
	 * @param array $fields
	 *
	 */

	public function loadAdressesById( array $addressIds, array $fields ) {
		$handleReadAddresses = $this->_pSDK->callGeneric( onOfficeSDK::ACTION_ID_READ, 'address', array(
				'recordids' => $addressIds,
				'data' => $fields,
			)
		);

		$this->_pSDK->sendRequests( $this->_config['token'], $this->_config['secret'] );
		$responseRaw = $this->_pSDK->getResponseArray( $handleReadAddresses );

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
