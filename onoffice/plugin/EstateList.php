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
use onOffice\WPlugin\Controller\EstateListBase;
use onOffice\WPlugin\Controller\EstateListEnvironment;
use onOffice\WPlugin\Controller\EstateListEnvironmentDefault;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataView;
use onOffice\WPlugin\Field\OutputFields;
use onOffice\WPlugin\Filter\DefaultFilterBuilder;
use onOffice\WPlugin\Filter\GeoSearchBuilder;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypes;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierHandler;
use function add_action;
use function do_action;
use function esc_url;
use function get_page_link;
use function plugin_dir_url;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */

class EstateList
	implements EstateListBase
{
	/** @var array */
	private $_responseArray = [];

	/** @var EstateFiles */
	private $_pEstateFiles = null;

	/** @var array */
	private $_currentEstate = [];

	/** @var array */
	private $_estateContacts = [];

	/** @var int */
	private $_currentEstatePage = 1;

	/** @var int */
	private $_numEstatePages = null;

	/** @var int */
	private $_handleEstateContactPerson = null;

	/** @var DataView */
	private $_pDataView = null;

	/** @var string */
	private $_unitsViewName = null;

	/** @var bool */
	private $_shuffleResult = false;

	/** @var bool */
	private $_formatOutput = true;

	/** @var EstateListEnvironment */
	private $_pEnvironment =  null;


	/**
	 *
	 * @param DataView $pDataView
	 * @param EstateListEnvironment $pEnvironment
	 *
	 */

	public function __construct(DataView $pDataView, EstateListEnvironment $pEnvironment = null)
	{
		$this->_pEnvironment = $pEnvironment ?? new EstateListEnvironmentDefault();
		$this->_pDataView = $pDataView;
	}


	/**
	 *
	 * @return int
	 *
	 */

	protected function getNumEstatePages()
	{
		if (!isset($this->_responseArray['data']['meta']['cntabsolute'])) {
			return null;
		}

		$recordNumOverAll = $this->_responseArray['data']['meta']['cntabsolute'];
		$numEstatePages = (int)ceil($recordNumOverAll / $this->_pDataView->getRecordsPerPage());

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

	public function loadEstates(int $currentPage = 1)
	{
		$pSDKWrapper = $this->_pEnvironment->getSDKWrapper();
		$this->_pEnvironment->getFieldnames()->loadLanguage();

		$parametersGetEstateList = $this->getEstateParameters($currentPage);

		$handleReadEstate = $pSDKWrapper->addRequest
			(onOfficeSDK::ACTION_ID_READ, 'estate', $parametersGetEstateList);
		$pSDKWrapper->sendRequests();

		$responseArrayEstates = $pSDKWrapper->getRequestResponse($handleReadEstate);
		$fileCategories = $this->getPreloadEstateFileCategories();

		$this->_pEstateFiles = $this->_pEnvironment->getEstateFiles($fileCategories);
		$estateIds = $this->getEstateIdToForeignMapping($responseArrayEstates);

		if ($estateIds !== []) {
			add_action('oo_beforeEstateRelations', [$this, 'registerContactPersonCall'], 10, 2);
			add_action('oo_afterEstateRelations', [$this, 'extractEstateContactPerson'], 10, 2);

			do_action('oo_beforeEstateRelations', $pSDKWrapper, $estateIds);

			$pSDKWrapper->sendRequests();

			do_action('oo_afterEstateRelations', $pSDKWrapper, $estateIds);
		}

		$this->_responseArray = $responseArrayEstates;

		if (isset($this->_responseArray['data']['records']) && $this->_shuffleResult) {
			$this->_pEnvironment->shuffle($this->_responseArray['data']['records']);
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

	public function registerContactPersonCall(SDKWrapper $pSDKWrapper, array $estateIds)
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
		$filter = $this->_pEnvironment->getDefaultFilterBuilder()->buildFilter();

		$numRecordsPerPage = $this->getRecordsPerPage();
		$offset = ( $currentPage - 1 ) * $numRecordsPerPage;
		$this->_currentEstatePage = $currentPage;

		$pFieldModifierHandler = new ViewFieldModifierHandler($pListView->getFields(),
			onOfficeSDK::MODULE_ESTATE);

		$requestParams = [
			'data' => $pFieldModifierHandler->getAllAPIFields(),
			'filter' => $filter,
			'estatelanguage' => $language,
			'outputlanguage' => $language,
			'listlimit' => $numRecordsPerPage,
			'listoffset' => $offset,
			'formatoutput' => $this->_formatOutput,
			'addMainLangId' => true,
		];

		$requestParams += $this->addExtraParams();

		return $requestParams;
	}


	/**
	 *
	 * @return array
	 *
	 */

	protected function addExtraParams(): array
	{
		$pListView = $this->_pDataView;
		$requestParams = [];

		if ($pListView->getSortby() !== null) {
			$requestParams['sortby'] = $pListView->getSortby();
		}

		if ($pListView->getSortorder() !== null) {
			$requestParams['sortorder'] = $pListView->getSortorder();
		}

		if ($pListView->getFilterId() != null) {
			$requestParams['filterid'] = $pListView->getFilterId();
		}

		$geoRangeSearchParameters = $this->_pEnvironment->getGeoSearchBuilder()->buildParameters();

		if ($geoRangeSearchParameters !== []) {
			$requestParams['georangesearch'] = $geoRangeSearchParameters;
		}

		return $requestParams;
	}


	/**
	 *
	 * @param array $estateResponseArray
	 * @return array Mapping: mainEstateId => multiLangId
	 *
	 */

	private function getEstateIdToForeignMapping($estateResponseArray)
	{
		$estateProperties = $estateResponseArray['data']['records'] ?? [];
		$estateIds = [];

		foreach ($estateProperties as $estate) {
			if (!isset($estate['id'])) {
				continue;
			}

			$elements = $estate['elements'];

			if (isset($elements['mainLangId'])) {
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

	private function collectEstateContactPerson($responseArrayContacts, array $estateIds)
	{
		$records = $responseArrayContacts['data']['records'][0]['elements'] ?? [];
		$allAddressIds = [];

		foreach ($records as $estateId => $adressIds) {
			if (is_null($adressIds)) {
				continue;
			}

			$subjectEstateId = $estateIds[$estateId];
			$this->_estateContacts[$subjectEstateId] = $adressIds;
			$allAddressIds = array_unique(array_merge($allAddressIds, $adressIds));
		}

		$fields = $this->_pDataView->getAddressFields();

		if ($fields !== [] && $allAddressIds !== []) {
			$this->_pEnvironment->getAddressList()->loadAdressesById($allAddressIds, $fields);
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
		global $numpages, $multipage, $page, $more;
		if (!isset($this->_responseArray['data']['records'])) {
			return false;
		}

		if (null !== $this->_numEstatePages) {
			$multipage = true;

			$page = $this->_currentEstatePage;
			$more = true;
			$numpages = $this->_numEstatePages;
		}

		$pEstateFieldModifierHandler = new ViewFieldModifierHandler
			($this->getFieldsForDataViewModifierHandler(), onOfficeSDK::MODULE_ESTATE, $modifier);

		$currentRecord = each($this->_responseArray['data']['records']);

		$this->_currentEstate['id'] = $currentRecord['value']['id'];
		$this->_currentEstate['mainId'] = $this->_currentEstate['id'];
		$recordElements = $currentRecord['value']['elements'];

		if (is_array($recordElements) && array_key_exists('mainLangId', $recordElements) &&
			$recordElements['mainLangId'] != null) {
			$this->_currentEstate['mainId'] = $recordElements['mainLangId'];
		}

		if (false !== $currentRecord) {
			$record = $currentRecord['value']['elements'];
			$recordModified = $pEstateFieldModifierHandler->processRecord($record);
			$pArrayContainer = new ArrayContainerEscape($recordModified);

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

	public function getFieldLabel($field): string
	{
		$recordType = onOfficeSDK::MODULE_ESTATE;
		$fieldNewName = $this->_pEnvironment->getFieldnames()->getFieldLabel($field, $recordType);

		return $fieldNewName;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getEstateLink(): string
	{
		$pageId = $this->_pEnvironment->getDataDetailView()->getPageId();
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

	public function	getEstatePictures(array $types = null)
	{
		$estateId = $this->_currentEstate['id'];
		$estateFiles = [];
		$estateImages = $this->_pEstateFiles->getEstatePictures($estateId);

		foreach ($estateImages as $image) {
			if (null !== $types && !in_array($image['type'], $types, true)) {
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

	public function getEstateMovieLinks(): array
	{
		return [];
	}


	/**
	 *
	 * Not supported in list view
	 * @param array $options
	 * @return array
	 *
	 */

	public function getMovieEmbedPlayers(array $options = []): array
	{
		return [];
	}


	/**
	 *
	 * @param int $imageId
	 * @param array $options
	 * @return string
	 *
	 */

	public function getEstatePictureUrl($imageId, array $options = null)
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

	public function getEstatePictureTitle($imageId)
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

	public function getEstatePictureText($imageId)
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

	public function getEstatePictureValues($imageId)
	{
		$currentEstate = $this->_currentEstate['id'];
		return $this->_pEstateFiles->getEstatePictureValues($imageId, $currentEstate);
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getEstateContactIds(): array
	{
		$recordId = $this->_currentEstate['id'];
		return $this->_estateContacts[$recordId] ?? [];
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getEstateContacts()
	{
		$addressIds = $this->getEstateContactIds();
		$result = [];

		foreach ($addressIds as $addressId) {
			$currentAddressData = $this->_pEnvironment->getAddressList()->getAddressById($addressId);
			$pArrayContainerCurrentAddress = new ArrayContainerEscape($currentAddressData);
			$result []= $pArrayContainerCurrentAddress;
		}

		return $result;
	}


	/**
	 *
	 * @return int
	 *
	 */

	public function getCurrentEstateId(): int
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
			$pEstateUnits = $this->_pEnvironment->getEstateUnitsByName($this->_unitsViewName);
			$pEstateUnits->loadByMainEstates($this);
			$unitCount = $pEstateUnits->getSubEstateCount($estateId);

			if ($unitCount > 0) {
				$htmlOutput = $pEstateUnits->generateHtmlOutput($estateId);
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
		$queryVars = [
			'estateid' => $this->getCurrentMultiLangEstateMainId(),
			'language' => Language::getDefault(),
			'configindex' => $this->_pDataView->getName(),
		];

		$documentlink = plugin_dir_url(__DIR__).'document.php?'.http_build_query($queryVars);
		return esc_url($documentlink);
	}


	/**
	 *
	 * @return string[] An array of visible fields
	 *
	 */

	public function getVisibleFilterableFields(): array
	{
		$fieldsValues = $this->_pEnvironment
			->getOutputFields($this->_pDataView)
			->getVisibleFilterableFields();
		$result = [];
		foreach ($fieldsValues as $field => $value) {
			$result[$field] = $this->_pEnvironment
				->getFieldnames()
				->getFieldInformation($field, onOfficeSDK::MODULE_ESTATE);
			$result[$field]['value'] = $value;
		}
		return $result;
	}


	/**
	 *
	 */

	public function resetEstateIterator()
	{
		reset($this->_responseArray['data']['records']);
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


	/**
	 *
	 * @return array
	 *
	 */

	public function getEstateIds(): array
	{
		return array_column($this->_responseArray['data']['records'], 'id');
	}


	/** @return EstateFiles */
	protected function getEstateFiles()
		{ return $this->_pEstateFiles; }

	/** @return DataView */
	public function getDataView(): DataView
		{ return $this->_pDataView; }

	/** @return DefaultFilterBuilder */
	public function getDefaultFilterBuilder(): DefaultFilterBuilder
		{ return $this->_pEnvironment->getDefaultFilterBuilder(); }

	/** @param DefaultFilterBuilder $pDefaultFilterBuilder */
	public function setDefaultFilterBuilder(DefaultFilterBuilder $pDefaultFilterBuilder)
		{ $this->_pEnvironment->setDefaultFilterBuilder($pDefaultFilterBuilder); }

	/** @return string */
	public function getUnitsViewName()
		{ return $this->_unitsViewName; }

	/** @param string $unitsViewName */
	public function setUnitsViewName($unitsViewName)
		{ $this->_unitsViewName = $unitsViewName; }

	/** @return bool */
	public function getShuffleResult(): bool
		{ return $this->_shuffleResult; }

	/** @param bool $shuffleResult */
	public function setShuffleResult(bool $shuffleResult)
		{ $this->_shuffleResult = $shuffleResult; }

	/** @return GeoSearchBuilder */
	public function getGeoSearchBuilder(): GeoSearchBuilder
		{ return $this->_pEnvironment->getGeoSearchBuilder(); }

	/** @param GeoSearchBuilder $pGeoSearchBuilder */
	public function setGeoSearchBuilder(GeoSearchBuilder $pGeoSearchBuilder)
		{ $this->_pEnvironment->setGeoSearchBuilder($pGeoSearchBuilder); }

	/** @return bool */
	public function getFormatOutput(): bool
		{ return $this->_formatOutput; }

	/** @param bool $formatOutput */
	public function setFormatOutput(bool $formatOutput)
		{ $this->_formatOutput = $formatOutput; }

	/** @return EstateListEnvironment */
	public function getEnvironment(): EstateListEnvironment
		{ return $this->_pEnvironment; }
}
