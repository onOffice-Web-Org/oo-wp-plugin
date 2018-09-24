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

use DateTime;
use Exception;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\EstateListInputVariableReader;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataView;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Filter\DefaultFilterBuilder;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListView;
use onOffice\WPlugin\Gui\DateTimeFormatter;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypes;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierHandler;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */

class EstateList
{
	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var Fieldnames */
	private $_pFieldnames = null;

	/** @var array */
	private $_responseArray = array();

	/** @var EstateFiles */
	private $_pEstateFiles = null;

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

	/** @var DefaultFilterBuilder */
	private $_pDefaultFilterBuilder = null;

	/** @var string */
	private $_unitsViewName = null;

	/** @var bool */
	private $_shuffleResult = false;

	/** @var EstateListInputVariableReader */
	private $_pEstateListInputVariableReader = null;


	/**
	 *
	 * @param DataView $pDataView
	 * @param DefaultFilterBuilder $pDefaultFilterBuilder
	 *
	 */

	public function __construct(DataView $pDataView)
	{
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

	protected function getNumEstatePages()
	{
		if ( empty( $this->_responseArray['data']['meta']['cntabsolute'] ) ) {
			return null;
		}

		$recordNumOverAll = $this->_responseArray['data']['meta']['cntabsolute'];
		$numEstatePages = ceil( $recordNumOverAll / $this->_pDataView->getRecordsPerPage() );

		return $numEstatePages;
	}


	/**
	 *
	 * @return int
	 *
	 */

	protected function getRecordsPerPage()
	{
		return $this->_pDataView->getRecordsPerPage();
	}


	/**
	 *
	 * @return array
	 *
	 */

	protected function getPreloadEstateFileCategories()
	{
		return $this->_pDataView->getPictureTypes();
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
		$fileCategories = $this->getPreloadEstateFileCategories();

		$this->_pEstateFiles = new EstateFiles( $fileCategories );

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
	 * @param SDKWrapper $pSDKWrapper
	 * @param array $estateIds
	 *
	 */

	public function registerContactPersonCall( SDKWrapper $pSDKWrapper, array $estateIds)
	{
		$parameters = [
			'parentids' => array_keys($estateIds),
			'relationtype' => onOfficeSDK::RELATION_TYPE_CONTACT_BROKER,
		];

		$this->_handleEstateContactPerson = $pSDKWrapper->addRequest
			(onOfficeSDK::ACTION_ID_GET, 'idsfromrelation', $parameters);
	}


	/**
	 *
	 * @param SDKWrapper $pSDKWrapper
	 * @param array $estateIds
	 *
	 */

	public function extractEstateContactPerson(SDKWrapper $pSDKWrapper, array $estateIds)
	{
		$responseArrayContactPerson = $pSDKWrapper->getRequestResponse
			($this->_handleEstateContactPerson);
		$this->collectEstateContactPerson($responseArrayContactPerson, $estateIds);
	}


	/**
	 *
	 * @param int $currentPage
	 * @return array
	 *
	 */

	private function getEstateParameters($currentPage)
	{
		$language = Language::getDefault();
		$pListView = $this->_pDataView;
		$pFilterBuilder = $this->_pDefaultFilterBuilder;

		if ($pFilterBuilder === null) {
			throw new Exception('$_pDefaultFilterBuilder must not be null');
		}

		$filter = $this->_pDefaultFilterBuilder->buildFilter();

		$numRecordsPerPage = $this->getRecordsPerPage();
		$offset = ( $currentPage - 1 ) * $numRecordsPerPage;
		$this->_currentEstatePage = $currentPage;

		$pFieldModifierHandler = new ViewFieldModifierHandler($pListView->getFields(),
			onOfficeSDK::MODULE_ESTATE);

		$dataFields = $pFieldModifierHandler->getAllAPIFields();

		if (in_array(GeoPosition::FIELD_GEO_POSITION, $dataFields))	{
			$pos = array_search(GeoPosition::FIELD_GEO_POSITION, $dataFields);
			unset($dataFields[$pos]);
			$dataFields []= 'laengengrad';
			$dataFields []= 'breitengrad';
		}

		$requestParams = array(
			'data' => $dataFields,
			'filter' => $filter,
			'estatelanguage' => $language,
			'outputlanguage' => $language,
			'listlimit' => $numRecordsPerPage,
			'listoffset' => $offset,
			'formatoutput' => true,
			'addMainLangId' => true,
		);

		$requestParams += $this->addExtraParams();

		return $requestParams;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getGeoSearchValues()
	{
		$pEstateInputReader = new EstateListInputVariableReader();

		$inputValues = [];
		$pGeoPosition = new GeoPosition();

		foreach ($pGeoPosition->getEstateSearchFields() as $key) {
			$inputValues[$key] = $pEstateInputReader->getFieldValue(
				$key, FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		}

		return $inputValues;
	}


	/**
	 *
	 * @return array
	 *
	 */

	protected function addExtraParams()
	{
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

		$pGeoPosition = new GeoPosition();
		$geoInputValues = $this->getGeoSearchValues();

		$requestGeoSearchParameters = $pGeoPosition->createGeoRangeSearchParameterRequest
			($geoInputValues);

		if ($requestGeoSearchParameters !== []) {
			$requestParams['georangesearch'] = $requestGeoSearchParameters;
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
		$pListViewFilterBuilder = new DefaultFilterBuilderListView($this->_pDataView);
		return $pListViewFilterBuilder->buildFilter();
	}


	/**
	 *
	 * @param array $estateResponseArray
	 * @return array Mapping: mainEstateId => multiLangId
	 *
	 */

	private function getEstateIdToForeignMapping( $estateResponseArray )
	{
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

	private function collectEstateContactPerson( $responseArrayContacts, array $estateIds )
	{
		if ( ! isset( $responseArrayContacts['data']['records'][0]['elements'] ) ) {
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
	 * @param string $modifier
	 * @return ArrayContainerEscape
	 *
	 */

	public function estateIterator($modifier = EstateViewFieldModifierTypes::MODIFIER_TYPE_DEFAULT)
	{
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

		$pEstateFieldModifierHandler = new ViewFieldModifierHandler
			($this->getFieldsForDataViewModifierHandler(), onOfficeSDK::MODULE_ESTATE, $modifier);

		$currentRecord = each( $this->_responseArray['data']['records'] );

		$this->_currentEstate['id'] = $currentRecord['value']['id'];
		$this->_currentEstate['type'] = $currentRecord['value']['type'];
		$this->_currentEstate['mainId'] = $this->_currentEstate['id'];
		$recordElements = $currentRecord['value']['elements'];

		if (is_array($recordElements) && array_key_exists('mainLangId', $recordElements) &&
			$recordElements['mainLangId'] != null) {
			$this->_currentEstate['mainId'] = $recordElements['mainLangId'];
		}

		if ( false !== $currentRecord ) {
			$record = $currentRecord['value']['elements'];
			$recordModified = $pEstateFieldModifierHandler->processRecord($record);
			$pArrayContainer = new ArrayContainerEscape( $recordModified );

			return $pArrayContainer;
		}

		return false;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getFieldsForDataViewModifierHandler(): array
	{
		$fields = $this->_pDataView->getFields();
		if ($this->getShowEstateMarketingStatus()) {
			$fields []= 'vermarktungsstatus';
		}

		return $fields;
	}


	/**
	 *
	 * @return int
	 *
	 */

	public function getEstateOverallCount()
	{
		return $this->_responseArray['data']['meta']['cntabsolute'];
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getFieldLabel( $field )
	{
		$recordType = $this->_currentEstate['type'];
		$fieldNewName = $this->_pFieldnames->getFieldLabel($field, $recordType);

		return $fieldNewName;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getEstateLink()
	{
		$pDataDetailViewHandler = new DataDetailViewHandler();
		$pDetailView = $pDataDetailViewHandler->getDetailView();
		$pageId = $pDetailView->getPageId();
		$fullLink = '#';

		if ($pageId !== 0) {
			$estate = $this->_currentEstate['mainId'];
			$fullLink = get_page_link($pageId).$estate;
		}

		return $fullLink;
	}


	/**
	 *
	 * @param array $types
	 * @return array
	 *
	 */

	public function getEstatePictures( array $types = null )
	{
		$estateId = $this->_currentEstate['id'];
		$estateFiles = array();
		$estateImages = $this->_pEstateFiles->getEstatePictures( $estateId );

		foreach ( $estateImages as $image ) {
			if ( null !== $types && ! in_array( $image['type'], $types, true ) ) {
				continue;
			}
			$estateFiles []= $image['id'];
		}

		return $estateFiles;
	}


	/**
	 *
	 * Not supported in list view
	 * @return array Returns an array if Movie Links are active and displayed as Link
	 *
	 */

	public function getEstateMovieLinks()
	{
		return array();
	}


	/**
	 *
	 * Not supported in list view
	 * @param array $options
	 * @return array
	 *
	 */

	public function getMovieEmbedPlayers($options = array())
	{
		return array();
	}


	/**
	 *
	 * @param int $imageId
	 * @param array $options
	 * @return string
	 *
	 */

	public function getEstatePictureUrl( $imageId, array $options = null )
	{
		$currentEstate = $this->_currentEstate['id'];
		return $this->_pEstateFiles->getEstateFileUrl($imageId, $currentEstate, $options);
	}


	/**
	 *
	 * @param int $imageId
	 * @return string
	 *
	 */

	public function getEstatePictureTitle( $imageId )
	{
		$currentEstate = $this->_currentEstate['id'];
		return $this->_pEstateFiles->getEstatePictureTitle($imageId, $currentEstate);
	}


	/**
	 *
	 * @param int $imageId
	 * @return string
	 *
	 */

	public function getEstatePictureText( $imageId )
	{
		$currentEstate = $this->_currentEstate['id'];
		return $this->_pEstateFiles->getEstatePictureText($imageId, $currentEstate);
	}


	/**
	 *
	 * @param int $imageId
	 * @return array
	 *
	 */

	public function getEstatePictureValues( $imageId )
	{
		$currentEstate = $this->_currentEstate['id'];
		return $this->_pEstateFiles->getEstatePictureValues($imageId, $currentEstate);
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getEstateContactIds()
	{
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

	public function getEstateContacts()
	{
		$addressIds = $this->getEstateContactIds();
		$result = array();

		foreach ( $addressIds as $addressId ) {
			$currentAddressData = $this->_pAddressList->getAddressById( $addressId );
			$pArrayContainerCurrentAddress = new ArrayContainerEscape( $currentAddressData );
			$result []= $pArrayContainerCurrentAddress;
		}

		return $result;
	}


	/**
	 *
	 * @return int
	 *
	 */

	public function getCurrentEstateId()
	{
		return $this->_currentEstate['id'];
	}


	/**
	 *
	 * @return int
	 *
	 */

	public function getCurrentMultiLangEstateMainId()
	{
		return $this->_currentEstate['mainId'];
	}


	/**
	 *
	 * @param string $unitViewName
	 * @param int $estateId
	 * @return string
	 *
	 */

	public function getEstateUnits()
	{
		$estateId = $this->getCurrentMultiLangEstateMainId();
		$htmlOutput = '';

		if ($this->_unitsViewName !== null) {
			$pEstateUnits = new EstateUnits( array($estateId), $this->_unitsViewName );
			$unitCount = $pEstateUnits->getUnitCount( $estateId );

			if ( $unitCount > 0 ) {
				$htmlOutput = $pEstateUnits->generateHtmlOutput( $estateId );
			}
		}

		return $htmlOutput;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getDocument()
	{
		$language = Language::getDefault();

		$estateId = $this->_currentEstate['mainId'];
		$queryVars = array(
			'estateid' => $estateId,
			'language' => $language,
			'configindex' => $this->_pDataView->getName(),
		);

		$documentlink = plugin_dir_url( __DIR__ ).'document.php?'.http_build_query( $queryVars );
		return esc_url( $documentlink );
	}


	/**
	 *
	 * @return string[] An array of visible fields
	 *
	 */

	public function getVisibleFilterableFields()
	{
		if (!$this->_pDataView instanceof DataListView) {
			throw new Exception('Only list views are filterable!');
		}

		$pDataListView = $this->_pDataView;

		/* @var $pDataListView DataListView */
		$filterable = $pDataListView->getFilterableFields();
		$hidden = $pDataListView->getHiddenFields();

		$visibleFilterable = array_diff($filterable, $hidden);

		$fieldsArray = array_combine($visibleFilterable, $visibleFilterable);
		$this->_pEstateListInputVariableReader = new EstateListInputVariableReader();
		$fieldsArray = $this->editForGeoPosition($fieldsArray);

		$result = array_map(array($this, 'getFieldInformationForEstateField'), $fieldsArray);

		return $result;
	}


	/**
	 *
	 * @param array $fieldsArray
	 * @return array
	 *
	 */

	private function editForGeoPosition(array $fieldsArray):array
	{
		if (array_key_exists(GeoPosition::FIELD_GEO_POSITION, $fieldsArray)) {
			$pGeoPosition = new GeoPosition();

			$pos = array_search(GeoPosition::FIELD_GEO_POSITION, $fieldsArray);
			unset($fieldsArray[$pos]);

			$geoFields = $pGeoPosition->getEstateSearchFields();
			$fieldsArray = array_merge($fieldsArray, array_combine($geoFields, $geoFields));
		}

		return $fieldsArray;
	}


	/**
	 *
	 * @internal for Callback only
	 * @param string $fieldInput
	 * @return array
	 *
	 */

	public function getFieldInformationForEstateField($fieldInput)
	{
		$fieldInformation = $this->_pFieldnames->getFieldInformation
			($fieldInput, onOfficeSDK::MODULE_ESTATE);
		$value = $this->_pEstateListInputVariableReader->getFieldValue($fieldInput);
		$fieldInformation['value'] = $this->formatValue($value, $fieldInformation['type']);
		return $fieldInformation;
	}


	/**
	 *
	 * @param mixed $value
	 * @param string $type
	 * @return type
	 *
	 */

	private function formatValue($value, $type)
	{
		if (is_float($value)) {
			return number_format_i18n($value, 2);
		} elseif (is_array($value)) {
			$value = array_map(function($val) use ($type) {
				return $this->formatValue($val, $type);
			}, $value);
		} elseif (Types\FieldTypes::isDateOrDateTime($type) && $value != '') {
			$format = DateTimeFormatter::SHORT|DateTimeFormatter::ADD_DATE;
			if ($type === Types\FieldTypes::FIELD_TYPE_DATETIME) {
				$format |= DateTimeFormatter::ADD_TIME;
			}

			$pDate = new DateTime($value.' Europe/Berlin');
			$pDateTimeFormatter = new DateTimeFormatter();
			$value = $pDateTimeFormatter->formatByTimestamp($format, $pDate->getTimestamp());
		}
		return $value;
	}


	/**
	 *
	 */

	public function resetEstateIterator()
	{
		reset( $this->_responseArray['data']['records'] );
	}


	/**
	 *
	 * @return bool
	 *
	 */

	public function getShowEstateMarketingStatus(): bool
	{
		return $this->_pDataView instanceof DataListView &&
			$this->_pDataView->getShowStatus();
	}


	/** @return EstateFiles */
	protected function getEstateFiles()
		{ return $this->_pEstateFiles; }

	/** @return DataView */
	public function getDataView()
		{ return $this->_pDataView; }

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
