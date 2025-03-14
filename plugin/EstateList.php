<?php

/**
 *
 *    Copyright (C) 2016-2020 onOffice GmbH
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

use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\SDK\Exception\HttpFetchNoResultException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Controller\EstateDetailUrl;
use onOffice\WPlugin\Controller\EstateListBase;
use onOffice\WPlugin\Controller\EstateListEnvironment;
use onOffice\WPlugin\Controller\EstateListEnvironmentDefault;
use onOffice\WPlugin\Controller\GeoPositionFieldHandler;
use onOffice\WPlugin\Controller\InputVariableReaderFormatter;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataView;
use onOffice\WPlugin\DataView\DataViewFilterableFields;
use onOffice\WPlugin\DataView\DataViewSimilarEstates;
use onOffice\WPlugin\DataView\UnknownViewException;
use onOffice\WPlugin\Field\Collection\FieldsCollectionFieldDuplicatorForGeoEstate;
use onOffice\WPlugin\Field\DistinctFieldsHandler;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorGeoPositionFrontend;
use onOffice\WPlugin\Field\OutputFields;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Filter\DefaultFilterBuilder;
use onOffice\WPlugin\Filter\GeoSearchBuilder;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\ImageTypes;
use onOffice\WPlugin\Utility\Redirector;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypes;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierHandler;
use function esc_url;
use function get_page_link;
use function home_url;
use function esc_attr;
use onOffice\WPlugin\WP\WPOptionWrapperDefault;
use onOffice\WPlugin\WP\WPPluginChecker;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\FieldParkingLot;
use onOffice\WPlugin\Filter\DefaultFilterBuilderFactory;
use onOffice\WPlugin\Field\CostsCalculator;

class EstateList
	implements EstateListBase
{
	const DEFAULT_LIMIT_CHARACTER_DESCRIPTION = 150;

	/** @var array */
	private $_records = [];

	/** @var array */
	private $_recordsRaw = [];

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

	/** @var DataView */
	private $_pDataView = null;

	/** @var string */
	private $_unitsViewName = null;

	/** @var bool */
	private $_formatOutput = true;

	/** @var EstateListEnvironment */
	private $_pEnvironment =  null;

	/** @var APIClientActionGeneric */
	private $_pApiClientAction = null;

	/** @var GeoSearchBuilder */
	private $_pGeoSearchBuilder = null;

	/** @var EstateDetailUrl */
	private $_pLanguageSwitcher;

	private $_pWPOptionWrapper;

	/** @var int */
	private $_filterAddressId;

	/** @var Redirector */
	private $_redirectIfOldUrl;

	/** @var array */
	private $_totalCostsData = [];

	/** @var FieldsCollection */
	private $_pFieldsCollection;

	/** @var string */
	private $_energyCertificate = '';


	/**
	 * @param DataView $pDataView
	 * @param EstateListEnvironment $pEnvironment
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function __construct(DataView $pDataView, EstateListEnvironment $pEnvironment = null)
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();
		$this->_pEnvironment = $pEnvironment ?? new EstateListEnvironmentDefault($pContainer);
		$this->_pDataView = $pDataView;
		$pSDKWrapper = $this->_pEnvironment->getSDKWrapper();
		$this->_pApiClientAction = new APIClientActionGeneric($pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'estate');
		$this->_pGeoSearchBuilder = $this->_pEnvironment->getGeoSearchBuilder();
		$this->_pLanguageSwitcher = $pContainer->get(EstateDetailUrl::class);
		$this->_pWPOptionWrapper = $pContainer->get(WPOptionWrapperDefault::class);
		$this->_redirectIfOldUrl = $pContainer->get(Redirector::class);
	}

	/**
	 * @return int
	 * @throws API\ApiClientException
	 */
	protected function getNumEstatePages()
	{
		$recordNumOverAll = $this->getEstateOverallCount();
		$recordsPerPageView = $this->_pDataView->getRecordsPerPage();
		// 20 is the default of API in case recordsPerPage <= 0
		$recordsPerPage = $recordsPerPageView <= 0 ? 20 : $recordsPerPageView;
		$numEstatePages = (int)ceil($recordNumOverAll / $recordsPerPage);

		return $numEstatePages;
	}


	/**
	 * @return int
	 */
	protected function getRecordsPerPage()
	{
		return $this->_pDataView->getRecordsPerPage();
	}

	/**
	 * @return array
	 */
	protected function getPreloadEstateFileCategories()
	{
		return $this->_pDataView->getPictureTypes();
	}

	/**
	 * @param int $currentPage
	 * @param DataView $pDataListView
	 * @throws API\APIEmptyResultException
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownViewException
	 * @throws HttpFetchNoResultException
	 * @throws API\ApiClientException
	 */
	public function loadEstates(int $currentPage = 1, DataView $pDataListView = null)
	{
		if ($pDataListView === null) {
			$pDataListView = $this->_pDataView;
		}
		$this->_pEnvironment->getFieldnames()->loadLanguage();
		if ($this->_pDataView instanceof DataListView && $this->_pDataView->getSortBySetting() === DataListView::SHOW_MARKED_PROPERTIES_SORT) {
			$this->loadRecordsOrderEstatesByTags($currentPage);
		} else {
			$this->loadRecords($currentPage);
		}

		$fileCategories = $this->getPreloadEstateFileCategories();

		$estateIds = $this->getEstateIdToForeignMapping($this->_records);

		if ($estateIds !== []) {
			$this->getEstateContactPerson($estateIds);

			$this->_pEstateFiles = $this->_pEnvironment->getEstateFiles();
			$this->_pEstateFiles->getAllFiles($fileCategories, $estateIds, $this->_pEnvironment->getSDKWrapper());
		}

		if ($pDataListView->getRandom()) {
			$this->_pEnvironment->shuffle($this->_records);
		}

		$this->_numEstatePages = $this->getNumEstatePages();
		$this->resetEstateIterator();
		$this->buildFieldsCollectionForEstate();
	}

	/**
	 *
	 */
	private function buildFieldsCollectionForEstate()
	{
		$this->_pFieldsCollection = new FieldsCollection();
		$pFieldBuilderShort = $this->_pEnvironment->getContainer()->get(FieldsCollectionBuilderShort::class);
		$listType = method_exists($this->_pDataView, 'getListType') ? $this->_pDataView->getListType() : null;
		$pFieldBuilderShort
			->addFieldsAddressEstate($this->_pFieldsCollection)
			->addFieldsEstateGeoPositionFrontend($this->_pFieldsCollection)
			->addCustomLabelFieldsEstateFrontend($this->_pFieldsCollection, $this->_pDataView->getName(), $listType);
	}

	/**
	 * @param int $currentPage
	 * @throws UnknownViewException
	 * @throws API\ApiClientException
	 */
	private function loadRecords(int $currentPage)
	{
		$estateParameters = $this->getEstateParameters($currentPage, $this->_formatOutput);
		$this->_pApiClientAction->setParameters($estateParameters);
		$this->_pApiClientAction->addRequestToQueue();

		$estateParametersRaw = $this->getEstateParameters($currentPage, false);
		$estateParametersRaw['data'] = $this->_pEnvironment->getEstateStatusLabel()->getFieldsByPrio();
		$estateParametersRaw['data'][] = 'vermarktungsart';
		$estateParametersRaw['data'][] = 'preisAufAnfrage';

		if (in_array('multiParkingLot', $this->_pDataView->getFields())) {
			$estateParametersRaw['data'] []= 'waehrung';
		}

		if ($this->getShowTotalCostsCalculator()) {
			$fields = ['kaufpreis', 'aussen_courtage', 'bundesland', 'waehrung'];
			$estateParametersRaw['data'] = array_merge($estateParametersRaw['data'], $fields);
		}

		if ($this->getShowEnergyCertificate()) {
			$energyCertificateFields = ['energieausweistyp', 'energyClass'];
			$estateParametersRaw['data'] = array_merge($estateParametersRaw['data'], $energyCertificateFields);
		}

		$estateParametersRaw['data'] = array_unique($estateParametersRaw['data']);

		$pApiClientActionRawValues = clone $this->_pApiClientAction;
		$pApiClientActionRawValues->setParameters($estateParametersRaw);
		$pApiClientActionRawValues->addRequestToQueue()->sendRequests();

		$this->_records = $this->_pApiClientAction->getResultRecords();
		$recordsRaw = $pApiClientActionRawValues->getResultRecords();
		$this->_recordsRaw = array_combine(array_column($recordsRaw, 'id'), $recordsRaw);
	}

	/**
	 * @param int $currentPage
	 * @throws UnknownViewException
	 * @throws API\ApiClientException
	 */
	private function loadRecordsOrderEstatesByTags(int $currentPage)
	{
		$this->_records = $this->fetchDataForOrderEstatesByTags($currentPage, $this->_formatOutput);
		$formattedRecordsRaw = $this->fetchDataForOrderEstatesByTags($currentPage, false);

		$numRecordsPerPage = $this->getRecordsPerPage();
		$startPosition = ($currentPage - 1) * $numRecordsPerPage;

		$combinedRawRecords = array_combine(array_column($formattedRecordsRaw, 'id'), $formattedRecordsRaw);
		$result = [];
		$this->processRecordsRawForOrderEsates($combinedRawRecords, $result);
		$pagedRecords = array_slice($result, $startPosition, $numRecordsPerPage, true);
		$this->_records = $pagedRecords;
	}

	/**
	 * @param array $recordsRaw
	 * @param array $result
	 */
	private function processRecordsRawForOrderEsates(&$recordsRaw, &$result)
	{
		foreach ($recordsRaw as $recordRaw) {
			$labelTag = $this->getInfoTagOfProperty($recordRaw["elements"]);
			$this->_recordsRaw[$recordRaw['id']] = $recordRaw;
			$this->_recordsRaw[$recordRaw['id']]["elements"]["tagNameOfEstate"] = $labelTag[1] ?? '';

			$records = array_filter($this->_records, function ($record) use ($recordRaw) {
				return $recordRaw['id'] === $record['id'];
			});

			$result = array_merge($result, $records);
		}
	}

	/**
	 * @param array $infoTagOfProperty
	 * @return array
	 */
	private function getInfoTagOfProperty(array $infoTagOfProperty): array
	{
		$sortByTags = preg_split("/,/", $this->_pDataView->getMarkedPropertiesSort());

		foreach ($sortByTags as $index => $key) {
			if (($infoTagOfProperty["vermarktungsart"] === $key && $infoTagOfProperty["verkauft"] === "1") ||
				(isset($infoTagOfProperty[$key]) && $infoTagOfProperty[$key] === "1")
			) {
				return [$index, $key];
			}
		}

		return [array_search("no_marker", $sortByTags), ''];
	}

	/**
	 * @param int $currentPage
	 * @param bool $formatOutput
	 * @return array
	 */
	private function fetchDataForOrderEstatesByTags(int $currentPage, bool $formatOutput): array
	{
		$language = Language::getDefault();
		$pListView = $this->filterActiveInputFields($this->_pDataView);
		$filter = $this->_pEnvironment->getDefaultFilterBuilder()->buildFilter();

		$numRecordsPerPage = 500;

		$pFieldModifierHandler = new ViewFieldModifierHandler(
			$pListView->getFields(),
			onOfficeSDK::MODULE_ESTATE
		);

		$aggregatedData = [];
		$totalFetched = 0;
		$this->_currentEstatePage = $currentPage;

		do {
			$offset = $totalFetched;
			$requestParams = [
				'data' => $pFieldModifierHandler->getAllAPIFields(),
				'filter' => $filter,
				'listlimit' => $numRecordsPerPage,
				'estatelanguage' => $language,
				'outputlanguage' => $language,
				'listoffset' => $offset,
				'formatoutput' => $formatOutput,
				'addMainLangId' => true,
			];
			if ($formatOutput !== true) {
				$requestParams['data'] = $this->_pEnvironment->getEstateStatusLabel()->getFieldsByPrio();
				$requestParams['data'][] = 'vermarktungsart';
				$requestParams['data'][] = 'preisAufAnfrage';
				if (in_array('multiParkingLot', $this->_pDataView->getFields())) {
					$requestParams['data'][] = 'waehrung';
				}
			}
			if ($this->enableShowPriceOnRequestText() && !isset($requestParams['data']['preisAufAnfrage'])) {
				$requestParams['data'][] = 'preisAufAnfrage';
			}
			if ($pListView->getName() === 'detail') {
				if ($this->getViewRestrict()) {
					$requestParams['filter']['referenz'][] = ['op' => '=', 'val' => 0];
				}
			} elseif ($this->getShowReferenceEstate() === DataListView::HIDE_REFERENCE_ESTATE) {
				$requestParams['filter']['referenz'][] = ['op' => '=', 'val' => 0];
			} elseif ($this->getShowReferenceEstate() === DataListView::SHOW_ONLY_REFERENCE_ESTATE) {
				$requestParams['filter']['referenz'][] = ['op' => '=', 'val' => 1];
			}

			$requestParams += $this->addExtraParams();

			$this->_pApiClientAction->setParameters($requestParams);
			$this->_pApiClientAction->addRequestToQueue()->sendRequests();
			$result = $this->_pApiClientAction->getResultRecords();

			$aggregatedData = array_merge($aggregatedData, $result);
			$totalFetched += count($result);
		} while (count($result) == $numRecordsPerPage);

		if ($formatOutput !== true) {
			usort($aggregatedData, [$this, 'sortMarkedProperties']);
		}

		return $aggregatedData;
	}

	/**
	 * @param array $recordA
	 * @param array $recordB
	 */
	private function sortMarkedProperties($recordA, $recordB)
	{
		$aPriority = $this->getInfoTagOfProperty($recordA['elements'])[0];
		$bPriority = $this->getInfoTagOfProperty($recordB['elements'])[0];

		return $aPriority - $bPriority;
	}

	/**
	 * @param $inputs
	 * @return DataView
	 */

	private function filterActiveInputFields($inputs): DataView
	{
		$activeInputs = [];
		$recordType = onOfficeSDK::MODULE_ESTATE;
		$pFieldsCollection = new FieldsCollection();
		$pFieldBuilderShort = $this->_pEnvironment->getContainer()->get(FieldsCollectionBuilderShort::class);
		$pFieldBuilderShort
			->addFieldsAddressEstate($pFieldsCollection)
			->addFieldsEstateGeoPosisionBackend($pFieldsCollection);

		foreach ($inputs->getFields() as $name) {
			if ($pFieldsCollection->containsFieldByModule($recordType, $name)) {
				$activeInputs[] = $name;
			}
		}
		$inputs->setFields($activeInputs);
		return $inputs;
	}

	/**
	 * @param $inputs
	 * @return DataView
	 *
	 */

	private function filterActiveInputFilterableFields($inputs): DataView
	{
		$activeInputs = [];
		$recordType = onOfficeSDK::MODULE_ESTATE;
		$pFieldsCollection = new FieldsCollection();
		$pFieldBuilderShort = $this->_pEnvironment->getContainer()->get(FieldsCollectionBuilderShort::class);
		$pFieldBuilderShort
			->addFieldsAddressEstate($pFieldsCollection)
			->addFieldsEstateGeoPosisionBackend($pFieldsCollection);

		foreach ($inputs->getFilterableFields() as $name) {
			if ($pFieldsCollection->containsFieldByModule($recordType, $name)) {
				$activeInputs[] = $name;
			}
		}
		$inputs->setFilterableFields($activeInputs);
		return $inputs;
	}

	/**
	 * @param array $estateIds
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws API\ApiClientException
	 */
	private function getEstateContactPerson(array $estateIds)
	{
		$pSDKWrapper = $this->_pEnvironment->getSDKWrapper();

		$parameters = [
			'parentids' => array_keys($estateIds),
			'relationtype' => onOfficeSDK::RELATION_TYPE_CONTACT_BROKER,
		];
		$pAPIClientAction = new APIClientActionGeneric($pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'idsfromrelation');
		$pAPIClientAction->setParameters($parameters);
		$pAPIClientAction->addRequestToQueue()->sendRequests();
		$this->collectEstateContactPerson($pAPIClientAction->getResultRecords(), $estateIds);
	}

	/**
	 * @param string $lang
	 * @param bool $formatOutput
	 * @return array
	 * @throws UnknownViewException
	 */
	public function getEstateListParametersForCache (string $lang, bool $formatOutput)
	{
		$pListView = $this->filterActiveInputFields($this->_pDataView);
		$pFieldModifierHandler = new ViewFieldModifierHandler($pListView->getFields(), onOfficeSDK::MODULE_ESTATE);

		$filter = $this->getDefaultFilterBuilder()->getDefaultFilter();
		$fields = $pFieldModifierHandler->getAllAPIFields();

		$requestParams = [
			'listname' => $this->_pDataView->getName(),
			'data' => $fields,
			'filter' => $filter,
			'estatelanguage' => $lang,
			'outputlanguage' => $lang,
			'listlimit' => 500,
			'formatoutput' => $formatOutput,
			'addMainLangId' => true
		];

		$requestParams['data'][] = $pListView->getSortby();
		$requestParams['data'] = array_merge($requestParams['data'], $pListView->getSortByUserValues());
		$requestParams['data'][] = 'preisAufAnfrage';
		$requestParams['data'][] = 'referenz';
		$requestParams['sortby'] = $pListView->getSortby();
		$requestParams['sortorder'] = $pListView->getSortorder();


		if ($this->getShowReferenceEstate() === DataListView::HIDE_REFERENCE_ESTATE) {
			$requestParams['filter']['referenz'][] = ['op' => '=', 'val' => 0];
		} elseif ($this->getShowReferenceEstate() === DataListView::SHOW_ONLY_REFERENCE_ESTATE) {
			$requestParams['filter']['referenz'][] = ['op' => '=', 'val' => 1];
		}

		if ($pListView instanceof DataListView && $pListView->getFilterId() !== 0) {
			$requestParams['filterid'] = $pListView->getFilterId();
		}

		return $requestParams;
	}
	/**
	 * @param int $currentPage
	 * @param bool $formatOutput
	 * @return array
	 * @throws UnknownViewException
	 */
	private function getEstateParameters(int $currentPage, bool $formatOutput)
	{
		$language = Language::getDefault();
		$pListView = $this->filterActiveInputFields($this->_pDataView);
		$filter = $this->getDefaultFilterBuilder()->buildFilter();

		if ($this->_filterAddressId != 0) {
			$addressList = $this->_pEnvironment->getAddressList();
			$addressList->fetchEstatesForAddressIds([$this->_filterAddressId]);
			$estateIds = $addressList->getEstateIdsForContact($this->_filterAddressId);
			$filter['Id'] = [["op" => "IN", "val" => $estateIds]];
		}

		$numRecordsPerPage = $this->getRecordsPerPage();

		$pFieldModifierHandler = new ViewFieldModifierHandler(
			$pListView->getFields(),
			onOfficeSDK::MODULE_ESTATE
		);

		$requestParams = [
			'data' => $pFieldModifierHandler->getAllAPIFields(),
			'filter' => $filter,
			'estatelanguage' => $language,
			'outputlanguage' => $language,
			'listlimit' => $numRecordsPerPage,
			'formatoutput' => $formatOutput,
			'addMainLangId' => true,
		];

		if ($pListView instanceof DataListView) {
			$requestParams['params_list_cache'] = $this->getEstateListParametersForCache($language, $formatOutput);
			$requestParams = array('listname' => $this->_pDataView->getName()) + $requestParams;
		}

		if (!$pListView->getRandom()) {
			$offset = ($currentPage - 1) * $numRecordsPerPage;
			$this->_currentEstatePage = $currentPage;
			$requestParams += [
				'listoffset' => $offset
			];
		}

		if ($this->enableShowPriceOnRequestText() && !isset($requestParams['data']['preisAufAnfrage'])) {
			$requestParams['data'][] = 'preisAufAnfrage';
		}
		if ($pListView->getName() === 'detail') {
			if ($this->getViewRestrict()) {
				$requestParams['filter']['referenz'][] = ['op' => '=', 'val' => 0];
			}
		} elseif ($this->getShowReferenceEstate() === DataListView::HIDE_REFERENCE_ESTATE) {
			$requestParams['filter']['referenz'][] = ['op' => '=', 'val' => 0];
		} elseif ($this->getShowReferenceEstate() === DataListView::SHOW_ONLY_REFERENCE_ESTATE) {
			$requestParams['filter']['referenz'][] = ['op' => '=', 'val' => 1];
		}

		$requestParams += $this->addExtraParams();
		if (isset($requestParams['georangesearch'])) {
			unset($requestParams['listname']);
			unset($requestParams['params_list_cache']);
		}
		return $requestParams;
	}
	/**
	 * @param string $addressId
	 * @return string
	 */
	public function getAddressLink(string $addressId): string
	{
		$addressList = $this->_pEnvironment->getAddressList();
		return $addressList->getAddressLink(($addressId));
	}

	/**
	 * @return array
	 */
	protected function addExtraParams(): array
	{
		$pListView = $this->_pDataView;
		$requestParams = [];

		if ($pListView->getSortby() !== '' && !$this->_pDataView->getRandom()) {
			$requestParams['sortby'] =  $pListView->getSortBy();
		}

		if ($pListView->getSortorder() !== '') {
			$requestParams['sortorder'] = $pListView->getSortorder();
		}

		if ($pListView instanceof DataListView && $pListView->getSortByTags() !== '' && $this->_pDataView->getSortBySetting() === DataListView::SHOW_MARKED_PROPERTIES_SORT) {
			$requestParams['sortby'] = $pListView->getSortByTags();
		}

		if ($pListView instanceof DataListView && $pListView->getSortByTagsDirection() !== '' && $this->_pDataView->getSortBySetting() === DataListView::SHOW_MARKED_PROPERTIES_SORT) {
			$requestParams['sortorder'] = $pListView->getSortByTagsDirection();
		}

		if ($pListView->getFilterId() !== 0) {
			$requestParams['filterid'] = $pListView->getFilterId();
		}

		// only do georange search if requested in listview configuration
		if (($pListView instanceof DataViewFilterableFields &&
			in_array(GeoPosition::FIELD_GEO_POSITION, $pListView->getFilterableFields(), true))) {
			$geoRangeSearchParameters = $this->getGeoSearchBuilder()->buildParameters();
			if ($geoRangeSearchParameters !== []) {
				$requestParams['georangesearch'] = $geoRangeSearchParameters;
			}
		}

		// only do georange search if requested in similar estate configuration
		if ($pListView instanceof DataViewSimilarEstates) {
			$geoRangeSearchParameters = $this->getGeoSearchBuilder()->buildParameters();

			if ($geoRangeSearchParameters !== []) {
				$requestParams['georangesearch'] = $geoRangeSearchParameters;
			}
		}

		return $requestParams;
	}

	/**
	 * @param array $estateResponseArray
	 * @return array Mapping: mainEstateId => multiLangId
	 */
	private function getEstateIdToForeignMapping($estateResponseArray): array
	{
		$estateIds = [];

		foreach ($estateResponseArray as $estate) {
			$elements = $estate['elements'];
			$estateMainId = $elements['mainLangId'] ?? $estate['id'];
			$estateIds[$estateMainId] = $estate['id'];
		}

		return $estateIds;
	}

	/**
	 * @param array $responseArrayContacts
	 * @param array $estateIds
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws API\ApiClientException
	 */
	private function collectEstateContactPerson($responseArrayContacts, array $estateIds)
	{
		$records = $responseArrayContacts[0]['elements'] ?? [];
		$allAddressIds = [];

		foreach ($records as $estateId => $addressIds) {
			$subjectEstateId = $estateIds[$estateId];
			$this->_estateContacts[$subjectEstateId] = $addressIds;
			$allAddressIds = array_unique(array_merge($allAddressIds, $addressIds));
		}

		$fields = $this->_pDataView->getAddressFields();

		if ($this->_pDataView instanceof DataDetailView && !empty($this->_pDataView->getContactImageTypes())) {
			if (in_array(ImageTypes::PASSPORTPHOTO, $this->_pDataView->getContactImageTypes()) && !in_array('imageUrl', $fields)) {
				$fields[] = 'imageUrl';
			}
			if (in_array(ImageTypes::BILDWEBSEITE, $this->_pDataView->getContactImageTypes())) {
				$fields[] = ImageTypes::BILDWEBSEITE;
			}
		}

		$defaultFields = ['defaultemail' => 'Email', 'defaultphone' => 'Telefon1', 'defaultfax' => 'Telefax1'];
		foreach ($defaultFields as $defaultField => $newField) {
			if (in_array($defaultField, $fields)) {
				$key = array_search($defaultField, $fields);
				unset($fields[$key]);
				if (!in_array($newField, $fields)) {
					$fields[$key] = $newField;
				}
			}
		}
		ksort($fields);

		if ($fields !== [] && $allAddressIds !== []) {
			if ($this->_pDataView instanceof DataDetailView && $this->_pDataView->getContactPerson() === DataDetailView::SHOW_MAIN_CONTACT_PERSON) {
				$allAddressIds = [$allAddressIds[0]];
			}

			$this->_pEnvironment->getAddressList()->loadAddressesById($allAddressIds, $fields);
		}
	}

	/**
	 * @param string $modifier
	 * @param bool $checkEstateIdRequestGuard
	 * @return ArrayContainerEscape
	 * @throws UnknownFieldException
	 */
	public function estateIterator($modifier = EstateViewFieldModifierTypes::MODIFIER_TYPE_DEFAULT, $checkEstateIdRequestGuard  = false)
	{
		global $numpages, $multipage, $more, $paged;

		if (
			null !== $this->_numEstatePages &&
			!$this->_pDataView->getRandom()
		) {
			$multipage = true;

			$paged = $this->_currentEstatePage;
			$more = true;
			$numpages = $this->_numEstatePages;
		}

		$pEstateFieldModifierHandler = $this->_pEnvironment->getViewFieldModifierHandler($this->_pDataView->getFields(), $modifier);

		$currentRecord = current($this->_records);
		next($this->_records);

		if (false === $currentRecord) {
			return false;
		}
		$this->_currentEstate['id'] = $currentRecord['id'];
		$recordElements = $currentRecord['elements'];
		$this->_currentEstate['mainId'] = $recordElements['mainLangId'] ??
			$this->_currentEstate['id'];
		$this->_currentEstate['title'] = $currentRecord['elements']['objekttitel'] ?? '';

		$recordModified = $pEstateFieldModifierHandler->processRecord($currentRecord['elements']);

		$fieldWaehrung = $this->_pEnvironment->getFieldnames()->getFieldInformation('waehrung', onOfficeSDK::MODULE_ESTATE);
		if (!empty($fieldWaehrung['permittedvalues']) && !empty($recordModified['waehrung']) && isset($recordModified['waehrung'])) {
			$recordModified['codeWaehrung'] = array_search($recordModified['waehrung'], $fieldWaehrung['permittedvalues']);
		}
		$recordRaw = $this->_recordsRaw[$this->_currentEstate['id']]['elements'] ?? [];

		if ($this->getShowEstateMarketingStatus()) {
			$pEstateStatusLabel = $this->_pEnvironment->getEstateStatusLabel();
			$recordModified['vermarktungsstatus'] = $pEstateStatusLabel->getLabel($recordRaw);
		}

		if ($this->getShowTotalCostsCalculator()) {
			$externalCommission = $this->getExternalCommission($recordRaw['aussen_courtage'] ?? '');
			$propertyTransferTax = $this->_pDataView->getPropertyTransferTax();
			if (!empty((float) $recordRaw['kaufpreis']) && !empty($recordRaw['bundesland']) && $externalCommission !== null) {
				$costsCalculator = $this->_pEnvironment->getContainer()->get(CostsCalculator::class);
				$this->_totalCostsData = $costsCalculator->getTotalCosts($recordRaw, $propertyTransferTax, $externalCommission);
			}
		}

		if ($modifier === EstateViewFieldModifierTypes::MODIFIER_TYPE_MAP && $this->_pDataView instanceof DataListView) {
			$recordModified['showGoogleMap'] = $this->getShowMapConfig();
		}

		if ($checkEstateIdRequestGuard && $this->_pWPOptionWrapper->getOption('onoffice-settings-title-and-description') == 0) {
			add_action('wp_head', function () use ($recordModified) {
				echo '<meta name="description" content="' . esc_attr(isset($recordModified["objektbeschreibung"])
					? $this->limit_characters($recordModified["objektbeschreibung"]) : null) . '" />';
			});
		}

		$WPPluginChecker = new WPPluginChecker;
		$isSEOPluginActive = $WPPluginChecker->isSEOPluginActive();
		$openGraphStatus = $this->_pWPOptionWrapper->getOption('onoffice-settings-opengraph');
		$twitterCardsStatus = $this->_pWPOptionWrapper->getOption('onoffice-settings-twittercards');
		if ($checkEstateIdRequestGuard && $openGraphStatus && !$isSEOPluginActive) {
			$this->addMetaTags(GenerateMetaDataSocial::OPEN_GRAPH_KEY, $recordModified);
		}
		if ($checkEstateIdRequestGuard && $twitterCardsStatus && !$isSEOPluginActive) {
			$this->addMetaTags(GenerateMetaDataSocial::TWITTER_KEY, $recordModified);
		}

		if (!empty($recordModified['multiParkingLot'])) {
			$parking = $this->_pEnvironment->getContainer()->get(FieldParkingLot::class);
			$recordModified['multiParkingLot'] = $parking->renderParkingLot($recordModified, $recordRaw['waehrung'] ?? '');
		}

		$recordModified = new ArrayContainerEscape($recordModified);

		if ($recordRaw['preisAufAnfrage'] === DataListView::SHOW_PRICE_ON_REQUEST) {
			if ($this->enableShowPriceOnRequestText()) {
				$priceFields = $this->_pDataView->getListFieldsShowPriceOnRequest();

				foreach ($priceFields as $priceField) {
					$this->displayTextPriceOnRequest($recordModified, $priceField);
				}
				$this->_totalCostsData = [];
			}
		}
		// do not show priceOnRequest as single Field
		unset($recordModified['preisAufAnfrage']);

		return $recordModified;
	}

	/**
	 * @param string $externalCommission
	 * @return mixed
	 */
	private function getExternalCommission(string $externalCommission)
	{
		if (preg_match('/(\d+[,]?\d*)\s*%/', $externalCommission, $matches)) {
			return floatval(str_replace(',', '.', $matches[1]));
		}

		return null;
	}

	/**
	 * @return array
	 */
	public function getTotalCostsData(): array
	{
		return $this->_totalCostsData;
	}

	/**
	 * @param ArrayContainerEscape $recordModified
	 * @param string $field
	 */
	private function displayTextPriceOnRequest($recordModified, $field)
	{
		if (!empty($recordModified[$field])) {
			$recordModified[$field] = esc_html__('Price on request', 'onoffice-for-wp-websites');
		}
	}

	public function custom_pre_get_document_title($title_parts_array, $recordModified)
	{
		if (isset($recordModified["objekttitel"])) {
			$title_parts_array = $recordModified["objekttitel"];
		}

		return $title_parts_array;
	}

	/**
	 * Set limit character for SEO meta description
	 * @param string $text
	 * @return string
	 */
	private function limit_characters(string $text): string
	{
		if (strlen($text) > self::DEFAULT_LIMIT_CHARACTER_DESCRIPTION) {
			$shortenedText = substr($text, 0, self::DEFAULT_LIMIT_CHARACTER_DESCRIPTION);
			if (substr($text, self::DEFAULT_LIMIT_CHARACTER_DESCRIPTION, 1) != ' ') {
				$shortenedText = substr($shortenedText, 0, strrpos($shortenedText, ' '));
			}
			$text = $shortenedText;
		}

		return $text;
	}

	public function getRawValues(): ArrayContainerEscape
	{
		return new ArrayContainerEscape($this->_recordsRaw);
	}

	/**
	 * @return int
	 * @throws API\ApiClientException
	 */
	public function getEstateOverallCount()
	{
		return $this->_pApiClientAction->getResultMeta()['cntabsolute'];
	}

	/**
	 * @param string $field
	 * @return string
	 */
	public function getFieldLabel($field): string
	{
		$recordType = onOfficeSDK::MODULE_ESTATE;
		$pLanguage = $this->_pEnvironment->getContainer()->get( Language::class )->getLocale();

		try {
			$label = $this->_pFieldsCollection->getFieldByModuleAndName($recordType, $field)->getLabel();
		} catch (UnknownFieldException $pE) {
			$label = $this->getEnvironment()->getFieldnames()->getFieldLabel($field, $recordType);
		}

		if ($this->_pDataView instanceof DataDetailView || $this->_pDataView instanceof DataViewSimilarEstates) {
			$dataView = $this->_pDataView->getCustomLabels();
			if (!empty($dataView[$field][$pLanguage])) {
				$label = $dataView[$field][$pLanguage];
			}
		}

		$fieldNewName = esc_html($label);
		return $fieldNewName;
	}

	/**
	 * @param string $field
	 * @return array
	 */
	public function getPermittedValues(string $field): array
	{
		$recordType = onOfficeSDK::MODULE_ESTATE;
		if (!$this->_pFieldsCollection->containsFieldByModule($recordType, $field)) {
			return [];
		}

		$values = $this->_pFieldsCollection->getFieldByModuleAndName($recordType, $field)->getPermittedvalues();

		if ($field === 'energyClass') {
			$preferredOrder = ['A+', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
			usort($values, function ($a, $b) use ($preferredOrder) {
				$posA = array_search($a, $preferredOrder);
				$posB = array_search($b, $preferredOrder);
				return $posA - $posB;
			});
		}

		return $values;
	}

	/**
	 * @return string
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function getEstateLink(): string
	{
		$pageId = $this->_pEnvironment->getDataDetailViewHandler()
			->getDetailView()->getPageId();

		$fullLink = '#';
		if ($pageId !== 0) {
			$estate   = $this->_currentEstate['mainId'];
			$title    = $this->_currentEstate['title'] ?? '';
			$url      = get_page_link($pageId);
			$fullLink = $this->_pLanguageSwitcher->createEstateDetailLink($url, $estate, $title);

			$fullLinkElements = parse_url($fullLink);
			if (empty($fullLinkElements['query'])) {
				$fullLink .= '/';
			}
		}

		return $fullLink;
	}

	/**
	 * @param array $types
	 * @return array
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
			$estateFiles[] = $image['id'];
		}

		return $estateFiles;
	}

	/**
	 * Not supported in list view
	 * @return array Returns an array if Movie Links are active and displayed as Link
	 */
	public function getEstateMovieLinks(): array
	{
		return [];
	}

	/**
	 * Not supported in list view
	 * @param array $options
	 * @return array
	 */
	public function getMovieEmbedPlayers(array $options = []): array
	{
		return [];
	}

	/**
	 * @param int $imageId
	 * @param array $options
	 * @return string
	 */
	public function getEstatePictureUrl($imageId, array $options = null)
	{
		$currentEstate = $this->_currentEstate['id'];
		return $this->_pEstateFiles->getEstateFileUrl($imageId, $currentEstate, $options);
	}

	/**
	 * @param int $imageId
	 * @return string
	 */
	public function getEstatePictureTitle($imageId)
	{
		$currentEstate = $this->_currentEstate['id'];
		return $this->_pEstateFiles->getEstatePictureTitle($imageId, $currentEstate);
	}

	/**
	 * @param int $imageId
	 * @param int $breakpoint
	 * @param float|null $width
	 * @param float|null $height
	 * @param bool $maxWidth
	 * @return string
	 */
	public function getResponsiveImageSource(int $imageId, int $breakpoint, float $width = null, float $height = null, bool $maxWidth = false)
	{
		$sourceTag = '<source media="(' . ($maxWidth ? 'max-width:' : 'min-width:') . $breakpoint . 'px)" srcset="';
		$pictureOptions1 = null;
		$pictureOptions15 = null;
		$pictureOptions2 = null;
		$pictureOptions3 = null;

		if (isset($width) || isset($height)) {
			$pictureOptions1 = ['width' => isset($width) ? $width : null, 'height' => isset($height) ? $height : null];
			$pictureOptions15 = ['width' => isset($width) ? round($width * 1.5) : null, 'height' => isset($height) ? round($height * 1.5) : null];
			$pictureOptions2 = ['width' => isset($width) ? round($width * 2) : null, 'height' => isset($height) ? round($height * 2) : null];
			$pictureOptions3 = ['width' => isset($width) ?  round($width * 3) : null, 'height' => isset($height) ? round($height * 3) : null];
		}

		return  $sourceTag .
			$this->getEstatePictureUrl($imageId, $pictureOptions1) . ' 1x,' .
			$this->getEstatePictureUrl($imageId, $pictureOptions15) . ' 1.5x,' .
			$this->getEstatePictureUrl($imageId, $pictureOptions2) . ' 2x,' .
			$this->getEstatePictureUrl($imageId, $pictureOptions3) . ' 3x">';
	}

	/**
	 * @param int $imageId
	 * @return string
	 */
	public function getEstatePictureText($imageId)
	{
		$currentEstate = $this->_currentEstate['id'];
		return $this->_pEstateFiles->getEstatePictureText($imageId, $currentEstate);
	}

	/**
	 * @param int $imageId
	 * @return array
	 */
	public function getEstatePictureValues($imageId)
	{
		$currentEstate = $this->_currentEstate['id'];
		return $this->_pEstateFiles->getEstatePictureValues($imageId, $currentEstate);
	}

	/**
	 *
	 * @return bool
	 * @throws DependencyException
	 * @throws NotFoundException
	 */

	public function hasDetailView(): bool
	{
		return $this->_pEnvironment->getDataDetailViewHandler()->getDetailView()->hasDetailView();
	}

	/**
	 *
	 * @return bool
	 * @throws DependencyException
	 * @throws NotFoundException
	 */

	public function getViewRestrict(): bool
	{
		return $this->_pEnvironment->getDataDetailViewHandler()->getDetailView()->getViewRestrict();
	}

	/**
	 * @return array
	 */
	public function getEstateContactIds(): array
	{
		$recordId = $this->_currentEstate['id'];
		return $this->_estateContacts[$recordId] ?? [];
	}

	/**
	 * @return array
	 */
	public function getEstateContacts()
	{
		$addressIds = $this->getEstateContactIds();
		$pAddressList = $this->_pEnvironment->getAddressList();
		$result = [];

		foreach ($addressIds as $addressId) {
			$currentAddressData = $pAddressList->getAddressById($addressId);
			$pArrayContainerCurrentAddress = new ArrayContainerEscape($currentAddressData);
			$pArrayContainerCurrentAddress['imageAlt'] = $pAddressList->generateImageAlt($addressId);

			if (!empty($pArrayContainerCurrentAddress['bildWebseite'])) {
				$pArrayContainerCurrentAddress['imageUrl'] = $pArrayContainerCurrentAddress['bildWebseite'];
				unset($pArrayContainerCurrentAddress['bildWebseite']);
			}

			$result[] = $pArrayContainerCurrentAddress;
		}

		return $result;
	}

	/**
	 * @return int
	 */
	public function getCurrentEstateId(): int
	{
		return $this->_currentEstate['id'];
	}

	/**
	 * @return int
	 */
	public function getCurrentMultiLangEstateMainId()
	{
		return $this->_currentEstate['mainId'];
	}

	/**
	 * @return string
	 * @throws API\APIEmptyResultException
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws HttpFetchNoResultException
	 * @throws UnknownViewException
	 */
	public function getEstateUnits()
	{
		$estateId = $this->getCurrentMultiLangEstateMainId();
		$htmlOutput = '';

		if ($this->_unitsViewName != null) {
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
	 * @return string
	 */
	public function getDocument()
	{
		$document = '';
		if ($this->_pDataView->getExpose() !== '') {
			$documentlink = home_url('document-pdf/' . $this->_pDataView->getName()
				. '/' . $this->getCurrentMultiLangEstateMainId());
			$document = esc_url($documentlink);
		}
		return $document;
	}

	/**
	 * @return string[] An array of visible fields
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownFieldException
	 */
	public function getVisibleFilterableFields(): array
	{
		$pContainer = $this->_pEnvironment->getContainer();
		$pFieldsCollection = new FieldsCollection();
		$pFieldsCollectionBuilderShort = $this->_pEnvironment->getFieldsCollectionBuilderShort();
		$pFieldsCollectionBuilderShort->addFieldsAddressEstate($pFieldsCollection);
		$pFieldsCollectionBuilderShort->addFieldsAddressEstateWithRegionValues($pFieldsCollection);
		if (!empty($this->_pDataView->getConvertTextToSelectForCityField())) {
			$pFieldsCollectionBuilderShort->addFieldEstateCityValues($pFieldsCollection, $this->getShowReferenceEstate());
		}
		$pFieldsCollection->merge(new FieldModuleCollectionDecoratorGeoPositionFrontend(new FieldsCollection));
		$pFieldsCollectionFieldDuplicatorForGeoEstate =
			$pContainer->get(FieldsCollectionFieldDuplicatorForGeoEstate::class);
		$pFieldsCollectionFieldDuplicatorForGeoEstate->duplicateFields($pFieldsCollection);
		$pDataView = $this->filterActiveInputFilterableFields($this->_pDataView);
		/** @var DistinctFieldsHandler $pDistinctFieldsHandler */
		$pDistinctFieldsHandler = $pContainer->get(DistinctFieldsHandler::class);
		$pFieldsCollection = $pDistinctFieldsHandler->modifyFieldsCollectionForEstate($pDataView, $pFieldsCollection);

		$fieldsValues = $pContainer->get(OutputFields::class)
			->getVisibleFilterableFields(
				$pDataView,
				$pFieldsCollection,
				new GeoPositionFieldHandler
			);

		if (array_key_exists("radius", $fieldsValues)) {
			$geoFields = $pDataView->getGeoFields();
			$fieldsValues["radius"] = !empty($geoFields['radius']) ? $geoFields['radius'] : NULL;
		}
		$result = [];
		foreach ($fieldsValues as $field => $value) {
			$result[$field] = $pFieldsCollection->getFieldByKeyUnsafe($field)
				->getAsRow();
			$result[$field]['name'] = $field;
			$result[$field]['value'] = $value;
			$result[$field]['label'] = $this->getFieldLabel($field);
			if (
				in_array($field, InputVariableReaderFormatter::APPLY_THOUSAND_SEPARATOR_FIELDS) &&
				!empty(get_option('onoffice-settings-thousand-separator'))
			) {
				$result[$field]['is-apply-thousand-separator'] = true;
			}
		}
		return $result;
	}

	/**
	 * @return bool
	 */
	private function getShowMapConfig(): bool
	{
		if ($this->_pDataView instanceof DataListView) {
			return $this->_pDataView->getShowMap();
		}

		return false;
	}


	/**
	 * @param string $keySocial
	 * @param array $recordModified
	 */
	private function addMetaTags(string $keySocial, array $recordModified)
	{
		$pGenerateMetaDataSocial = $this->_pEnvironment->getContainer()->get(GenerateMetaDataSocial::class);
		$estateInformationForMetaData = $this->getEstateInformationForMetaData($recordModified);
		$metaData = [];

		if ($keySocial === GenerateMetaDataSocial::OPEN_GRAPH_KEY) {
			$metaData = $pGenerateMetaDataSocial->generateMetaDataSocial($estateInformationForMetaData, [GenerateMetaDataSocial::OPEN_GRAPH_KEY]);
		}

		if ($keySocial === GenerateMetaDataSocial::TWITTER_KEY) {
			$metaData = $pGenerateMetaDataSocial->generateMetaDataSocial($estateInformationForMetaData, [GenerateMetaDataSocial::TWITTER_KEY]);
		}

		if (!empty($metaData)) {
			$this->addMetaTagsToWpHead($metaData, $keySocial);
		}
	}

	/**
	 * @param array $metaData
	 * @param string $keySocial
	 * @return void
	 */
	private function addMetaTagsToWpHead(array $metaData, string $keySocial)
	{
		add_action('wp_head', function () use ($metaData, $keySocial) {
			foreach ($metaData as $metaKey => $metaValue) {
				if ($keySocial === GenerateMetaDataSocial::TWITTER_KEY) {
					echo '<meta name="' . GenerateMetaDataSocial::TWITTER_KEY . ':' . esc_html($metaKey) . '" content="' . esc_attr($metaValue) . '">';
				} elseif ($keySocial === GenerateMetaDataSocial::OPEN_GRAPH_KEY) {
					echo '<meta property="' . GenerateMetaDataSocial::OPEN_GRAPH_KEY . ':' . esc_html($metaKey) . '" content="' . esc_attr($metaValue) . '">';
				}
			}
		}, 1);
	}

	/**
	 * @param array $recordModified
	 * @return array
	 */
	private function getEstateInformationForMetaData(array $recordModified): array
	{
		$image = $this->getImageForMetaData();
		$title = $recordModified['objekttitel'] ?? '';
		$url = $this->getEstateLink() ?? '';
		$description = $recordModified['objektbeschreibung'] ?? '';

		return [
			'title' => $title,
			'url' => $url,
			'description' => $description,
			'image' => $image
		];
	}

	/**
	 * @return string
	 */
	private function getImageForMetaData(): string
	{
		$estatePicturesByEstateId = $this->_pEstateFiles->getEstatePictures($this->_currentEstate['id']);

		if (empty($estatePicturesByEstateId)) {
			return '';
		}

		$estatePictureUrl = '';
		foreach ($estatePicturesByEstateId as $key => $estatePicture) {
			if ($estatePicture['type'] === ImageTypes::TITLE) {
				$estatePictureUrl = $estatePicture['url'];
				break;
			} elseif ($key === reset($estatePicturesByEstateId)) {
				$estatePictureUrl = $estatePicture['url'];
			}
		}

		return $estatePictureUrl;
	}

	/**
	 *
	 * @param $field
	 * @return string
	 */

	 public function getFieldInformation(string $field): array
	 {
		 return $this->getEnvironment()->getFieldnames()->getFieldInformation($field, onOfficeSDK::MODULE_ESTATE);
	 }

	/**
	 *
	 */
	public function resetEstateIterator()
	{
		reset($this->_records);
	}

	/**
	 * @return bool
	 */
	public function getShowEstateMarketingStatus(): bool
	{
		return $this->_pDataView instanceof DataListView &&
			$this->_pDataView->getShowStatus();
	}

	/**
	 * @return bool
	 */
	public function getShowReferenceStatus(): bool
	{
		if ($this->_pDataView instanceof DataListView) {
			return $this->_pDataView->getShowReferenceStatus();
		} else {
			return true;
		}
	}

	/**
	 * @return bool
	 */
	public function getShowReferenceEstate(): string
	{
		if ( $this->_pDataView instanceof DataListView || $this->_pDataView instanceof DataViewSimilarEstates) {
			return $this->_pDataView->getShowReferenceEstate();
		}

		return DataListView::HIDE_REFERENCE_ESTATE;
	}

	/**
	 * @return array
	 */
	public function getEstateIds(): array
	{
		return array_column($this->_records, 'id');
	}

	/** @return array */
	public function getAddressFields(): array
	{
		return $this->_pDataView->getAddressFields();
	}

	/** @return bool */
	private function enableShowPriceOnRequestText()
	{
		if ($this->_pDataView instanceof DataListView || $this->_pDataView instanceof DataDetailView || $this->_pDataView instanceof DataViewSimilarEstates) {
			return $this->_pDataView->getShowPriceOnRequest();
		} else {
			return false;
		}
	}

	/** @return bool */
	public function getShowEnergyCertificate(): bool
	{
		return $this->_pDataView instanceof DataDetailView && $this->_pDataView->getShowEnergyCertificate();
	}

	/** @return EstateFiles */
	protected function getEstateFiles()
	{
		return $this->_pEstateFiles;
	}

	/** @return DataView */
	public function getDataView(): DataView
	{
		return $this->_pDataView;
	}

	/**
	 * @return DefaultFilterBuilder
	 * @throws UnknownViewException
	 */
	public function getDefaultFilterBuilder(): DefaultFilterBuilder
	{
		return $this->_pEnvironment->getDefaultFilterBuilder();
	}

	/** @param DefaultFilterBuilder $pDefaultFilterBuilder */
	public function setDefaultFilterBuilder(DefaultFilterBuilder $pDefaultFilterBuilder)
	{
		$this->_pEnvironment->setDefaultFilterBuilder($pDefaultFilterBuilder);
	}

	/** @return string */
	public function getUnitsViewName()
	{
		return $this->_unitsViewName;
	}

	/** @param string $unitsViewName */
	public function setUnitsViewName($unitsViewName)
	{
		$this->_unitsViewName = $unitsViewName;
	}

	/** @param string $filterAddressId */
	public function setFilterAddressId($filterAddressId)
	{
		$this->_filterAddressId = $filterAddressId;
	}

	/** @return GeoSearchBuilder */
	public function getGeoSearchBuilder(): GeoSearchBuilder
	{
		return $this->_pGeoSearchBuilder;
	}

	/** @param GeoSearchBuilder $pGeoSearchBuilder */
	public function setGeoSearchBuilder(GeoSearchBuilder $pGeoSearchBuilder)
	{
		$this->_pGeoSearchBuilder = $pGeoSearchBuilder;
	}

	/** @return bool */
	public function getFormatOutput(): bool
	{
		return $this->_formatOutput;
	}

	/** @param bool $formatOutput */
	public function setFormatOutput(bool $formatOutput)
	{
		$this->_formatOutput = $formatOutput;
	}

	/** @return EstateListEnvironment */
	public function getEnvironment(): EstateListEnvironment
	{
		return $this->_pEnvironment;
	}

	/**
	 * @return mixed
	 */
	public function getListViewId()
	{
		if ($this->getDataView() instanceof DataListView) {
			return $this->getDataView()->getId();
		}

		return 'estate_detail';
	}

	/**
	 * @return bool
	 */
	public function getShowTotalCostsCalculator(): bool
	{
		if ($this->_pDataView instanceof DataDetailView) {
			return $this->_pDataView->getShowTotalCostsCalculator();
		}

		return false;
	}
}
