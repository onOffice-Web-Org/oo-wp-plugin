<?php

/**
 *
 *    Copyright (C) 2026 onOffice GmbH
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

declare(strict_types=1);

namespace onOffice\tests;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Filter\EstateCityValuesMapper;
use onOffice\WPlugin\Language;
use WP_UnitTestCase;

/**
 *
 */
class TestClassEstateCityValuesMapper
	extends WP_UnitTestCase
{
	/** @var EstateCityValuesMapper */
	private $_pMapper = null;

	/** @var array */
	private $_responseCurrentLanguage = [
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
						"id" => "3785",
						"type" => "",
						"elements" => [
							"ort" => "Gufidaun",
							"mainLangId" => "3783",
						],
					],[
						"id" => "2063",
						"type" => "",
						"elements" => [
							"ort" => "Natz-Schabs",
							"mainLangId" => "2061",
						],
					],[
						"id" => "2673",
						"type" => "",
						"elements" => [
							"ort" => "Mühlbach",
							"mainLangId" => "2671",
						],
					],[
						"id" => "3001",
						"type" => "",
						"elements" => [
							"ort" => "Mühlbach",
							"mainLangId" => "2999",
						],
					],
				]
		],
		"status" => [
			"errorcode" => 0,
			"message" => "OK"
		]
	];

	/** @var array */
	private $_responseMainLanguage = [
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
						"id" => "3783",
						"type" => "",
						"elements" => [
							"ort" => "Gudon",
						],
					],[
						"id" => "2061",
						"type" => "",
						"elements" => [
							"ort" => "Naz-Sciaves",
						],
					],[
						"id" => "2671",
						"type" => "",
						"elements" => [
							"ort" => "Mühlbach",
						],
					],[
						"id" => "2999",
						"type" => "",
						"elements" => [
							"ort" => "Rio Di Pusteria",
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
	 * @before
	 */
	public function prepare()
	{
		$pSDKWrapper = new SDKWrapperMocker();

		$currentLanguageParams = [
			'data' => ['ort', 'Id'],
			'listlimit' => 500,
			'addMainLangId' => true,
			'estatelanguage' => Language::getDefault(),
			'filter' => [
				'veroeffentlichen' => [['op' => '=', 'val' => 1]]
			]
		];
		$mainLanguageParams = [
			'data' => ['ort', 'Id'],
			'listlimit' => 500,
			'addMainLangId' => true,
			'filter' => [
				'veroeffentlichen' => [['op' => '=', 'val' => 1]]
			]
		];
		$pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_READ, 'estate', '',
			$currentLanguageParams, null, $this->_responseCurrentLanguage);
		$pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_READ, 'estate', '',
			$mainLanguageParams, null, $this->_responseMainLanguage);

		$this->_pMapper = new EstateCityValuesMapper($pSDKWrapper, DataListView::HIDE_REFERENCE_ESTATE);
	}

	public function testGetMainLanguageCityValuesExpandsLocalizedCities()
	{
		$result = $this->_pMapper->getMainLanguageCityValues(['Gufidaun']);
		$this->assertEquals(['Gudon'], $result);
	}

	public function testGetMainLanguageCityValuesMergesCollisions()
	{
		$result = $this->_pMapper->getMainLanguageCityValues(['Mühlbach']);
		$this->assertEquals(['Mühlbach', 'Rio Di Pusteria'], $result);
	}

	public function testGetMainLanguageCityValuesKeepsUnknownValues()
	{
		$result = $this->_pMapper->getMainLanguageCityValues(['Unbekannte Stadt']);
		$this->assertEquals(['Unbekannte Stadt'], $result);
	}

	public function testGetMainLanguageCityValuesMergesMultipleSelections()
	{
		$result = $this->_pMapper->getMainLanguageCityValues(['Gufidaun', 'Natz-Schabs', 'Mühlbach']);
		$this->assertEquals(['Gudon', 'Naz-Sciaves', 'Mühlbach', 'Rio Di Pusteria'], $result);
	}

	public function testGetMainLanguageCityValuesEmptyInput()
	{
		$this->assertEquals([], $this->_pMapper->getMainLanguageCityValues([]));
	}
}