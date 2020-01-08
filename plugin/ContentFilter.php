<?php

/**
 *
 *    Copyright (C) 2016-2019 onOffice GmbH
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
use onOffice\WPlugin\Controller\EstateTitleBuilder;
use onOffice\WPlugin\Controller\SortList\SortListBuilder;
use onOffice\WPlugin\Controller\SortList\SortListDataModel;
use onOffice\WPlugin\Controller\SortList\SortListTypes;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataListViewFactory;
use onOffice\WPlugin\Field\DistinctFieldsScriptRegistrator;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorGeoPositionFrontend;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Filter\DefaultFilterBuilderDetailView;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListView;
use onOffice\WPlugin\Filter\GeoSearchBuilderFromInputVars;
use onOffice\WPlugin\Filter\SearchParameters\SearchParameters;
use onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel;
use onOffice\WPlugin\Filter\SearchParameters\SearchParametersModelBuilder;
use onOffice\WPlugin\ScriptLoader\ScriptLoaderMap;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Utility\__String;
use onOffice\WPlugin\Utility\Logger;
use onOffice\WPlugin\WP\WPQueryWrapper;
use onOffice\WPlugin\WP\WPScriptStyleDefault;
use WP_Query;
use const ONOFFICE_DI_CONFIG_PATH;
use function __;
use function add_rewrite_rule;
use function add_rewrite_tag;
use function get_page_uri;
use function get_post;
use function shortcode_atts;
use function wp_get_post_parent_id;


/**
 *
 */

class ContentFilter
{
	/** @var Logger */
	private $_pLogger = null;

	/** @var ScriptLoaderMap */
	private $_pScriptLoaderMap = null;


	/**
	 *
	 * @param Logger $pLogger
	 * @param ScriptLoaderMap $pScriptLoaderMap
	 *
	 */

	public function __construct(Logger $pLogger, ScriptLoaderMap $pScriptLoaderMap)
	{
		$this->_pLogger = $pLogger;
		$this->_pScriptLoaderMap = $pScriptLoaderMap;
	}


	/**
	 *
	 */

	public function addCustomRewriteTags() {
		add_rewrite_tag('%estate_id%', '([^&]+)');
		add_rewrite_tag('%view%', '([^&]+)');
	}


	/**
	 *
	 */

	public function addCustomRewriteRules() {
		$pDetailView = $this->getEstateDetailView();
		$detailPageId = $pDetailView->getPageId();

		if ($detailPageId != null) {
			$pagename = get_page_uri($detailPageId);
			$pageUrl = $this->rebuildSlugTaxonomy($detailPageId);
			add_rewrite_rule('^('.preg_quote($pageUrl).')/([0-9]+)/?$',
				'index.php?pagename='.urlencode($pagename).'&view=$matches[1]&estate_id=$matches[2]','top');
		}

		add_rewrite_rule('^distinctfields-json/?$', 'index.php?distinctfields_json=1', 'top');
		add_rewrite_rule('^document-pdf/([^\/]+)/([0-9]+)/?$', 'index.php?document_pdf=1&view=$matches[1]&estate_id=$matches[2]', 'top');
	}


	/**
	 *
	 * @param array $attributesInput
	 * @return string
	 *
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
					$pSortListModel = $pSortListBuilder->build($pListView);
					$pListView->setSortby($pSortListModel->getSelectedSortby());
					$pListView->setSortorder($pSortListModel->getSelectedSortorder());

					$this->setAllowedGetParametersEstate($pListView, $pSortListModel);
					$pTemplate = new Template($pListView->getTemplate());

					$pListViewFilterBuilder = new DefaultFilterBuilderListView($pListView);
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
	 */
	private function setAllowedGetParametersEstate(DataListView $pDataView, SortListDataModel $pSortListDataModel)
	{
		$pRequestVariableSanitizer = new RequestVariablesSanitizer();

		$filterableFieldsView = $pDataView->getFilterableFields();
		$filterableFields = $this->setAllowedGetParametersEstateGeo($filterableFieldsView);

		$pDIContainerBuilder = new ContainerBuilder;
		$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pDIContainerBuilder->build();
		$pBuilderShort = $pContainer->get(FieldsCollectionBuilderShort::class);
		$pSearchParametersModelBuilder = $pContainer->get(SearchParametersModelBuilder::class);
		$pModel = $pSearchParametersModelBuilder->build($filterableFields, onOfficeSDK::MODULE_ESTATE, $pBuilderShort);

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
	 *
	 * @param array $filterableFields
	 * @return array
	 *
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
	 *
	 * @param int $page
	 * @return string
	 *
	 */

	private function rebuildSlugTaxonomy($page)
	{
		$pPost = get_post($page);

		if ($pPost === null) {
			return;
		}

		$listpermalink = $pPost->post_name;
		$parent = wp_get_post_parent_id($page);

		if ($parent) {
			$listpermalink = $this->rebuildSlugTaxonomy($parent).'/'.$listpermalink;
		}

		return $listpermalink;
	}


	/**
	 *
	 * @global WP_Query $wp_query
	 * @param DataDetailView $pDetailView
	 * @param string $unitsView
	 * @return EstateDetail
	 *
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
	 *
	 * @param array $title see Wordpress internal function wp_get_document_title()
	 * @return string
	 *
	 */

	public function setTitle(array $title)
	{
		$estateId = (int)(new WPQueryWrapper())->getWPQuery()->get('estate_id', 0);
		if ($estateId === 0) {
			return $title;
		}

		$newTitleValue = '';
		$pEstateTitleBuilder = new EstateTitleBuilder();
		$titleFull = $pEstateTitleBuilder->buildTitle($estateId, '%1$s');
		$titleLength = __String::getNew($titleFull)->length();

		if ($titleLength > 0 && $titleLength < 70) {
			$newTitleValue = $titleFull;
		} else {
			/* translators: %2$s is the kind of estate, %3$s the markting type,
							%4$s the city, %5$s is the estate number.
							Example: House (Sale) in Aachen - JJ12345 */
			$format = __('%2$s (%3$s) in %4$s - %5$s', 'onoffice');
			$newTitleValue = $pEstateTitleBuilder->buildTitle($estateId, $format);
		}

		$title['title'] = $newTitleValue;

		return $title;
	}


	/**
	 *
	 * @return DataDetailView
	 *
	 */

	private function getEstateDetailView(): DataDetailView
	{
		$pDataDetailViewHandler = new DataDetailViewHandler();
		return $pDataDetailViewHandler->getDetailView();
	}
}