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

use DI\Container;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\DistinctFieldsFilter;
use onOffice\WPlugin\Field\FieldnamesEnvironmentTest;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use WP_UnitTestCase;
use function json_decode;


class TestClassDistinctFieldsFilter
	extends WP_UnitTestCase
{

	/** FieldnamesEnvironment */
	private $_pFieldnamesEnvironment = null;

	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort = null;



	/**
	 * @before
	 *
	 */

	public function prepare()
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

		$this->_pFieldsCollectionBuilderShort = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
			->setMethods(['addFieldsAddressEstate', 'addFieldsSearchCriteria'])
			->setConstructorArgs([new Container])
			->getMock();

		$this->_pFieldsCollectionBuilderShort->method('addFieldsAddressEstate')
			->with($this->anything())
			->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
				$pField1 = new Field('objektart', onOfficeSDK::MODULE_ESTATE);
				$pField1->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pField1);

				$pField2 = new Field('wohnflaeche', onOfficeSDK::MODULE_ESTATE);
				$pField2->setType(FieldTypes::FIELD_TYPE_FLOAT);
				$pFieldsCollection->addField($pField2);

				return $this->_pFieldsCollectionBuilderShort;
			}));

			$this->_pFieldsCollectionBuilderShort->method('addFieldsSearchCriteria')
			->with($this->anything())
			->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
				$pField1 = new Field('objektart', onOfficeSDK::MODULE_SEARCHCRITERIA);
				$pField1->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pField1);

				$pField2 = new Field('wohnflaeche', onOfficeSDK::MODULE_ESTATE);
				$pField2->setType(FieldTypes::FIELD_TYPE_FLOAT);
				$pFieldsCollection->addField($pField2);

				return $this->_pFieldsCollectionBuilderShort;
			}));
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\DistinctFieldsFilter::filter
	 * @covers onOffice\WPlugin\Field\DistinctFieldsFilter::isNumericalType
	 * @covers onOffice\WPlugin\Field\DistinctFieldsFilter::isMultiselectableType
	 *
	 */

	public function testFilterEstates()
	{
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

		$pInstance = new DistinctFieldsFilter($this->_pFieldsCollectionBuilderShort, 'estate');
		$this->assertEquals($expectedResult, $pInstance->filter('objekttyp', $inputValues));
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\DistinctFieldsFilter::filter
	 * @covers onOffice\WPlugin\Field\DistinctFieldsFilter::isNumericalType
	 * @covers onOffice\WPlugin\Field\DistinctFieldsFilter::isMultiselectableType
	 *
	 */

	public function testFilterSearchcriteria()
	{
		$expectedResult =
			[
			"objektart" =>
				[
					["op" => "regexp",
					"val" => "haus"]
				]
			];

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

		$pInstance = new DistinctFieldsFilter($this->_pFieldsCollectionBuilderShort, 'searchcriteria');
		$this->assertEquals($expectedResult, $pInstance->filter('objekttyp', $inputValues));
	}
}