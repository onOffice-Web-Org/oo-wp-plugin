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

	/** @var array */
	private $_estateContacts = array();

	/** @var AddressList */
	private $_pAddressList = array();

	/** @var string */
	private $_configName = null;

	/** @var string */
	private $_view = null;

	/** @var int */
	private $_currentEstatePage = 1;

	/** @var int */
	private $_numEstatePages = null;

	/** @var int */
	private $_estateRecordsPerPage = 20;


	/**
	 *
	 * @param array $config
	 * @param string $configName
	 * @param string $viewName
	 *
	 */

	public function __construct( array $config, $configName, $viewName ) {
		$this->_pOnOfficeSdk = new onOfficeSDK();
		$this->_pOnOfficeSdk->setCaches( $config['cache'] );
		$this->_config = $config;
		$this->_configName = $configName;
		$this->_view = $viewName;
		$this->_pAddressList = new AddressList( $config );
	}


	/**
	 *
	 * @return int
	 *
	 */

	private function getNumEstatePages() {
		if ( empty( $this->_responseArray['data']['meta']['cntabsolute'] ) ) {
			return null;
		}

		$recordNumOverAll = $this->_responseArray['data']['meta']['cntabsolute'];
		$numEstatePages = ceil( $recordNumOverAll / $this->_estateRecordsPerPage );

		return $numEstatePages;
	}


	/**
	 *
	 * @param int $currentPage
	 * @param array $filter
	 *
	 * @return string
	 *
	 */

	public function loadEstates( $currentPage, $filter = array() ) {
		$pSdk = $this->_pOnOfficeSdk;
		$pSdk->setApiVersion( $this->_config['apiversion'] );

		$configByName = $this->_config['estate'][$this->_configName];
		$configByView = $configByName['views'][$this->_view];

		$language = $configByView['language'];
		$data = $configByView['data'];

		$numRecordsPerPage = isset( $configByView['records'] ) ? $configByView['records'] : 20;

		$filter = array_merge( $filter, $configByName['filter'] );
		$offset = ( $currentPage - 1 ) * $numRecordsPerPage;
		$this->_currentEstatePage = $currentPage;

		if ( isset( $configByView['filter'] ) ) {
			$filter = $configByView['filter'];
		}

		$parametersGetEstateList = array(
			'data' => $data,
			'filter' => $filter,
			'language' => $language,
			'listoffset' => $offset,
		);

		$idReadEstate = $pSdk->callGeneric( onOfficeSDK::ACTION_ID_READ, 'estate', $parametersGetEstateList );

		$parametersGetFieldList = array(
			'labels' => 1,
			'language' => $language,
		);

		$idGetFields = $pSdk->callGeneric( onOfficeSDK::ACTION_ID_GET, 'fields', $parametersGetFieldList );
		$pSdk->sendRequests( $this->_config['token'], $this->_config['secret'] );

		$responseArrayEstates = $pSdk->getResponseArray( $idReadEstate );

		$pictureCategories = $configByView['pictures'];

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

		$handleEstateContactPerson = $pSdk->callGeneric( onOfficeSDK::ACTION_ID_GET, 'idsfromrelation', array(
					'parentids' => $estateIds,
					'relationtype' => 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:contactPerson',
				)
			);

		$pSdk->sendRequests( $this->_config['token'], $this->_config['secret'] );

		$responseArrayContactPerson = $pSdk->getResponseArray( $handleEstateContactPerson );
		$this->collectEstateContactPerson( $responseArrayContactPerson );

		$responseArrayEstatePicturesSmall = $pSdk->getResponseArray( $idGetEstatePicturesSmall );
		$this->collectEstatePictures( $responseArrayEstatePicturesSmall, '300x400' );

		$responseArrayEstatePicturesOriginal = $pSdk->getResponseArray( $idGetEstatePicturesOriginal );
		$this->collectEstatePictures( $responseArrayEstatePicturesOriginal, 'o' );

		$fieldList = $responseArrayFieldList['data']['records'];
		$this->createFieldList( $fieldList );

		$this->_responseArray = $responseArrayEstates;
		$this->_numEstatePages = $this->getNumEstatePages();

		$this->resetEstateIterator( $this->_responseArray );
	}


	/**
	 *
	 * @param int $id
	 *
	 */

	public function loadSingleEstate( $id ) {
		$filter = array(
			'Id' => array(
				array('op' => '=', 'val' => $id),
			),
		);

		$this->loadEstates( 1, $filter );
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
	 * @param array $responseArrayContacts
	 * @return null
	 *
	 */

	private function collectEstateContactPerson( $responseArrayContacts ) {
		if (! array_key_exists( 'data', $responseArrayContacts ) ||
			! array_key_exists( 'records', $responseArrayContacts['data'] ) ||
			! array_key_exists( 0, $responseArrayContacts['data']['records'] ) ||
			! array_key_exists( 'elements', $responseArrayContacts['data']['records'][0] ) ) {
			return;
		}

		$records = $responseArrayContacts['data']['records'][0]['elements'];
		$allAddressIds = array();

		foreach ( $records as $estateId => $adressIds ) {
			if ( is_null( $adressIds ) ) {
				continue;
			}

			if ( ! is_array( $adressIds ) ) {
				$adressIds = array($adressIds);
			}

			$this->_estateContacts[$estateId] = $adressIds;
			$allAddressIds = array_merge( $allAddressIds, $adressIds );
		}

		$fields = array();
		if ( isset( $this->_config['estate'][$this->_configName]['views'][$this->_view]['contactdata'] ) ) {
			$fields = $this->_config['estate'][$this->_configName]['views'][$this->_view]['contactdata'];
		}

		if ( count( $fields ) > 0 ) {
			$this->_pAddressList->loadAdressesById( $allAddressIds, $fields );
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
	 * @return ArrayContainerEscape
	 *
	 */

	public function estateIterator() {
		global $numpages, $multipage, $page, $more, $pages;
		if ( ! isset( $this->_responseArray['data']['records'] ) ) {
			return false;
		}

		if ( null !== $this->_numEstatePages ) {
			$multipage = true;

			$page = $this->_currentEstatePage;
			$more = true;
			$pages = array();
			$numpages = $this->_numEstatePages;
		}

		$currentRecord = each( $this->_responseArray['data']['records'] );

		$this->_currentEstate['id'] = $currentRecord['value']['id'];
		$this->_currentEstate['type'] = $currentRecord['value']['type'];

		if ( false !== $currentRecord ) {
			$record = $currentRecord['value']['elements'];
			$pArrayContainer = new ArrayContainerEscape( $record );

			return $pArrayContainer;
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
	 * @return string
	 *
	 */

	public function getEstateLink( $view )
	{
		$currentPageLink = get_permalink();
		$estate = $this->_currentEstate['id'];
		$fullLink = esc_url($currentPageLink.$view.'-'.$estate.'/');
		return $fullLink;
	}


	/**
	 *
	 * @return ArrayContainerEscape
	 *
	 */

	public function getEstatePicturesSmall() {
		$recordId = $this->_currentEstate['id'];
		$size = '300x400';
		if ( array_key_exists( $recordId, $this->_estateFiles ) &&
			array_key_exists( $size, $this->_estateFiles[$recordId] ) ) {
			$estateFiles = $this->_estateFiles[$recordId][$size];

			$pArrayContainer = new ArrayContainerEscape( $estateFiles, Escape::URL );
			return $pArrayContainer;
		}

		return new ArrayContainerEscape( array() );
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
			return esc_url( $this->_estateFiles[$recordId][$size][$number] );
		}

		return null;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getEstateContactIds() {
		$recordId = $this->_currentEstate['id'];
		if ( ! empty( $this->_estateContacts[$recordId] ) ) {
			return $this->_estateContacts[$recordId];
		}

		return array();
	}


	/**
	 *
	 * @return ArrayContainerEscape[]
	 *
	 */

	public function getEstateContacts() {
		$addressIds = $this->getEstateContactIds();
		$result = array();

		foreach ( $addressIds as $addressId ) {
			$currentAddressData = $this->_pAddressList->getAddressById( $addressId );
			$pArrayContainerCurrentAddress = new ArrayContainerEscape( $currentAddressData );
			$result[] = $pArrayContainerCurrentAddress;
		}

		return $result;
	}


	/**
	 *
	 */

	public function resetEstateIterator() {
		reset( $this->_responseArray );
	}


	/**
	 *
	 * @param int $recordsPerPage
	 *
	 */

	public function setEstateRecordsPerPage( $recordsPerPage ) {
		$this->_estateRecordsPerPage = $recordsPerPage;
	}
}