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

class EstateCityValuesMapperSDKWrapperMocker
	extends SDKWrapperMocker
{
	/** @var bool[] */
	private $_saveToCacheValues = [];

	/** @var int[] */
	private $_requestCounts = [];

	public function sendRequests(bool $saveToCache = true)
	{
		$this->_saveToCacheValues[] = $saveToCache;
		$this->_requestCounts[] = count($this->getRequestArray());
		parent::sendRequests($saveToCache);
	}

	public function getSaveToCacheValues(): array
	{
		return $this->_saveToCacheValues;
	}

	public function getRequestCounts(): array
	{
		return $this->_requestCounts;
	}
}

/**
 *
 */
class TestClassEstateCityValuesMapper
	extends WP_UnitTestCase
{
	/** @var EstateCityValuesMapper */
	private $_pMapper = null;

	/** @var EstateCityValuesMapperSDKWrapperMocker */
	private $_pSDKWrapper = null;

	/** @var array */
	private $_responseCurrentLanguage = [
		"actionid" => "urn:onoffice-de-ns:smart:2.5:smartml:action:read",
		"resourceid" => "",
		"resourcetype" => "estate",
		"cacheable" => true,
		"identifier" => "",
		"data" => [
			"meta" => [
				"cntabsolute" => 4
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
				"cntabsolute" => 4
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
		$this->_pSDKWrapper = new EstateCityValuesMapperSDKWrapperMocker();

		$currentLanguageParams = [
			'data' => ['ort', 'Id'],
			'listlimit' => 500,
			'addMainLangId' => true,
			'sortby' => 'Id',
			'sortorder' => 'ASC',
			'estatelanguage' => Language::getDefault(),
			'filter' => [
				'referenz' => [['op' => '=', 'val' => 0]],
				'veroeffentlichen' => [['op' => '=', 'val' => 1]]
			]
		];
		$mainLanguageParams = [
			'data' => ['ort', 'Id'],
			'listlimit' => 500,
			'addMainLangId' => true,
			'sortby' => 'Id',
			'sortorder' => 'ASC',
			'filter' => [
				'referenz' => [['op' => '=', 'val' => 0]],
				'veroeffentlichen' => [['op' => '=', 'val' => 1]]
			]
		];
		$this->_pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_READ, 'estate', '',
			$currentLanguageParams, null, $this->_responseCurrentLanguage);
		$this->_pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_READ, 'estate', '',
			$mainLanguageParams, null, $this->_responseMainLanguage);

		$this->_pMapper = new EstateCityValuesMapper($this->_pSDKWrapper, DataListView::HIDE_REFERENCE_ESTATE);
	}

	public function testGetMainLanguageCityValuesExpandsLocalizedCities()
	{
		$result = $this->_pMapper->getMainLanguageCityValues(['Gufidaun']);
		$this->assertEquals(['Gudon'], $result);
		$this->assertSame([true], $this->_pSDKWrapper->getSaveToCacheValues());
		$this->assertSame([2], $this->_pSDKWrapper->getRequestCounts());
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
		$this->assertSame([], $this->_pSDKWrapper->getRequestArray());
		$this->assertSame([], $this->_pSDKWrapper->getSaveToCacheValues());
	}

	public function testGetMainLanguageCityValuesPaginatesAndUsesFilterId()
	{
		$pSDKWrapper = new EstateCityValuesMapperSDKWrapperMocker();
		$currentLanguageParams = [
			'data' => ['ort', 'Id'],
			'listlimit' => 500,
			'addMainLangId' => true,
			'sortby' => 'Id',
			'sortorder' => 'ASC',
			'estatelanguage' => Language::getDefault(),
			'filter' => [
				'referenz' => [['op' => '=', 'val' => 0]],
				'veroeffentlichen' => [['op' => '=', 'val' => 1]],
			],
			'filterid' => 23,
		];
		$mainLanguageParams = $currentLanguageParams;
		unset($mainLanguageParams['estatelanguage']);

		foreach ([0, 500, 1000] as $offset) {
			$currentParams = $currentLanguageParams;
			$mainParams = $mainLanguageParams;
			if ($offset !== 0) {
				$currentParams['listoffset'] = $offset;
				$mainParams['listoffset'] = $offset;
			}

			$currentRecords = $offset === 1000 ? [[
				'id' => 'local-1001',
				'elements' => ['ort' => 'Spätstadt', 'mainLangId' => 'main-1001'],
			]] : [];
			$mainRecords = $offset === 1000 ? [[
				'id' => 'main-1001',
				'elements' => ['ort' => 'Late City'],
			]] : [];

			$pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_READ, 'estate', '',
				$currentParams, null, $this->buildResponse(1001, $currentRecords));
			$pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_READ, 'estate', '',
				$mainParams, null, $this->buildResponse(1001, $mainRecords));
		}

		$pMapper = new EstateCityValuesMapper($pSDKWrapper, DataListView::HIDE_REFERENCE_ESTATE, 23);
		$this->assertSame(['Late City'], $pMapper->getMainLanguageCityValues(['Spätstadt']));
		$this->assertSame([true, true], $pSDKWrapper->getSaveToCacheValues());
		$this->assertSame([2, 6], $pSDKWrapper->getRequestCounts());
	}

	private function buildResponse(int $count, array $records): array
	{
		return [
			'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:read',
			'resourceid' => '',
			'resourcetype' => 'estate',
			'cacheable' => true,
			'identifier' => '',
			'data' => [
				'meta' => ['cntabsolute' => $count],
				'records' => $records,
			],
			'status' => ['errorcode' => 0, 'message' => 'OK'],
		];
	}
}
