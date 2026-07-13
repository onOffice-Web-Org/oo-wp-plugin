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

if ( ! defined( 'ABSPATH' ) ) exit;

use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\SDK\Exception\HttpFetchNoResultException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\API\ApiClientException;
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
use onOffice\WPlugin\Field\CostsCalculator;
use onOffice\WPlugin\Filter\DefaultFilterBuilderDetailViewAddress;

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

	/** @var bool|null */
	private $_hasPriceOnRequestField = null;

	private $_geoFilter = null;


	/**
	 * @param DataView $pDataView
	 * @param EstateListEnvironment $pEnvironment
	 * @param Container|null $pContainer If null, a new container will be built (legacy).
	 *        Pass the global DI container from plugin.php for better performance.
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function __construct(DataView $pDataView, EstateListEnvironment $pEnvironment = null, Container $pContainer = null)
	{
		if ($pContainer === null) {
			$pContainer = self::getGlobalContainer();
		}
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
	 * Returns a shared global DI container instance.
	 * Avoids rebuilding the container multiple times per request.
	 *
	 * @return Container
	 */
	private static function getGlobalContainer(): Container
	{
		static $pGlobalContainer = null;
		if ($pGlobalContainer === null) {
			$pContainerBuilder = new ContainerBuilder;
			$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
			$pGlobalContainer = $pContainerBuilder->build();
		}
		return $pGlobalContainer;
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
			// --- BATCHED Phase 2: Queue idsfromrelation + estatepictures, send together ---
			$pSDKWrapper = $this->_pEnvironment->getSDKWrapper();

			// Queue idsfromrelation (contact persons)
			$pRelationAction = new APIClientActionGeneric($pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'idsfromrelation');
			$pRelationAction->setParameters([
				'parentids' => array_keys($estateIds),
				'relationtype' => onOfficeSDK::RELATION_TYPE_CONTACT_BROKER,
			]);
			$pRelationAction->addRequestToQueue();

			// Queue estatepictures
			$this->_pEstateFiles = $this->_pEnvironment->getEstateFiles();
			$pPicturesAction = null;
			if (count($fileCategories) > 0) {
				$pPicturesAction = new APIClientActionGeneric($pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'estatepictures');
				$pPicturesAction->setParameters([
					'estateids' => array_values($estateIds),
					'categories' => $fileCategories,
					'language' => Language::getDefault(),
				]);
				$pPicturesAction->addRequestToQueue();
			}

			// Send idsfromrelation + estatepictures in ONE HTTP request
			$pSDKWrapper->sendRequests();

			// Process idsfromrelation results
			$this->collectEstateContactPerson($pRelationAction->getResultRecords(), $estateIds);

			// Process estatepictures results
			if ($pPicturesAction !== null && $pPicturesAction->getResultStatus()) {
				$this->_pEstateFiles->collectEstateFilesFromRecords($pPicturesAction->getResultRecords());
			}

			// --- BATCHED Phase 3: file GETs (already batched internally) ---
			try {
				$this->_pEstateFiles->getFilesByEstateIds($estateIds, $pSDKWrapper);
			} catch (\onOffice\SDK\Exception\HttpFetchNoResultException $e) {
				// Estate files could not be fetched — continue without images
			}
		}

		if ($pDataListView->getRandom()) {
			$this->_pEnvironment->shuffle($this->_records);
		}

		$this->_numEstatePages = $this->getNumEstatePages();
		$this->resetEstateIterator();
		$this->buildFieldsCollectionForEstate();
	}

	/**
	 * @return EstateList
	 * @throws API\APIEmptyResultException
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownViewException
	 * @throws HttpFetchNoResultException
	 * @throws API\ApiClientException
	 */
	public function getEstateListForMap(): EstateList
	{
		$pEstateListForMap = clone $this;
		$pDataViewForMap = clone $this->_pDataView;
		$estateCount = $this->getEstateOverallCount();

		if (method_exists($pDataViewForMap, 'setRecordsPerPage') && $estateCount > 0) {
			$pDataViewForMap->setRecordsPerPage($estateCount);
		}

		$pEstateListForMap->_pDataView = $pDataViewForMap;
		$pEstateListForMap->loadEstatesForMap(1);
		$pEstateListForMap->resetEstateIterator();

		return $pEstateListForMap;
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
		if ($this->hasPriceOnRequestField()) {
			$estateParametersRaw['data'][] = 'preisAufAnfrage';
		}
		$estateParametersRaw['data'][] = 'virtualAddress';
		$estateParametersRaw['data'][] = 'provisionsfrei';
		$estateParametersRaw['data'][] = 'nutzungsart';

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

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Geo search pagination
		if (isset($_GET['geo_search'])) {
			$perPage = $this->getRecordsPerPage();
			$offset = ($currentPage - 1) * $perPage;
			$this->_records = array_slice($this->_records, $offset, $perPage);
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
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
	 * @param int $currentPage
	 * @throws UnknownViewException
	 * @throws API\ApiClientException
	 */
	private function loadEstatesForMap(int $currentPage = 1)
	{
		$this->_pEnvironment->getFieldnames()->loadLanguage();

		$this->loadRecordsForMap($currentPage);

		$this->resetEstateIterator();
	}

	/**
	 * @param int $currentPage
	 * @throws ApiClientException
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownViewException
	 */
	private function loadRecordsForMap(int $currentPage)
	{
		$numRecordsPerPage = $this->getRecordsPerPage();

		if ($numRecordsPerPage > 500) {
			$allRecords = [];
			$allRecordsRaw = [];
			$totalFetched = 0;

			do {
				$offset = $totalFetched;
				$requestLimit = min(500, $numRecordsPerPage - $totalFetched);

				$estateParameters = $this->getEstateParametersForMap($currentPage, $this->_formatOutput, $offset, $requestLimit);
				$this->_pApiClientAction->setParameters($estateParameters);
				$this->_pApiClientAction->addRequestToQueue();

				$estateParametersRaw = $this->getEstateParametersForMap($currentPage, false, $offset, $requestLimit);
				$estateParametersRaw['data'] = array_unique($estateParametersRaw['data']);

				$pApiClientActionRawValues = clone $this->_pApiClientAction;
				$pApiClientActionRawValues->setParameters($estateParametersRaw);
				$pApiClientActionRawValues->addRequestToQueue();

				$this->_pEnvironment->getSDKWrapper()->sendRequests();

				$records = $this->_pApiClientAction->getResultRecords();
				$recordsRaw = $pApiClientActionRawValues->getResultRecords();

				$allRecords = array_merge($allRecords, $records);
				if (!empty($recordsRaw)) {
					$allRecordsRaw = array_merge($allRecordsRaw, array_combine(array_column($recordsRaw, 'id'), $recordsRaw));
				}

				$totalFetched += count($records);
			} while (count($records) == 500 && $totalFetched < $numRecordsPerPage);

			$this->_records = $allRecords;
			$this->_recordsRaw = $allRecordsRaw;
		} else {
			$estateParameters = $this->getEstateParametersForMap($currentPage, $this->_formatOutput);
			$this->_pApiClientAction->setParameters($estateParameters);
			$this->_pApiClientAction->addRequestToQueue();

			$estateParametersRaw = $this->getEstateParametersForMap($currentPage, false);
			$estateParametersRaw['data'] = array_unique($estateParametersRaw['data']);

			$pApiClientActionRawValues = clone $this->_pApiClientAction;
			$pApiClientActionRawValues->setParameters($estateParametersRaw);
			$pApiClientActionRawValues->addRequestToQueue();

			$this->_pEnvironment->getSDKWrapper()->sendRequests();

			$this->_records = $this->_pApiClientAction->getResultRecords();
			$recordsRaw = $pApiClientActionRawValues->getResultRecords();
			$this->_recordsRaw = !empty($recordsRaw) ? array_combine(array_column($recordsRaw, 'id'), $recordsRaw) : [];
		}
	}

	/**
	 * @param int $currentPage
	 * @param bool $formatOutput
	 * @param int $offset
	 * @param int|null $requestLimit
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownViewException
	 */
	private function getEstateParametersForMap(int $currentPage, bool $formatOutput, int $offset = 0, ?int $requestLimit = null)
	{
		$language = Language::getDefault();
		$pListView = $this->filterActiveInputFields($this->_pDataView);
		$filter = $this->getDefaultFilterBuilder()->buildFilter();


		$numRecordsPerPage = $this->getRecordsPerPage();

		if ($requestLimit !== null && $numRecordsPerPage > 500) {
			$listLimit = $requestLimit;
		} else {
			$listLimit = $numRecordsPerPage;
		}

		$mapFields = [
			'breitengrad',
			'laengengrad',
			'objekttitel',
			'strasse',
			'hausnummer',
			'plz',
			'ort',
			'land',
			'virtualAddress',
            'referenz',
		];

		$requestParams = [
			'data' => $mapFields,
			'filter' => $filter,
			'estatelanguage' => $language,
			'outputlanguage' => $language,
			'listlimit' => $listLimit,
			'formatoutput' => $formatOutput,
			'addMainLangId' => true,
		];

		if ($pListView instanceof DataListView) {
			$requestParams = array('listname' => $this->_pDataView->getName()) + $requestParams;
		}

		if (!$pListView->getRandom()) {
			if ($offset > 0 || $requestLimit !== null) {
				$calculatedOffset = $offset;
			} else {
				$calculatedOffset = ($currentPage - 1) * $numRecordsPerPage;
			}
			$this->_currentEstatePage = $currentPage;
			$requestParams += [
				'listoffset' => $calculatedOffset
			];
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

		return $requestParams;
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

        if (!empty($this->_filterAddressId)) {
            $addressList = $this->_pEnvironment->getAddressList();
            $addressList->fetchEstatesForAddressIds([$this->_filterAddressId]);
            $estateIds = $addressList->getEstateIdsForContact($this->_filterAddressId);
            $filter['Id'] = [["op" => "IN", "val" => $estateIds]];
        }

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
				if ($this->hasPriceOnRequestField()) {
					$requestParams['data'][] = 'preisAufAnfrage';
				}
				if (in_array('multiParkingLot', $this->_pDataView->getFields())) {
					$requestParams['data'][] = 'waehrung';
				}
			}
			if ($this->enableShowPriceOnRequestText() && $this->hasPriceOnRequestField() && !in_array('preisAufAnfrage', $requestParams['data'], true)) {
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

		if (in_array('regionaler_zusatz', $inputs->getFields(), true)) {
			$pFieldBuilderShort->addFieldsAddressEstateWithRegionValues($pFieldsCollection);
		}

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

		if (in_array('regionaler_zusatz', $inputs->getFilterableFields(), true)) {
			$pFieldBuilderShort->addFieldsAddressEstateWithRegionValues($pFieldsCollection);
		}

		foreach ($inputs->getFilterableFields() as $name) {
			if ($pFieldsCollection->containsFieldByModule($recordType, $name)) {
				$activeInputs[] = $name;
			}
		}
		$inputs->setFilterableFields($activeInputs);

		return $inputs;
	}

	/**
	 * @param string $lang
	 * @param bool $formatOutput
	 * @return array
	 * @throws UnknownViewException
	 */
	public function getEstateListParametersForCache (bool $formatOutput, ?string $lang = null)
	{
		$pListView = $this->filterActiveInputFields($this->_pDataView);
		$pFieldModifierHandler = new ViewFieldModifierHandler($pListView->getFields(), onOfficeSDK::MODULE_ESTATE);

		$lang = $lang ?? Language::getDefault();

		$filter = $this->getDefaultFilterBuilder()->getDefaultFilter();
		$fields = $pFieldModifierHandler->getAllAPIFields();

		if($formatOutput === false) {
			$fields = array_merge(
				$fields,
				$this->_pEnvironment->getEstateStatusLabel()->getFieldsByPrio()
			);
		}

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
		if ($this->hasPriceOnRequestField()) {
			$requestParams['data'][] = 'preisAufAnfrage';
		}
		$requestParams['data'][] = 'provisionsfrei';
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
		
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Geo search is a public filter, no nonce needed
		$hasGeoSearch = false;
		if ( isset( $_GET['geo_search'] ) ) {
			$geoSearch = sanitize_text_field( wp_unslash( $_GET['geo_search'] ) );
			$geoCoords = explode( ',', $geoSearch );
			if ( count( $geoCoords ) === 2 ) {
				$filter['geo'][0]['loc'] = $geoSearch;
				$hasGeoSearch = true;
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ($hasGeoSearch) {
			$numRecordsPerPage = 500;
		}

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
			$requestParams['params_list_cache'] = $this->getEstateListParametersForCache($formatOutput, $language);
			$requestParams = array('listname' => $this->_pDataView->getName()) + $requestParams;
		}

		if (!$pListView->getRandom()) {
			$offset = $hasGeoSearch ? 0 : ($currentPage - 1) * $numRecordsPerPage;
			$this->_currentEstatePage = $currentPage;
			$requestParams += [
				'listoffset' => $offset
			];
		}

		if ($this->enableShowPriceOnRequestText() && $this->hasPriceOnRequestField() && !in_array('preisAufAnfrage', $requestParams['data'], true)) {
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
		$filter = $this->getDefaultFilterBuilder()->buildFilter();

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

		if (
			in_array($requestParams['sortby'], $pListView->getListFieldsShowPriceOnRequest()) &&
			$this->hasPriceOnRequestField()
		) {
			$sortKey = $requestParams['sortby'];
			$sortOrder = $requestParams['sortorder'];

			$requestParams['sortby'] = ['preisAufAnfrage' => 'ASC', $sortKey => $sortOrder];
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

		if ($this->_pDataView instanceof DataListView) {
			$fields = ['Name', 'Vorname', 'imageUrl'];
		}

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

			try {
				$addressList = $this->_pEnvironment->getAddressList();

				$pDefaultFilterBuilder = new DefaultFilterBuilderDetailViewAddress();
				$addressList->setDefaultFilterBuilder($pDefaultFilterBuilder);
				$addressList->loadBrokerAddressesById($allAddressIds, $fields, false);
			} catch (API\ApiClientException $exception) {
				error_log('onOffice: Address list data unavailable: ' . $exception->getMessage());
			}
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
		$recordRaw = $this->_recordsRaw[$this->_currentEstate['id']]['elements'] ?? [];

		if ($this->getShowEstateMarketingStatus()) {
			$pEstateStatusLabel = $this->_pEnvironment->getEstateStatusLabel();
			$recordModified['vermarktungsstatus'] = $pEstateStatusLabel->getLabel($recordRaw);
		}

		if ($this->getShowTotalCostsCalculator()) {
			$externalCommission = $this->getExternalCommission($recordRaw['aussen_courtage'] ?? '');
			$propertyTransferTax = $this->_pDataView->getPropertyTransferTax();
		
			if (!empty((float) $recordRaw['kaufpreis']) && !empty($recordRaw['bundesland'])) {
				$costsCalculator = $this->_pEnvironment->getContainer()->get(CostsCalculator::class);
		
				
				$this->_totalCostsData = $costsCalculator->getTotalCosts($recordRaw, $propertyTransferTax, $externalCommission);
				
			}
		}

		if ($modifier === EstateViewFieldModifierTypes::MODIFIER_TYPE_MAP && $this->_pDataView instanceof DataListView) {
    
			if (isset($recordRaw['showGoogleMap']) && ($recordRaw['showGoogleMap'] === '0' || $recordRaw['showGoogleMap'] === 0 || $recordRaw['showGoogleMap'] === false)) {
				$recordModified['showGoogleMap'] = false;
			} 
			elseif (isset($recordRaw['showGoogleMap']) && ($recordRaw['showGoogleMap'] === '1' || $recordRaw['showGoogleMap'] === 1 || $recordRaw['showGoogleMap'] === true)) {
				$recordModified['showGoogleMap'] = true;
			} 
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

		if (isset($recordModified['regionaler_zusatz']) && is_array($recordModified['regionaler_zusatz'])) {
			$recordModified['regionaler_zusatz'] = $recordModified['regionaler_zusatz'][0] ?? '';
		}

		$recordModified = new ArrayContainerEscape($recordModified);

		if ($this->hasPriceOnRequestField() && ($recordRaw['preisAufAnfrage'] ?? null) === DataListView::SHOW_PRICE_ON_REQUEST) {
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
		if (preg_match('/(\d+[,]?\d*)/', $externalCommission, $matches)) {
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

	/**
	 * @return bool
	 */
	private function hasPriceOnRequestField(): bool
	{
		if ($this->_hasPriceOnRequestField !== null) {
			return $this->_hasPriceOnRequestField;
		}

		try {
			$this->_pEnvironment->getFieldnames()->getFieldInformation('preisAufAnfrage', onOfficeSDK::MODULE_ESTATE);
			$this->_hasPriceOnRequestField = true;
		} catch (UnknownFieldException $pE) {
			$this->_hasPriceOnRequestField = false;
		}

		return $this->_hasPriceOnRequestField;
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
	 * Resolve the total number of estates for this list through the regular list cache
	 * lifecycle, without loading estate pictures, contacts or raw values.
	 *
	 * The request is built with the exact same parameters as loadRecords()'s main
	 * request, so it produces the identical DBCache key. On a cache hit no additional
	 * API call is made; on a miss the regular data-creation process runs and warms the
	 * list cache before the count is returned. This lets the estate search preview reuse
	 * the estate list's cache entry instead of issuing its own isolated API request.
	 *
	 * @return int
	 * @throws API\ApiClientException
	 * @throws UnknownViewException
	 */
	public function getEstateOverallCountFromCache(): int
	{
		$estateParameters = $this->getEstateParameters(1, $this->_formatOutput);
		$this->_pApiClientAction->setParameters($estateParameters);
		$this->_pApiClientAction->addRequestToQueue()->sendRequests();

		return (int) $this->getEstateOverallCount();
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

			$fullLinkElements = wp_parse_url($fullLink);
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
	 * @return array
	 */
	public function getEstateFilesInfo(): array
	{
		$currentEstate = $this->_currentEstate['mainId'];
		return $this->_pEstateFiles->getEstateAllFilesById($currentEstate);
	}

	/**
	 * @return int
	 */
	public function getEstateFilesCount(): int
	{
		$currentEstate = $this->_currentEstate['mainId'];
		return count($this->_pEstateFiles->getEstateAllFilesById($currentEstate));
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
     * Retrieves the unformatted API raw data of a contact person directly by their ID.
     * * @param int|string $addressId
     * @return array
     */
    public function getContactRawById($addressId): array
    {
        if (empty($addressId)) {
            return [];
        }

        return $this->getEnvironment()->getAddressList()->getRawById((int)$addressId);
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
		$filterableFields = $this->_pDataView->getFilterableFields();
		if (in_array('regionaler_zusatz', $filterableFields, true)) {
			$pFieldsCollectionBuilderShort->addFieldsAddressEstateWithRegionValues($pFieldsCollection);
		}
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
		$allDisplayModes = method_exists($pDataView, 'getRangeFieldDisplayModes')
			? $pDataView->getRangeFieldDisplayModes()
			: [];
		$result = [];
		foreach ($fieldsValues as $field => $value) {
			$result[$field] = $pFieldsCollection->getFieldByKeyUnsafe($field)
				->getAsRow();
			$result[$field]['name'] = $field;
			$result[$field]['value'] = $value;
			$result[$field]['label'] = $this->getFieldLabel($field);
			$result[$field]['rangeFieldDisplayMode'] = $allDisplayModes[$field] ?? 'range';
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
	public function getShowMapConfig(): bool
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
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- TWITTER_KEY is a safe class constant
					echo '<meta name="' . GenerateMetaDataSocial::TWITTER_KEY . ':' . esc_html($metaKey) . '" content="' . esc_attr($metaValue) . '">';
				} elseif ($keySocial === GenerateMetaDataSocial::OPEN_GRAPH_KEY) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- OPEN_GRAPH_KEY is a safe class constant
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

	/** @param array $geoFilter */
	public function setGeoFilter($geoFilter)
	{
		$this->_geoFilter = $geoFilter;
	}

	/**
	 * @return bool
	 */
	public function hasGeoFilter(): bool
	{
		return ($this->_geoFilter != null);
	}

	/**
	 * @return object
	 */
	public function getGeoFilter(): object
	{
		return $this->_geoFilter;
	}

	/**
	 * checks whether or not a given value is highlighted
	 * @param string $value
	 * @return bool
	 */
	public function isHighlightedField(string $value) : bool
	{
		return in_array($value, $this->getDataView()->getHighlightedFields());
	}

	/**
	 * checks whether or not a given value is highlighted
	 * @param string $value
	 * @return bool
	 */
	public function getHighlightedFields() : array
	{
		return $this->getDataView()->getHighlightedFields();
	}
}
