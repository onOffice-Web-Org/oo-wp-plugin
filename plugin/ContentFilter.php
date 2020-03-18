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
use Exception;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\SortList\SortListBuilder;
use onOffice\WPlugin\Controller\SortList\SortListDataModel;
use onOffice\WPlugin\Controller\SortList\SortListTypes;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataListViewFactory;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\DistinctFieldsScriptRegistrator;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Filter\DefaultFilterBuilderDetailView;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListView;
use onOffice\WPlugin\Filter\GeoSearchBuilderFromInputVars;
use onOffice\WPlugin\Filter\SearchParameters\SearchParameters;
use onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Utility\Logger;
use onOffice\WPlugin\WP\WPScriptStyleDefault;
use WP_Query;
use function __;
use function shortcode_atts;
use const ONOFFICE_DI_CONFIG_PATH;

/**
 *
 */

class ContentFilter
{
	/** @var Logger */
	private $_pLogger = null;

	/**
	 * @param Logger $pLogger
	 */
	public function __construct(Logger $pLogger)
	{
		$this->_pLogger = $pLogger;
	}

	/**
	 * @param array $attributesInput
	 * @return string
	 * @throws Exception
	 */
	public function registerEstateShortCodes($attributesInput)
	{
		global $wp_query;
		$pDIContainerBuilder = new ContainerBuilder;
		$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pDIContainerBuilder->build();

		$page = 1;
		if (!empty($wp_query->query_vars['page'])) {
			$page = $wp_query->query_vars['page'];
		}

		$attributes = shortcode_atts([
			'view' => null,
			'units' => null,
		], $attributesInput);

		if ($attributes['view'] !== null) {
			try {
				$pDetailView = $this->getEstateDetailView();

				if ($pDetailView->getName() === $attributes['view']) {
					/* @var $pTemplate Template */
					$pTemplate = $pContainer->get(Template::class)->withTemplateName($pDetailView->getTemplate());
					$pEstateDetail = $this->preloadSingleEstate($pDetailView, $attributes['units']);
					$pTemplate->setEstateList($pEstateDetail);
					$result = $pTemplate->render();
					return $result;
				}

				$pListViewFactory = new DataListViewFactory();
				$pListView = $pListViewFactory->getListViewByName($attributes['view']);

				if (is_object($pListView) && $pListView->getName() === $attributes['view']) {

					$pDIContainerBuilder = new ContainerBuilder;
					$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
					$pContainer = $pDIContainerBuilder->build();
					$pSortListBuilder = $pContainer->get(SortListBuilder::class);
					$pFieldsCollectionBuilderShort = $pContainer->get(FieldsCollectionBuilderShort::class);
					$pSortListModel = $pSortListBuilder->build($pListView);
					$pListView->setSortby($pSortListModel->getSelectedSortby());
					$pListView->setSortorder($pSortListModel->getSelectedSortorder());

					$this->setAllowedGetParametersEstate($pListView, $pSortListModel, $pFieldsCollectionBuilderShort);
					$pTemplate = new Template($pListView->getTemplate());

					$pListViewFilterBuilder = new DefaultFilterBuilderListView($pListView, $pFieldsCollectionBuilderShort);
					$availableOptionsEstates = $pListView->getAvailableOptions();
					$pDistinctFieldsChecker = new DistinctFieldsScriptRegistrator
						(new WPScriptStyleDefault);
					$pDistinctFieldsChecker->registerScripts(onOfficeSDK::MODULE_ESTATE,
						$availableOptionsEstates);

					$pGeoSearchBuilder = new GeoSearchBuilderFromInputVars();
					$pGeoSearchBuilder->setViewProperty($pListView);

					$pEstateList = new EstateList($pListView);
					$pEstateList->setDefaultFilterBuilder($pListViewFilterBuilder);
					$pEstateList->setUnitsViewName($attributes['units']);
					$pEstateList->setGeoSearchBuilder($pGeoSearchBuilder);

					$pTemplate->setEstateList($pEstateList);
					$pEstateList->loadEstates($page);

					$result = $pTemplate->render();
					return $result;
				}
			} catch (Exception $pException) {
				return $this->_pLogger->logErrorAndDisplayMessage($pException);
			}
			return __('Estates view not found.', 'onoffice');
		}
	}

	/**
	 * @param DataListView $pDataView
	 * @param SortListDataModel $pSortListDataModel
	 * @param FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort
	 * @throws UnknownFieldException
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	private function setAllowedGetParametersEstate(DataListView $pDataView, SortListDataModel $pSortListDataModel,
		FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort)
	{
		$pRequestVariableSanitizer = new RequestVariablesSanitizer();
		$pFieldsCollection = new FieldsCollection();

		$pFieldsCollectionBuilderShort->addFieldsAddressEstate($pFieldsCollection);

		$pModel = new SearchParametersModel();
		$filterableFieldsView = $pDataView->getFilterableFields();
		$filterableFields = $this->setAllowedGetParametersEstateGeo($filterableFieldsView);

		foreach ($filterableFields as $filterableField) {

			if (!$pFieldsCollection->containsFieldByModule(onOfficeSDK::MODULE_ESTATE, $filterableField)) {
				continue;
			}

			$pField = $pFieldsCollection->getFieldByModuleAndName(onOfficeSDK::MODULE_ESTATE, $filterableField);

			if (FieldTypes::isMultipleSelectType($pField->getType())) {
				$pModel->setParameterArray
					($filterableField, $pRequestVariableSanitizer->getFilteredGet($filterableField, FILTER_DEFAULT, FILTER_FORCE_ARRAY));
			}
			else {
				$pModel->setParameter
					($filterableField, $pRequestVariableSanitizer->getFilteredGet($filterableField));
			}

			$type = $pField->getType();

			if (FieldTypes::isNumericType($type) ||
				FieldTypes::isDateOrDateTime($type)) {
				$pModel->addAllowedGetParameter($filterableField.'__von');
				$pModel->addAllowedGetParameter($filterableField.'__bis');
			}

			$pModel->addAllowedGetParameter($filterableField);
		}


		if ($pSortListDataModel->isAdjustableSorting())	{
			foreach (SortListTypes::getSortUrlPrameter() as $urlParameter) {
				$pModel->addAllowedGetParameter($urlParameter);
				$pModel->setParameter($urlParameter, $pRequestVariableSanitizer->getFilteredGet($urlParameter));
			}
		}

		add_filter('wp_link_pages_link', function(string $link, int $i) use ($pModel): string {
			$pSearchParameters = new SearchParameters();
			return $pSearchParameters->linkPagesLink($link, $i, $pModel);
		}, 10, 2);
		add_filter('wp_link_pages_args', [$pModel, 'populateDefaultLinkParams']);
	}

	/**
	 * @param array $filterableFields
	 * @return array
	 */
	private function setAllowedGetParametersEstateGeo(array $filterableFields): array
	{
		$positionGeoPos = array_search(GeoPosition::FIELD_GEO_POSITION, $filterableFields, true);

		if ($positionGeoPos !== false) {
			$pGeoPosition = new GeoPosition();
			$geoPositionFields = $pGeoPosition->getEstateSearchFields();
			foreach ($geoPositionFields as $geoPositionField) {
				$filterableFields []= $geoPositionField;
			}
			unset($filterableFields[$positionGeoPos]);
		}
		return $filterableFields;
	}

	/**
	 * @global WP_Query $wp_query
	 * @param DataDetailView $pDetailView
	 * @param string $unitsView
	 * @return EstateDetail
	 */
	private function preloadSingleEstate(DataDetailView $pDetailView, $unitsView)
	{
		global $wp_query;

		$estateId = $wp_query->query_vars['estate_id'] ?? 0;

		$pDefaultFilterBuilder = new DefaultFilterBuilderDetailView();
		$pDefaultFilterBuilder->setEstateId($estateId);

		$pEstateDetailList = new EstateDetail($pDetailView);
		$pEstateDetailList->setDefaultFilterBuilder($pDefaultFilterBuilder);
		$pEstateDetailList->setUnitsViewName($unitsView);
		$pEstateDetailList->loadSingleEstate($estateId);

		return $pEstateDetailList;
	}

	/**
	 * @return DataDetailView
	 */
	private function getEstateDetailView(): DataDetailView
	{
		$pDataDetailViewHandler = new DataDetailViewHandler();
		return $pDataDetailViewHandler->getDetailView();
	}
}