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

namespace onOffice\WPlugin;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Filter\DefaultFilterBuilder;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */

class EstateList {
	/** @var onOffice\WPlugin\SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var \onOffice\WPlugin\Fieldnames */
	private $_pFieldnames = null;

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

	/** @var int */
	private $_currentEstatePage = 1;

	/** @var int */
	private $_numEstatePages = null;

	/** @var int */
	private $_handleEstateContactPerson = null;

	/** @var DataView */
	private $_pDataView = null;

	/** @var Filter\DefaultFilterBuilder */
	private $_pDefaultFilterBuilder = null;

	/** @var string */
	private $_unitsViewName = null;

	/** @var bool */
	private $_shuffleResult = false;

	/**
	 *
	 * @param \onOffice\WPlugin\DataView\DataView $pDataView
	 * @param DefaultFilterBuilder $pDefaultFilterBuilder
	 *
	 */

	public function __construct(DataView\DataView $pDataView) {
		$this->_pSDKWrapper = new SDKWrapper();
		$this->_pFieldnames = new Fieldnames();
		$this->_pAddressList = new AddressList();
		$this->_pDataView = $pDataView;
	}


	/**
	 *
	 * @return int
	 *
	 */

	protected function getNumEstatePages() {
		if ( empty( $this->_responseArray['data']['meta']['cntabsolute'] ) ) {
			return null;
		}

		$recordNumOverAll = $this->_responseArray['data']['meta']['cntabsolute'];
		$numEstatePages = ceil( $recordNumOverAll / $this->_pDataView->getRecordsPerPage());

		return $numEstatePages;
	}


	/**
	 *
	 * @return int
	 *
	 */

	protected function getRecordsPerPage() {
		return $this->_pDataView->getRecordsPerPage();
	}


	/**
	 *
	 * @param int $currentPage
	 *
	 */

	public function loadEstates($currentPage = 1)
	{
		$pSDKWrapper = $this->_pSDKWrapper;

		$this->_pFieldnames->loadLanguage();

		$parametersGetEstateList = $this->getEstateParameters( $currentPage );

		$handleReadEstate = $this->_pSDKWrapper->addRequest(
			onOfficeSDK::ACTION_ID_READ, 'estate', $parametersGetEstateList );
		$this->_pSDKWrapper->sendRequests();

		$responseArrayEstates = $this->_pSDKWrapper->getRequestResponse( $handleReadEstate );
		$pictureCategories = $this->_pDataView->getPictureTypes();
		$this->_pEstateImages = new EstateImages( $pictureCategories );

		$estateIds = $this->getEstateIdToForeignMapping( $responseArrayEstates );

		if ( count( $estateIds ) > 0 ) {
			$pSDKWrapper = new SDKWrapper();
			add_action('oo_beforeEstateRelations', array($this, 'registerContactPersonCall'), 10, 2);
			add_action('oo_afterEstateRelations', array($this, 'extractEstateContactPerson'), 10, 2);

			do_action('oo_beforeEstateRelations', $pSDKWrapper, $estateIds);

			$pSDKWrapper->sendRequests();

			do_action('oo_afterEstateRelations', $pSDKWrapper, $estateIds);
		}

		$this->_responseArray = $responseArrayEstates;

		if ( isset( $this->_responseArray['data']['records'] ) && $this->_shuffleResult ) {
			shuffle($this->_responseArray['data']['records']);
		}

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
				'parentids' => array_keys($estateIds),
				'relationtype' => onOfficeSDK::RELATION_TYPE_CONTACT_BROKER,
			)
		);
	}


	/**
	 *
	 * @param SDKWrapper $pSDKWrapper
	 * @param array $estateIds
	 *
	 */

	public function extractEstateContactPerson(SDKWrapper $pSDKWrapper, array $estateIds) {
		$responseArrayContactPerson = $pSDKWrapper->getRequestResponse( $this->_handleEstateContactPerson );
		$this->collectEstateContactPerson( $responseArrayContactPerson, $estateIds );
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getEstateParameters($currentPage) {
		$language = Language::getDefault();
		$pListView = $this->_pDataView;
		$pFilterBuilder = $this->_pDefaultFilterBuilder;

		if ($pFilterBuilder === null) {
			throw new \Exception('$_pDefaultFilterBuilder must not be null');
		}

		$filter = $this->_pDefaultFilterBuilder->buildFilter();

		$numRecordsPerPage = $this->getRecordsPerPage();
		$offset = ( $currentPage - 1 ) * $numRecordsPerPage;
		$this->_currentEstatePage = $currentPage;

		$requestParams = array(
			'data' => $pListView->getFields(),
			'filter' => $filter,
			'estatelanguage' => $language,
			'outputlanguage' => $language,
			'listlimit' => $numRecordsPerPage,
			'listoffset' => $offset,
			'formatoutput' => true,
			'addMainLangId' => true,
		);

		$requestParams = ($requestParams + $this->addExtraParams());

		return $requestParams;
	}


	/**
	 *
	 * @return array
	 *
	 */

	protected function addExtraParams() {
		$pListView = $this->_pDataView;

		$requestParams = array();

		if ($pListView->getSortby() !== null ) {
			$requestParams['sortby'] = $pListView->getSortby();
		}

		if ($pListView->getSortorder() !== null) {
			$requestParams['sortorder'] = $pListView->getSortorder();
		}

		if ($pListView->getFilterId() != null) {
			$requestParams['filterid'] = $pListView->getFilterId();
		}

		return $requestParams;
	}


	/**
	 *
	 * @return array
	 *
	 */

	protected function getDefaultFilter()
	{
		$pListViewFilterBuilder = new Filter\DefaultFilterBuilderListView($this->_pDataView);
		return $pListViewFilterBuilder->buildFilter();
	}


	/**
	 *
	 * @param array $estateResponseArray
	 * @return array Mapping: mainEstateId => multiLangId
	 *
	 */

	private function getEstateIdToForeignMapping( $estateResponseArray ) {
		if ( ! isset( $estateResponseArray['data']['records'] ) ) {
			return array();
		}

		$estateProperties = $estateResponseArray['data']['records'];

		$estateIds = array();

		foreach ( $estateProperties as $estate ) {
			if ( ! array_key_exists( 'id', $estate ) ) {
				continue;
			}

			$elements = $estate['elements'];

			if (array_key_exists('mainLangId', $elements) && $elements['mainLangId'] != null) {
				$estateIds[$elements['mainLangId']] = $estate['id'];
			} else {
				$estateIds[$estate['id']] = $estate['id'];
			}
		}

		return $estateIds;
	}


	/**
	 *
	 * @param array $responseArrayContacts
	 * @param array $estateIds
	 * @return null
	 *
	 */

	private function collectEstateContactPerson( $responseArrayContacts, array $estateIds ) {
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

			$subjectEstateId = $estateIds[$estateId];

			$this->_estateContacts[$subjectEstateId] = $adressIds;
			$allAddressIds = array_unique( array_merge( $allAddressIds, $adressIds ) );
		}

		$fields = $this->_pDataView->getAddressFields();

		if ( count( $fields ) > 0 && count( $allAddressIds ) > 0 ) {
			$this->_pAddressList->loadAdressesById( $allAddressIds, $fields );
		}
	}


	/**
	 *
	 * @return type
	 *
	 */

	private function getCensoredAddressData( $record ) {
		$requestedFields = $this->_pDataView->getAddressFields();

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
		$fieldNewName = $this->_pFieldnames->getFieldLabel($field, $recordType);

		return $fieldNewName;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getEstateLink( $view )
	{
		$pDetailView = DataView\DataDetailViewHandler::getDetailView();
		$pageId = $pDetailView->getPageId();
		$fullLink = '#';

		if ($pageId != null) {
			$estate = $this->_currentEstate['mainId'];
			$fullLink = get_page_link($pageId).$estate;
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
		return $this->_pEstateImages->getEstatePictureTitle($imageId, $currentEstate);
	}


	/**
	 *
	 * @param int $imageId
	 * @return string
	 *
	 */

	public function getEstatePictureText( $imageId ) {
		$currentEstate = $this->_currentEstate['id'];
		return $this->_pEstateImages->getEstatePictureText($imageId, $currentEstate);
	}


	/**
	 *
	 * @param int $imageId
	 * @return array
	 *
	 */

	public function getEstatePictureValues( $imageId ) {
		$currentEstate = $this->_currentEstate['id'];
		return $this->_pEstateImages->getEstatePictureValues($imageId, $currentEstate);
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
	 * @return int
	 *
	 */

	public function getCurrentMultiLangEstateMainId() {
		$recordId = $this->_currentEstate['mainId'];
		return $recordId;
	}


	/**
	 *
	 * @param string $unitViewName
	 * @param int $estateId
	 * @return string
	 *
	 */

	public function getEstateUnits() {
		$estateId = $this->getCurrentMultiLangEstateMainId();

		$pEstateUnits = new EstateUnits( array($estateId), $this->_unitsViewName );
		$unitCount = $pEstateUnits->getUnitCount( $estateId );
		$htmlOutput = '';

		if ( $unitCount > 0 ) {
			$htmlOutput = $pEstateUnits->generateHtmlOutput( $estateId );
		}

		return $htmlOutput;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getDocument() {
		$language = Language::getDefault();

		$estateId = $this->_currentEstate['mainId'];
		$documentId = $this->_pDataView->getExpose();

		$queryVars = array(
			'documentid' => $documentId,
			'estateid' => $estateId,
			'language' => $language,
			'configindex' => $this->_pDataView->getName(),
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


	/** @return onOffice\WPlugin\DataView\DataView */
	public function getDataView() {
		return $this->_pDataView;
	}

	/** @return DefaultFilterBuilder */
	public function getDefaultFilterBuilder()
		{ return $this->_pDefaultFilterBuilder; }

	/** @param DefaultFilterBuilder $pDefaultFilterBuilder */
	public function setDefaultFilterBuilder(DefaultFilterBuilder $pDefaultFilterBuilder)
		{ $this->_pDefaultFilterBuilder = $pDefaultFilterBuilder; }

	/** @return string */
	public function getUnitsViewName()
		{ return $this->_unitsViewName; }

	/** @param string $unitsViewName */
	public function setUnitsViewName($unitsViewName)
		{ $this->_unitsViewName = $unitsViewName; }

	/** @return bool */
	public function getShuffleResult()
		{ return $this->_shuffleResult; }

	/** @param bool $shuffleResult */
	public function setShuffleResult($shuffleResult)
		{ $this->_shuffleResult = $shuffleResult; }
}
