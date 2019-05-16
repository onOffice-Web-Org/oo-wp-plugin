<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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
use onOffice\WPlugin\Field\DistinctFieldsFilter;
use onOffice\WPlugin\Field\FieldnamesEnvironmentTest;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Types\FieldsCollection;
use WP_UnitTestCase;
use function json_decode;


class TestClassDistinctFieldsFilter
	extends WP_UnitTestCase
{

	/** FieldnamesEnvironment */
	private $_pFieldnamesEnvironment = null;


	/**
	 *
	 */

	public function setUp()
	{
		$this->_pFieldnamesEnvironment = new FieldnamesEnvironmentTest();
		$fieldParameters = [
			'labels' => true,
			'showContent' => true,
			'showTable' => true,
			'language' => 'ENG',
			'modules' => ['address', 'estate'],
		];
		$pSDKWrapperMocker = $this->_pFieldnamesEnvironment->getSDKWrapper();
		$responseGetFields = json_decode
			(file_get_contents(__DIR__.'/resources/ApiResponseGetFields.json'), true);
		/* @var $pSDKWrapperMocker SDKWrapperMocker */
		$pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'fields', '',
			$fieldParameters, null, $responseGetFields);

		$searchCriteriaFieldsParameters = ['language' => 'ENG', 'additionalTranslations' => true];
		$responseGetSearchcriteriaFields = json_decode
			(file_get_contents(__DIR__.'/resources/ApiResponseGetSearchcriteriaFieldsENG.json'), true);
		$pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'searchCriteriaFields', '',
			$searchCriteriaFieldsParameters, null, $responseGetSearchcriteriaFields);

		parent::setUp();
	}


	/** */
	public function testFilterEstates()
	{
		$module = 'estate';
		$inputValues =
			[
				"" => "Send",
				"objektart[]" => ["haus"],
				"wohnflaeche__von" => "100",
				"wohnflaeche__bis" => "300"
			];

		$expectedResult =
			[
				"objektart" => [["op" => "in", "val" => ["haus"]]],
				"wohnflaeche" => [["op" => "between", "val" => ["100", "300"]]],
			];

		$pFieldnames = new Fieldnames(new FieldsCollection(), false, $this->_pFieldnamesEnvironment);
		$pFieldnames->loadLanguage();
		$pInstance = new DistinctFieldsFilter($pFieldnames, $module);
		$result = $pInstance->filter('objekttyp', $inputValues);

		$this->assertEquals($result, $expectedResult);
	}


	/** */
	public function testFilterSearchcriteria()
	{
		$module = 'searchcriteria';

		$inputValues =
			["oo_formid" => "applicant-search-form-1",
				"oo_formno" => "1",
				"objektart" => "haus",
				"objekttyp" => "",
				"vermarktungsart" => "",
				"wohnflaeche" => "",
				"range_land" => "",
				"range_plz" => "",
				"range_strasse" => "",
				"" => "Search+for+Prospective+Buyers"];
		$expectedResult =
			[
			"objektart" =>
				[
					["op" => "regexp",
					"val" => "haus"]
				]
			];

		$pFieldnames = new Fieldnames(new FieldsCollection(), false, $this->_pFieldnamesEnvironment);
		$pFieldnames->loadLanguage();
		$pInstance = new DistinctFieldsFilter($pFieldnames, $module);
		$result = $pInstance->filter('objekttyp', $inputValues);
		$this->assertEquals($result, $expectedResult);
	}
}