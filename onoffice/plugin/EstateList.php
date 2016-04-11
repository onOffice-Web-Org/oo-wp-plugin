<?php

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Fieldnames;
use onOffice\SDK\onOfficeSDK;

/**
 *
 */


class EstateList {
	/** @var onOffice\WPlugin\SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var onOffice\WPlugin\Fieldnames */
	private $_pFieldnames = null;

	/** @var array */
	private $_configName = null;

	/** @var array */
	private $_configByName = array();

	/** @var array */
	private $_responseArray = array();

	/** @var EstateImages */
	private $_pEstateImages = null;

	/** @var array */
	private $_currentEstate = array();

	/** @var array */
	private $_estateContacts = array();

	/** @var AddressList */
	private $_pAddressList = array();

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

	public function __construct( $configName, $viewName ) {
		$this->_pSDKWrapper = new SDKWrapper();
		$estateConfig = ConfigWrapper::getInstance()->getConfigByKey( 'estate' );
		$this->_configByName = $estateConfig[$configName];
		$this->_configName = $configName;
		$this->_view = $viewName;
		$this->_pFieldnames = new Fieldnames();
		$this->_pAddressList = new AddressList();
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
		$pSDKWrapper = $this->_pSDKWrapper;

		$configByView = $this->_configByName['views'][$this->_view];
		$language = $configByView['language'];
		$data = $configByView['data'];
		$numRecordsPerPage = isset( $configByView['records'] ) ? $configByView['records'] : 20;
		$filter = array_merge( $filter, $this->_configByName['filter'] );
		$offset = ( $currentPage - 1 ) * $numRecordsPerPage;
		$this->_currentEstatePage = $currentPage;

		if ( isset( $configByView['filter'] ) ) {
			$filter = $configByView['filter'];
		}

		$parametersGetEstateList = array(
			'data' => $data,
			'filter' => $filter,
			'estatelanguage' => $language,
			'outputlanguage' => $language,
			'listoffset' => $offset,
			'formatoutput' => 1,
		);

		$handleReadEstate = $pSDKWrapper->addRequest( onOfficeSDK::ACTION_ID_READ, 'estate', $parametersGetEstateList );
		$pSDKWrapper->sendRequests();

		$responseArrayEstates = $pSDKWrapper->getRequestResponse( $handleReadEstate );
		$pictureCategories = $configByView['pictures'];

		$estateIds = $this->collectEstateIds( $responseArrayEstates );

		if ( count( $estateIds ) > 0 ) {

			$this->_pEstateImages = new EstateImages( $estateIds, $pictureCategories );

			$handleEstateContactPerson = $pSDKWrapper->addRequest( onOfficeSDK::ACTION_ID_GET, 'idsfromrelation', array(
						'parentids' => $estateIds,
						'relationtype' => onOfficeSDK::RELATION_TYPE_CONTACT_BROKER,
					)
				);

			$pSDKWrapper->sendRequests();

			$responseArrayContactPerson = $pSDKWrapper->getRequestResponse( $handleEstateContactPerson );
			$this->collectEstateContactPerson( $responseArrayContactPerson );

			$this->_responseArray = $responseArrayEstates;
		} else {
			$this->_responseArray['data']['records'] = array();
		}

		$this->_numEstatePages = $this->getNumEstatePages();

		$this->resetEstateIterator();
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
		if ( ! isset( $estateResponseArray['data']['records'] ) ) {
			return array();
		}

		$estateProperties = $estateResponseArray['data']['records'];

		$estateIds = array();

		foreach ( $estateProperties as $estate ) {
			if ( ! array_key_exists( 'id', $estate ) ) {
				continue;
			}

			$estateIds[] = $estate['id'];
		}

		return $estateIds;
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
		if ( isset( $this->_configByName['views'][$this->_view]['contactdata'] ) ) {
			$fields = $this->_configByName['views'][$this->_view]['contactdata'];
		}

		if ( count( $fields ) > 0 ) {
			$this->_pAddressList->loadAdressesById( $allAddressIds, $fields );
		}
	}


	/**
	 *
	 * @return type
	 *
	 */

	private function getCensoredAddressData( $record ) {
		if ( isset( $this->_configByName['views'][$this->_view] ) ) {
			$requestedFields = $this->_configByName['views'][$this->_view]['data'];

			if ( in_array( 'virtualAddress', $requestedFields ) &&
				1 == $record['virtualAddress'] ) {
				if ( in_array( 'virtualStreet', $requestedFields ) ) {
					$record['strasse'] = $record['virtualStreet'];
				}
				if ( in_array( 'virtualHouseNumber', $requestedFields ) ) {
					$record['hausnummer'] = $record['virtualHouseNumber'];
				}
				if ( in_array( 'virtualLongitude', $requestedFields ) ) {
					$record['laengengrad'] = $record['virtualLongitude'];
				}
				if ( in_array( 'virtualLatitude', $requestedFields ) ) {
					$record['breitengrad'] = $record['virtualLatitude'];
				}
			}
		}

		return $record;
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
			$recordCensored = $this->getCensoredAddressData( $record );
			$pArrayContainer = new ArrayContainerEscape( $recordCensored );

			return $pArrayContainer;
		}

		return false;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getFieldLabel( $field ) {
		$recordType = $this->_currentEstate['type'];
		$configByView = $this->_configByName['views'][$this->_view];

		$this->_pFieldnames->loadLanguageIfNotCached($configByView['language']);
		$fieldNewName = $this->_pFieldnames->getFieldLabel(
			$field, $recordType, $configByView['language'] );

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
		$fullLink = esc_url( $currentPageLink.$view.'-'.$estate.'/' );
		return $fullLink;
	}


	/**
	 *
	 * @return ArrayContainerEscape
	 *
	 */

	public function getEstatePictures( array $types = null ) {
		$estateId = $this->_currentEstate['id'];
		$estateFiles = array();

		$estateImages = $this->_pEstateImages->getEstatePictures( $estateId );

		foreach ( $estateImages as $image ) {
			if ( null !== $types && ! in_array( $image['imagetype'], $types, true ) ) {
				continue;
			}

			$estateFiles []= $image['id'];
		}
		return $estateFiles;
	}


	/**
	 *
	 * @param int $imageId
	 * @param array $options
	 * @return string
	 *
	 */

	public function getEstatePictureUrl( $imageId, array $options = null ) {
		$currentEstate = $this->_currentEstate['id'];
		return $this->_pEstateImages->getEstatePictureUrl( $imageId, $currentEstate, $options );
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
	 * @param string $templateType
	 * @return string
	 *
	 */

	public function getDocument( $templateType ) {
		$configByView = $this->_configByName['views'][$this->_view];
		$language = $configByView['language'];

		$estateId = $this->_currentEstate['id'];
		$documentId = array_search( $templateType, $this->_configByName['documents'] );

		$queryVars = array(
			'documentid' => $documentId,
			'estateid' => $estateId,
			'language' => $language,
			'configindex' => $this->_configName,
		);

		$documentlink = plugin_dir_url( __DIR__ ).'document.php?'. http_build_query( $queryVars );
		return esc_url( $documentlink );
	}


	/**
	 *
	 */

	public function resetEstateIterator() {
		reset( $this->_responseArray['data']['records'] );
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