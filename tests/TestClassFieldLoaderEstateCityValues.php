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

use DI\ContainerBuilder;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\Collection\FieldLoaderEstateCityValues;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Types\FieldTypes;
use WP_UnitTestCase;


/**
 *
 */
class TestClassFieldLoaderEstateCityValues
	extends WP_UnitTestCase
{
	/** @var FieldLoaderEstateCityValues */
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
						"id" => "estate",
						"type" => "",
						"elements" => [
							"label" => "Properties",
							"ort" => [
								"type" => "varchar",
								"length" => 50,
								"permittedvalues" => null,
								"default" => null,
								"label" => "City",
								"tablename" => "ObjGeo",
								"content" => "Geographical data"
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
	private $responseEstate = [
		"actionid" => "urn:onoffice-de-ns:smart:2.5:smartml:action:read",
		"resourceid" => "",
		"resourcetype" => "estate",
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
							"ort" => "Aechen",
						],
					],[
						"id" => "16",
						"type" => "",
						"elements" => [
							"ort" => "Gnutz",
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
			'fieldList' => ['ort'],
			'language' => Language::getDefault(),
			'modules' => [onOfficeSDK::MODULE_ESTATE],
			'realDataTypes' => true
		];
		$estateParameters = [
			'data' => ['ort'],
			'listlimit' => 500,
			'estatelanguage' => Language::getDefault(),
			'filter' => [
				'referenz' => [['op' => '=', 'val' => 1]],
				'veroeffentlichen' => [['op' => '=', 'val' => 1]]
			]
		];
		$pSDKWrapper = new SDKWrapperMocker();

		$pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'fields', '',
			$fieldParameters, null, $this->responseField);
		$pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_READ, 'estate', '',
			$estateParameters, null, $this->responseEstate);
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();
		$pContainer->set(SDKWrapper::class, $pSDKWrapper);
		$this->_pFieldLoader = $pContainer->make(FieldLoaderEstateCityValues::class, ['pShowReferenceEstate' => '2']);
	}


	/**
	 *
	 */

	public function testLoad()
	{
		$result = iterator_to_array($this->_pFieldLoader->load());
		$expectation = [
			"ort" => [
				"type" => FieldTypes::FIELD_TYPE_VARCHAR,
				"length" => 50,
				"permittedvalues" => ['Aechen','Gnutz'],
				"default" => null,
				"label"	=> 'City',
				"tablename" => 'ObjGeo',
				"content" => __('Geographical data', 'onoffice-for-wp-websites')
			]
		];
		$this->assertEquals($expectation, $result);
	}

	/**
     * Tests the pagination loop when the API returns more than 500 records.
     */
    public function testLoadWithPagination()
    {
        $fieldParameters = [
            'labels' => true,
            'showContent' => true,
            'showTable' => true,
            'fieldList' => ['ort'],
            'language' => Language::getDefault(),
            'modules' => [onOfficeSDK::MODULE_ESTATE],
            'realDataTypes' => true
        ];

        $estateParameters = [
            'data' => ['ort'],
            'listlimit' => 500,
            'estatelanguage' => Language::getDefault(),
            'filter' => [
                'referenz' => [['op' => '=', 'val' => 1]],
                'veroeffentlichen' => [['op' => '=', 'val' => 1]]
            ]
        ];

        $pSDKWrapper = new SDKWrapperMocker();

        // Mock Fields Request
        $pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'fields', '',
            $fieldParameters, null, $this->responseField);

        // Mock Page 1 (Offset 0, Records 1-500)
        $pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_READ, 'estate', '',
            $estateParameters, null, $this->buildEstateResponse(501, 'Aachen'));

        // Mock Page 2 (Offset 500, Records 501-1000)
        $page2Params = $estateParameters;
        $page2Params['listoffset'] = 500;
        $pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_READ, 'estate', '',
            $page2Params, null, $this->buildEstateResponse(501, 'Zwickau'));

        $pContainerBuilder = new ContainerBuilder;
        $pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
        $pContainer = $pContainerBuilder->build();
        $pContainer->set(SDKWrapper::class, $pSDKWrapper);
        
        $pFieldLoader = $pContainer->make(FieldLoaderEstateCityValues::class, ['pShowReferenceEstate' => '2']);

        $result = iterator_to_array($pFieldLoader->load());

        // The loader must successfully query both pages, extract the cities, merge, sort, and deduplicate them.
        $this->assertEquals(['Aachen', 'Zwickau'], $result['ort']['permittedvalues']);
    }

    /**
     * Helper method to generate mock estate responses with dynamic metadata.
     * 
     * @param int $cntabsolute
     * @param string $cityName
     * @return array
     */
    private function buildEstateResponse(int $cntabsolute, string $cityName): array
    {
        return [
            "actionid" => "urn:onoffice-de-ns:smart:2.5:smartml:action:read",
            "resourceid" => "",
            "resourcetype" => "estate",
            "cacheable" => true,
            "identifier" => "",
            "data" => [
                "meta" => [
                    "cntabsolute" => $cntabsolute
                ],
                "records" => [
                    [
                        "id" => "1",
                        "type" => "",
                        "elements" => [
                            "ort" => $cityName
                        ]
                    ]
                ]
            ],
            "status" => [
                "errorcode" => 0,
                "message" => "OK"
            ]
        ];
    }
}
