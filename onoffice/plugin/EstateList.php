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


class EstateList {
	/** @var onOffice\SDK\onOfficeSDK */
	private $_pOnOfficeSdk = null;

	/** @var array */
	private $_fieldList = array();

	/** @var array */
	private $_config = array();

	/** @var array */
	private $_estateFiles = array();

	/** @var array */
	private $_responseArray = array();

	/** @var array */
	private $_currentEstate = array();

	/**
	 *
	 * @param array $config
	 *
	 */

	public function __construct( array $config ) {
		$this->_pOnOfficeSdk = new onOfficeSDK();
		$this->_config = $config;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function loadEstates( $data = array(), $filter = array() ) {
		$pSdk = $this->_pOnOfficeSdk;
		$pSdk->setApiVersion( '1.5' );

		$parametersGetEstateList = array(
			'data' => $data,
			'filter' => $filter,
		);

		$idReadEstate = $pSdk->callGeneric( onOfficeSDK::ACTION_ID_READ, 'estate', $parametersGetEstateList );
		$parametersGetFieldList = array(
			'labels' => 1,
			'language' => 'DEU',
		);

		$token = $this->_config['token'];
		$secret = $this->_config['secret'];

		$idGetFields = $pSdk->callGeneric( onOfficeSDK::ACTION_ID_GET, 'fields', $parametersGetFieldList );
		$pSdk->sendRequests( $token, $secret );

		$responseArrayEstates = $pSdk->getResponseArray( $idReadEstate );

		$pictureCategories =  array(
			'Titelbild',
			'Foto',
			'Foto_gross',
			'Grundriss',
			'Lageplan',
			'Stadtplan',
			'Anzeigen',
			'Epass_Skala',
			'Finanzierungsbeispiel'
		);

		$estateIds = $this->collectEstateIds( $responseArrayEstates );
		$idGetEstatePicturesSmall = $pSdk->callGeneric( onOfficeSDK::ACTION_ID_GET, 'estatepictures', array(
					'estateids' => $estateIds,
					'categories' => $pictureCategories,
					'size' => '300x400',
				)
			);
		$idGetEstatePicturesOriginal = $pSdk->callGeneric( onOfficeSDK::ACTION_ID_GET, 'estatepictures', array(
					'estateids' => $estateIds,
					'categories' => $pictureCategories,
				)
			);
		$responseArrayFieldList = $pSdk->getResponseArray( $idGetFields );


		$pSdk->sendRequests( $this->_config['token'], $this->_config['secret'] );
		$responseArrayEstatePicturesSmall = $pSdk->getResponseArray( $idGetEstatePicturesSmall );

		$this->collectEstatePictures( $responseArrayEstatePicturesSmall, '300x400' );

		$responseArrayEstatePicturesOriginal = $pSdk->getResponseArray( $idGetEstatePicturesOriginal );

		$this->collectEstatePictures( $responseArrayEstatePicturesOriginal, 'o' );

		$fieldList = $responseArrayFieldList['data']['records'];
		$this->createFieldList( $fieldList );

		$this->_responseArray = $responseArrayEstates;
		$this->resetEstateIterator( $this->_responseArray );
	}


	/**
	 *
	 * @param array $estateResponseArray
	 * @return array
	 *
	 */

	private function collectEstateIds( $estateResponseArray ) {
		if ( ! array_key_exists( 'data', $estateResponseArray ) ) {
			return array();
		}

		$estateProperties = $estateResponseArray['data']['records'];

		$estateIds = array();

		foreach ( $estateProperties as $estate ) {
			if ( ! array_key_exists( 'id', $estate ) ) {
				return array();
			}

			$estateIds[] = $estate['id'];
		}

		return $estateIds;
	}


	/**
	 *
	 * @param array $responseArrayEstatePictures
	 * @return null
	 *
	 */

	private function collectEstatePictures( $responseArrayEstatePictures, $size ) {
		if (! array_key_exists( 'data', $responseArrayEstatePictures ) ||
			! array_key_exists( 'records', $responseArrayEstatePictures['data'] ) ) {
			return;
		}

		$records = $responseArrayEstatePictures['data']['records'];

		foreach ( $records as $properties ) {
			$this->_estateFiles[$properties['elements']['estateid']][$size][] = $properties['elements']['url'];
		}
	}


	/**
	 *
	 * @param array $fieldResult
	 * @return null
	 *
	 */

	private function createFieldList( $fieldResult ) {
		if ( count( $fieldResult ) == 0 ) {
			return;
		}

		foreach ( $fieldResult as $moduleProperties ) {
			if ( ! array_key_exists( 'elements', $moduleProperties ) ) {
				continue;
			}

			foreach ( $moduleProperties['elements'] as $fieldName => $fieldProperties ) {
				if ( 'label' == $fieldName ) {
					continue;
				}

				$this->_fieldList[$moduleProperties['id']][$fieldName] = $fieldProperties;
			}
		}
	}


	/**
	 *
	 * @return array|boolean
	 *
	 */

	public function estateIterator() {
		if ( ! array_key_exists( 'data', $this->_responseArray ) &&
				! array_key_exists( 'records', $this->_responseArray['data'] ) ) {
			return false;
		}

		$currentRecord = each( $this->_responseArray['data']['records'] );

		$this->_currentEstate['id'] = $currentRecord['value']['id'];
		$this->_currentEstate['type'] = $currentRecord['value']['type'];

		if ( false !== $currentRecord ) {
			return $currentRecord['value']['elements'];
		}

		return false;
	}


	/**
	 *
	 * @param string $field
	 * @return string
	 *
	 */

	public function getFieldLabel( $field ) {
		$recordType = $this->_currentEstate['type'];
		$fieldNewName = $field;

		if ( array_key_exists( $recordType, $this->_fieldList ) &&
			array_key_exists( $field, $this->_fieldList[$recordType] ) ) {
			$fieldNewName = $this->_fieldList[$recordType][$field]['label'];
		}

		return $fieldNewName;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getEstatePicturesSmall( ) {
		$recordId = $this->_currentEstate['id'];
		$size = '300x400';
		if ( array_key_exists( $recordId, $this->_estateFiles ) &&
			array_key_exists( $size, $this->_estateFiles[$recordId] ) ) {
			return $this->_estateFiles[$recordId][$size];
		}

		return array();
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getEstatePictureBig( $number ) {
		$recordId = $this->_currentEstate['id'];
		$size = 'o';
		if ( ! empty( $this->_estateFiles[$recordId][$size][$number] ) ) {
			return $this->_estateFiles[$recordId][$size][$number];
		}

		return null;
	}


	/**
	 *
	 */

	public function resetEstateIterator() {
		reset( $this->_responseArray );
	}
}