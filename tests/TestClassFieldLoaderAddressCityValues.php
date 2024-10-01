<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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
use onOffice\WPlugin\Language;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Field\Collection\FieldLoaderAddressCityValues;
use WP_UnitTestCase;


/**
 *
 */
class TestClassFieldLoaderAddressCityValues
	extends WP_UnitTestCase
{
	/** @var FieldLoaderAddressCityValues */
	private $_pFieldLoader = null;

	/** @var array */
	private $responseField = [
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
						"id" => "address",
						"type" => "",
						"elements" => [
							"label" => "Contact",
							"Ort" => [
								"type" => "varchar",
								"length" => 50,
								"permittedvalues" => null,
								"default" => null,
								"label" => "City",
								"content" => "Contact"
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

	/** @var array */
	private $responseAddress = [
		"actionid" => "urn:onoffice-de-ns:smart:2.5:smartml:action:read",
		"resourceid" => "",
		"resourcetype" => "address",
		"cacheable" => true,
		"identifier" => "",
		"data" => [
			"meta" => [
				"cntabsolute" => null
			],
			"records" =>
				[
					[
						"id" => "15",
						"type" => "",
						"elements" => [
							"Ort" => "Aechen",
						],
					],[
						"id" => "16",
						"type" => "",
						"elements" => [
							"Ort" => "Gnutz",
						],
					],
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
			'language' => Language::getDefault(),
			'modules' => [onOfficeSDK::MODULE_ADDRESS],
			'realDataTypes' => true
		];
		$estateParameters = [
			'data' => ['Ort'],
			'listlimit' => 500,
			'filter' => [
				'homepage_veroeffentlichen' => [['op' => '=', 'val' => 1]]
			]
		];
		$pSDKWrapper = new SDKWrapperMocker();

		$pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'fields', '',
			$fieldParameters, null, $this->responseField);
		$pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_READ, 'address', '',
			$estateParameters, null, $this->responseAddress);
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();
		$pContainer->set(SDKWrapper::class, $pSDKWrapper);
		$this->_pFieldLoader = $pContainer->make(FieldLoaderAddressCityValues::class);
	}


	/**
	 *
	 */

	public function testLoad()
	{
		$result = iterator_to_array($this->_pFieldLoader->load());
		$expectation = [
			"Ort" => [
				"type" => FieldTypes::FIELD_TYPE_MULTISELECT,
				"length" => 50,
				"permittedvalues" => ['Aechen' => 'Aechen', 'Gnutz' => 'Gnutz'],
				"default" => null,
				"label"	=> 'City',
				"content" => "Contact"
			]
		];
		$this->assertEquals($expectation, $result);
	}
}
