<?php

declare(strict_types=1);

namespace onOffice\WPlugin\Form\Preview;

use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataListViewFactory;
use onOffice\WPlugin\DataView\UnknownViewException;
use onOffice\WPlugin\Filter\DefaultFilterBuilderFactory;
use onOffice\WPlugin\Filter\GeoSearchBuilderFromInputVars;
use onOffice\WPlugin\GeoPosition;


class FormPreviewEstate
{
	/** @var DataListViewFactory */
	private $_pDataListViewFactory;

	/** @var APIClientActionGeneric */
	private $_pApiClientAction;

	/** @var DefaultFilterBuilderFactory */
	private $_pDefaultFilterBuilderFactory;

	/**
	 * @param DataListViewFactory $pDataListViewFactory
	 * @param APIClientActionGeneric $pApiClientAction
	 * @param DefaultFilterBuilderFactory $pDefaultFilterBuilderFactory
	 */
	public function __construct(
		DataListViewFactory $pDataListViewFactory,
		APIClientActionGeneric $pApiClientAction,
		DefaultFilterBuilderFactory $pDefaultFilterBuilderFactory)
	{
		$this->_pDataListViewFactory = $pDataListViewFactory;
		$this->_pApiClientAction = $pApiClientAction;
		$this->_pDefaultFilterBuilderFactory = $pDefaultFilterBuilderFactory;
	}

	/**
	 * @param string $listName
	 * @return int
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws ApiClientException
	 * @throws UnknownViewException
	 * @throws Exception
	 */
	public function preview(string $listName): int
	{
		$pListView = $this->_pDataListViewFactory->getListViewByName($listName);
		$requestParams = [
			'listlimit' => 0,
			'filterid' => $pListView->getFilterId(),
		];
		if (in_array(GeoPosition::FIELD_GEO_POSITION, $pListView->getFilterableFields(), true)) {
			$pGeoSearchBuilder = new GeoSearchBuilderFromInputVars;
			$pGeoSearchBuilder->setViewProperty($pListView);
			$geoRangeSearchParameters = $pGeoSearchBuilder->buildParameters();

			if ($geoRangeSearchParameters !== []) {
				$requestParams['georangesearch'] = $geoRangeSearchParameters;
			}
		}
		$pDefaultFilterBuilder = $this->_pDefaultFilterBuilderFactory->buildDefaultListViewFilter($pListView);
		$requestParams['filter'] = $pDefaultFilterBuilder->buildFilter();

		switch ($pListView->getShowReferenceEstate()) {
			case DataListView::HIDE_REFERENCE_ESTATE:
				$requestParams['filter']['referenz'][] = ['op' => '=', 'val' => 0];
				break;
			case DataListView::SHOW_ONLY_REFERENCE_ESTATE:
				$requestParams['filter']['referenz'][] = ['op' => '=', 'val' => 1];
				break;
		}

		$pApiClientAction = $this->_pApiClientAction->withActionIdAndResourceType
			(onOfficeSDK::ACTION_ID_READ, 'estate');
		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->addRequestToQueue()->sendRequests();
		return (int)$pApiClientAction->getResultMeta()['cntabsolute'];
	}
}