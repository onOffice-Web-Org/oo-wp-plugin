<?php

declare(strict_types=1);

namespace onOffice\WPlugin\Controller;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Favorites;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\API\ApiClientException;


class ShowPublishedPropertiesInEditListView
{
	/** @var APIClientActionGeneric */
	private $_pApiClientAction;

	/**
	 * @param APIClientActionGeneric $pApiClientAction
	 */
	public function __construct(APIClientActionGeneric $pApiClientAction)
	{
		$this->_pApiClientAction = $pApiClientAction;
	}

	/**
	 * @param array $elements
	 * @return array
	 */
	public function getShowPublishedProperties(array $elements): array
	{
		$results = [];

		foreach($elements as $element => $listValue){
			$requestParams = $this->addShowPublishedPropertiesRequestParams($listValue, $element);
			$results[$element] = $this->loadNumberEstates($requestParams);
		}

		$response = [
			'success' => !empty($results),
			'data' => $results,
		];

		return $response;
	}

	/**
	 * @param array $listValue
	 * @param string $element
	 * @return array
	 */
	private function addShowPublishedPropertiesRequestParams(array $listValue, string $element): array
	{
		$requestParams = [];
		$ids = Favorites::getAllFavorizedIds();

		foreach ($listValue as $key => $value) {
			$requestParams[$key] = $this->addPublicFilterRequestParam();
			switch ($element) {
				case "oopluginlistviews-listtype":
					if ($value == DataListView::LISTVIEW_TYPE_FAVORITES) {
						$requestParams[$key]['filter']['Id'][] = ['op' => 'in', 'val' => $ids];
					}
					break;

				case "oopluginlistviews-showreferenceestate":
					if ($value == DataListView::HIDE_REFERENCE_ESTATE) {
						$requestParams[$key]['filter']['referenz'][] = ['op' => '=', 'val' => 0];
					} elseif ($value == DataListView::SHOW_ONLY_REFERENCE_ESTATE) {
						$requestParams[$key]['filter']['referenz'][] = ['op' => '=', 'val' => 1];
					}
					break;

				case "oopluginlistviews-filterId":
					$requestParams[$key]['filterid'] = $value;
					break;
			}
		}

		return $requestParams;
	}

	/**
	 * @param array $pListRequestParams
	 * @return array
	 */
	private function loadNumberEstates(array $pListRequestParams): array
	{
		$results = [];
		$listRequestInQueue = [];
		$pApiClientAction = $this->_pApiClientAction->withActionIdAndResourceType(onOfficeSDK::ACTION_ID_READ, 'estate');
		$pApiClientActions = null;

		foreach ($pListRequestParams as $key => $requestParams) {
			$pApiClientActions = clone $pApiClientAction;
			$pApiClientActions->setParameters($requestParams);
			$pApiClientActions->addRequestToQueue();
			$listRequestInQueue[$key] = $pApiClientActions;
		}
		$pApiClientActions->sendRequests();

		if (!$pApiClientActions->getResultStatus() || empty($pApiClientActions->getResultRecords())) {
			return [];
		}

		foreach($listRequestInQueue as $key => $pApiClientAction) {
			$results[$key] = $pApiClientAction->getResultMeta()['cntabsolute'];
		}

		return $results;
	}

	/**
	 * @return array
	 */
	private function addPublicFilterRequestParam(): array
	{
		$requestParams = [
			'filter' => [
				'veroeffentlichen' => [['op' => '=', 'val' => 1]]
			]
		];

		return $requestParams;
	}
}