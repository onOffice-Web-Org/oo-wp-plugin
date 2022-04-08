<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

use DI\ContainerBuilder;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\Collection\FieldLoaderEstateRegionValues;
use onOffice\WPlugin\Field\Collection\FieldLoaderGeneric;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\Region\RegionController;
use onOffice\WPlugin\SDKWrapper;
use WP_UnitTestCase;


/**
 *
 */
class TestClassFieldLoaderEstateRegionValues
	extends WP_UnitTestCase
{
	/** @var FieldLoaderGeneric */
	private $_pFieldLoader = null;

	private $response = [
		"actionid" => "urn:onoffice-de-ns:smart:2.5:smartml:action:get",
		"resourceid" => "",
		"resourcetype" => "fields",
		"cacheable" => true,
		"identifier" => "",
		"data" => [
			"meta" => [
				"cntabsolute" => null
			],
			"records" =>
				[
					[
						"id" => "estate",
						"type" => "",
						"elements" => [
							"label" => "Immobilien",
							"regionaler_zusatz" => [
								"type" => "multiselect",
								"length" => null,
								"permittedvalues" => null,
								"default" => null,
								"label" => "Regional addition",
								"tablename" => "ObjGeo",
								"content" => "Geografische-Angaben"
							],
						],
					]
				]
		],
		"status" => [
			"errorcode" => 0,
			"message" => "OK"
		]
	];


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$fieldParameters = [
			'labels' => true,
			'showContent' => true,
			'showTable' => true,
			'fieldList' => ['regionaler_zusatz'],
			'language' => Language::getDefault(),
			'modules' => [onOfficeSDK::MODULE_ESTATE],
			'realDataTypes' => true
		];
		$pSDKWrapper = new SDKWrapperMocker();

		$pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'fields', '',
			$fieldParameters, null, $this->response);
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();
		$pRegionController = $this->getMockBuilder(RegionController::class)
			->disableOriginalConstructor()
			->getMock();

		$pContainer->set(RegionController::class, $pRegionController);
		$pContainer->set(SDKWrapper::class, $pSDKWrapper);
		$this->_pFieldLoader = $pContainer->get(FieldLoaderEstateRegionValues::class);
	}


	/**
	 *
	 */

	public function testLoad()
	{
		$result = iterator_to_array($this->_pFieldLoader->load());
		$this->assertCount(1, $result);
		foreach ($result as $fieldname => $fieldProperties) {
			$this->assertInternalType('string', $fieldname);
			$actualModule = $fieldProperties['module'];
			$this->assertContains($actualModule,
				[onOfficeSDK::MODULE_ADDRESS, onOfficeSDK::MODULE_ESTATE], 'Module: ' . $actualModule);
			$this->assertArrayHasKey('module', $fieldProperties);
			$this->assertArrayHasKey('label', $fieldProperties);
			$this->assertArrayHasKey('type', $fieldProperties);
			$this->assertArrayHasKey('default', $fieldProperties);
			$this->assertArrayHasKey('length', $fieldProperties);
			$this->assertArrayHasKey('permittedvalues', $fieldProperties);
			$this->assertArrayHasKey('content', $fieldProperties);
		}
	}
}
