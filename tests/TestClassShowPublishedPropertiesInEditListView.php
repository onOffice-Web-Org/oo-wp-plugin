<?php

/**
 *
 *    Copyright (C) 2023 onOffice GmbH
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

declare (strict_types=1);

namespace onOffice\tests;

use onOffice\SDK\onOfficeSDK;
use WP_UnitTestCase;
use onOffice\WPlugin\Controller\ShowPublishedPropertiesInEditListView;
use onOffice\WPlugin\API\APIClientActionGeneric;

/**
 * Test for class DistinctFieldsHandler
 */
class TestClassShowPublishedPropertiesInEditListView
	extends WP_UnitTestCase
{
	/** @var ShowPublishedPropertiesInEditListView */
	private $_pShowPublishedPropertiesInEditListView;

	/**
	 * @before
	 */
	public function prepare()
	{
		$pApiClientAction = new APIClientActionGeneric($this->buildSDKWrapper(), '', '');
		$this->_pShowPublishedPropertiesInEditListView = new ShowPublishedPropertiesInEditListView($pApiClientAction);
	}

	/**
	 * @return SDKWrapperMocker
	 */
	private function buildSDKWrapper(): SDKWrapperMocker
	{
		$parameters = [
			'filter' => [
				'veroeffentlichen' => [[
					'op' => '=',
					'val' => 1,
				]],
			]
		];
		$parameterEstateHideReferenceEstate = [
			'filter' => [
				'veroeffentlichen' => [[
					'op' => '=',
					'val' => 1,
				]],
				'referenz' => [[
					'op' => '=',
					'val' => 0,
				]],
			]
		];
		$parameterEstateOnlyReferenceEstate = [
			'filter' => [
				'veroeffentlichen' => [[
					'op' => '=',
					'val' => 1,
				]],
				'referenz' => [[
					'op' => '=',
					'val' => 1,
				]],
			]
		];
		$parameterEstateFilterId = [
			'filter' => [
				'veroeffentlichen' => [[
					'op' => '=',
					'val' => 1,
				]],
			],
			'filterid' => 5
		];

		$pSDKWrapperMocker = new SDKWrapperMocker();
		$pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_READ, 'estate', '', $parameters, null, $this->prepareMockerForEstateTypeDefault());
		$pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_READ, 'estate', '', $parameterEstateHideReferenceEstate, null, $this->prepareMockerForEstateHideReferenceEstate());
		$pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_READ, 'estate', '', $parameterEstateOnlyReferenceEstate, null, $this->prepareMockerForEstateOnlyReferenceEstate());
		$pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_READ, 'estate', '', $parameterEstateFilterId, null, $this->prepareMockerForEstateFilterId());

		return $pSDKWrapperMocker;
	}

	/**
	 *
	 */
	public function testGetShowPublishedProperties()
	{
		$elements = [
			'oopluginlistviews-listtype'=>['default'],
			'oopluginlistviews-showreferenceestate'=>[0,1,2],
			'oopluginlistviews-filterId'=>[5],
		];
		$dataPublishedProperties = [
			'oopluginlistviews-listtype'=>[3],
			'oopluginlistviews-showreferenceestate'=>[4,3,4],
			'oopluginlistviews-filterId'=>[5],
		];
		$response = $this->_pShowPublishedPropertiesInEditListView->getShowPublishedProperties($elements);
		$this->assertTrue($response['success']);
		$this->assertEquals($dataPublishedProperties, $response['data']);
	}

	/**
	 *
	 */
	private function prepareMockerForEstateTypeDefault()
	{
		$response = [
			'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:read',
			'resourceid' => '',
			'resourcetype' => 'estate',
			'identifier' => '',
			'data' => [
				'meta' => [
					'cntabsolute' => 3,
				],
				'records' => [
					0 => [
						'id' => 0,
						'type' => '',
						'elements' => [
						],
					],
				],
			],
			'status' => [
				'errorcode' => 0,
				'message' => 'OK',
			],
		];

		return $response;
	}	

	/**
	*
	*/
	private function prepareMockerForEstateOnlyReferenceEstate()
	{
		$response = [
			'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:read',
			'resourceid' => '',
			'resourcetype' => 'estate',
			'identifier' => '',
			'data' => [
				'meta' => [
					'cntabsolute' => 4,
				],
				'records' => [
					0 => [
						'id' => 0,
						'type' => '',
						'elements' => [
						],
					],
				],
			],
			'status' => [
				'errorcode' => 0,
				'message' => 'OK',
			],
		];

		return $response;
	}

	/**
	*
	*/
	private function prepareMockerForEstateHideReferenceEstate()
	{
		$response = [
			'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:read',
			'resourceid' => '',
			'resourcetype' => 'estate',
			'identifier' => '',
			'data' => [
				'meta' => [
					'cntabsolute' => 4,
				],
				'records' => [
					0 => [
						'id' => 0,
						'type' => '',
						'elements' => [
						],
					],
				],
			],
			'status' => [
				'errorcode' => 0,
				'message' => 'OK',
			],
		];

		return $response;
	}

	/**
	*
	*/
	private function prepareMockerForEstateFilterId()
	{
		$response = [
			'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:read',
			'resourceid' => '',
			'resourcetype' => 'estate',
			'identifier' => '',
			'data' => [
				'meta' => [
					'cntabsolute' => 5,
				],
				'records' => [
					0 => [
						'id' => 0,
						'type' => '',
						'elements' => [
						],
					],
				],
			],
			'status' => [
				'errorcode' => 0,
				'message' => 'OK',
			],
		];

		return $response;
	}
}