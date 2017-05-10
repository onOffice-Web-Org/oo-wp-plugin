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
use onOffice\WPlugin\Fieldnames;
use onOffice\SDK\onOfficeSDK;

/**
 *
 */


class EstateList {
	/** @var onOffice\WPlugin\SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var \onOffice\WPlugin\Fieldnames */
	private $_pFieldnames = null;

	/** @var array */
	private $_configName = null;

	/** @var array */
	private $_configByName = array();

	/** @var array */
	private $_responseArray = array();

	/** @var EstateImages */
	private $_pEstateImages = null;

	/** @var EstateUnits */
	private $_pEstateUnits = null;

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

	/** @var int */
	private $_handleEstateContactPerson = null;

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
		$this->_pFieldnames = Fieldnames::getInstance();
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
		$language = $this->getLanguage();
		$this->_pFieldnames->loadLanguage( $language );

		$parametersGetEstateList = $this->getEstateParameters( $configByView, $currentPage, $filter );

		$handleReadEstate = $this->_pSDKWrapper->addRequest( onOfficeSDK::ACTION_ID_READ, 'estate', $parametersGetEstateList );
		$this->_pSDKWrapper->sendRequests();

		$responseArrayEstates = $this->_pSDKWrapper->getRequestResponse( $handleReadEstate );
		$pictureCategories = $configByView['pictures'];
		$this->_pEstateImages = new EstateImages( $pictureCategories );

		$estateIds = $this->collectEstateIds( $responseArrayEstates );

		if ( count( $estateIds ) > 0 ) {
			$pSDKWrapper = new SDKWrapper();
			add_action('oo_beforeEstateRelations', array($this, 'registerContactPersonCall'), 10, 2);
			add_action('oo_afterEstateRelations', array($this, 'extractEstateContactPerson'), 10, 1);

			do_action('oo_beforeEstateRelations', $pSDKWrapper, $estateIds);

			$pSDKWrapper->sendRequests();

			do_action('oo_afterEstateRelations', $pSDKWrapper);
		}

		$this->_responseArray = $responseArrayEstates;

		$this->_numEstatePages = $this->getNumEstatePages();

		$this->resetEstateIterator();
	}


	/**
	 *
	 * @param array $estateIds
	 *
	 */

	public function registerContactPersonCall( SDKWrapper $pSDKWrapper, array $estateIds) {
		$this->_handleEstateContactPerson = $pSDKWrapper->addRequest( onOfficeSDK::ACTION_ID_GET, 'idsfromrelation', array(
				'parentids' => $estateIds,
				'relationtype' => onOfficeSDK::RELATION_TYPE_CONTACT_BROKER,
			)
		);
	}


	/**
	 *
	 * @param SDKWrapper $pSDKWrapper
	 *
	 */

	public function extractEstateContactPerson(SDKWrapper $pSDKWrapper) {
		$responseArrayContactPerson = $pSDKWrapper->getRequestResponse( $this->_handleEstateContactPerson );
		$this->collectEstateContactPerson( $responseArrayContactPerson );
	}


	/**
	 *
	 * @param array $configByView
	 * @return array
	 *
	 */

	private function getEstateParameters( array $configByView, $currentPage, $filter ) {
		$language = $this->getLanguage();
		$data = $configByView['data'];

		$numRecordsPerPage = isset( $configByView['records'] ) ? $configByView['records'] : 20;
		$filter = array_merge( $filter, $this->_configByName['filter'] );
		$offset = ( $currentPage - 1 ) * $numRecordsPerPage;
		$this->_currentEstatePage = $currentPage;

		if ( isset( $configByView['filter'] ) ) {
			$filter = $configByView['filter'];
		}

		$requestParams = array(
			'data' => $data,
			'filter' => $filter,
			'estatelanguage' => $language,
			'outputlanguage' => $language,
			'listlimit' => $numRecordsPerPage,
			'listoffset' => $offset,
			'formatoutput' => true,
			'addMainLangId' => true,
		);

		if ( array_key_exists( 'sortby', $configByView) ) {
			$requestParams['sortby'] = $configByView['sortby'];
		}

		if ( array_key_exists( 'sortorder', $configByView) ) {
			$requestParams['sortorder'] = $configByView['sortorder'];
		}

		return $requestParams;
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
			$allAddressIds = array_unique( array_merge( $allAddressIds, $adressIds ) );
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

		if (array_key_exists('mainLangId', $record)) {
			unset($record['mainLangId']);
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
		$this->_currentEstate['mainId'] = $this->_currentEstate['id'];
		$recordElements = $currentRecord['value']['elements'];

		if (is_array($recordElements) && array_key_exists('mainLangId', $recordElements) &&
			$recordElements['mainLangId'] != null) {
			$mainLangId = $recordElements['mainLangId'];
			$this->_currentEstate['mainId'] = $mainLangId;
		}

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
	 * @return type
	 *
	 */

	public function getEstateOverallCount() {
		return $this->_responseArray['data']['meta']['cntabsolute'];
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getFieldLabel( $field ) {
		$recordType = $this->_currentEstate['type'];
		$language = $this->getLanguage();
		$fieldNewName = $this->_pFieldnames->getFieldLabel($field, $recordType, $language );

		return $fieldNewName;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getEstateLink( $view )
	{
		$targetView = $this->_configByName['views'][$view];

		if ( is_string( $targetView ) && strpos( $targetView, ':' ) !== false) {
			$foreignView = explode( ':', $targetView );

			$foreignViewConfigName = $foreignView[0];
			$foreignViewConfigView = $foreignView[1];

			if (strpos($foreignViewConfigName, '@') === 0) {
				$foreignViewConfigName = substr($foreignViewConfigName, 1);
			}
		} else {
			$foreignViewConfigName = $this->_configName;
			$foreignViewConfigView = $view;
		}

		$estate = $this->_currentEstate['mainId'];

		// _default
		if ( substr( $foreignViewConfigName, 0, 1) == '_' ) {
			$fullLink = site_url().'/'.$foreignViewConfigView.'/'.$estate.'/';
		} else {
			$estateConfig = ConfigWrapper::getInstance()->getConfigByKey( 'estate' );
			$detailpageid = $estateConfig[$foreignViewConfigName]['views'][$foreignViewConfigView]['pageid'];
			$listpageid = wp_get_post_parent_id( $detailpageid );
			$link = get_permalink( $listpageid );

			$fullLink = $link.$foreignViewConfigView.'-'.$estate.'/';
		}

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
	 * @param int $imageId
	 * @return string
	 *
	 */

	public function getEstatePictureTitle( $imageId ) {
		$currentEstate = $this->_currentEstate['id'];
		return $this->_pEstateImages->getEstatePuctureTitle($imageId, $currentEstate);
	}


	/**
	 *
	 * @param int $imageId
	 * @return string
	 *
	 */

	public function getEstatePictureText( $imageId ) {
		$currentEstate = $this->_currentEstate['id'];
		return $this->_pEstateImages->getEstatePuctureText($imageId, $currentEstate);
	}


	/**
	 *
	 * @param int $imageId
	 * @return array
	 *
	 */

	public function getEstatePictureValues( $imageId ) {
		$currentEstate = $this->_currentEstate['id'];
		return $this->_pEstateImages->getEstatePuctureValues($imageId, $currentEstate);
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
	 * @return int
	 *
	 */

	public function getCurrentEstateId() {
		$recordId = $this->_currentEstate['id'];

		return $recordId;
	}


	/**
	 *
	 * @param int $estateId
	 * @param string $configName
	 * @param string $viewName
	 * @return string
	 *
	 */

	public function getEstateUnits( $estateId, $configName, $viewName ) {
		$this->_pEstateUnits = new EstateUnits( array( $estateId ), $configName, $viewName );
		$unitCount = $this->_pEstateUnits->getUnitCount( $estateId );
		$htmlOutput = '';

		if ( $unitCount > 0 ) {
			$htmlOutput = $this->_pEstateUnits->generateHtmlOutput( $estateId );
		}

		return $htmlOutput;
	}


	/**
	 *
	 * @param string $templateType
	 * @return string
	 *
	 */

	public function getDocument( $templateType ) {
		$language = $this->getLanguage();

		$estateId = $this->_currentEstate['mainId'];
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


	/**
	 *
	 * @return string
	 *
	 */

	public function getLanguage() {
		$configByView = $this->_configByName['views'][$this->_view];
		$language = $configByView['language'];

		if ($configByView['language'] == 'auto')
		{
			$config = ConfigWrapper::getInstance()->getConfig();
			$languageMapping = $config['localemap'];
			$currentLocale = get_locale();

			if (array_key_exists($currentLocale, $languageMapping))
			{
				$language = $languageMapping[$currentLocale];
			}
			else
			{
				$language = $languageMapping['fallback'];
			}
		}
		return $language;
	}
}
