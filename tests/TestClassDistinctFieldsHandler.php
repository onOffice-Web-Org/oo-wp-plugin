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

use DI\Container;
use onOffice\SDK\onOfficeSDK;
use onOffice\tests\SDKWrapperMocker;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\DistinctFieldsHandler;
use onOffice\WPlugin\Field\DistinctFieldsHandlerEnvironment;
use onOffice\WPlugin\Field\FieldnamesEnvironment;
use onOffice\WPlugin\Field\FieldnamesEnvironmentTest;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use WP_UnitTestCase;
use function json_decode;

/**
 *
 *
 */

class TestClassDistinctFieldsHandler
	extends WP_UnitTestCase
{

	/** */
	const INPUT_VALUES = [
			'objektart' => ['wohnung'],
			'vermarktungsart' => ['kauf']
		];

	/** */
	const DISTINCT_FIELDS = ['objekttyp'];

	/** */
	const GEO_POSITION_FIELDS = ['range'];

	/** */
	const MODULE = 'estate';

	/** @var FieldnamesEnvironment */
	private $_pFieldnamesEnvironment = null;

	/** @var DistinctFieldsHandler */
	private $_pInstance = null;

	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort = null;


	/**
	 *
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

		$parametersEstates = [
			'language' => 'ENG',
			'module' => 'estate',
			'field' => 'objekttyp',
			'filter' => [
				'objektart' => [['op' => 'in', 'val' => ['wohnung']]],
				'vermarktungsart' => [['op' => 'in', 'val' => ['kauf']]]
			],
			'georangesearch' => ['range']
		];

		$responseEstates =  file_get_contents
			(__DIR__.'/resources/Field/AppResponseDistinctFieldsHandlerEstate.json');
		$responseEstatesFields = json_decode($responseEstates, true);

		$pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'distinctValues',
			'', $parametersEstates, null, $responseEstatesFields);

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

				$pField2 = new Field('vermarktungsart', onOfficeSDK::MODULE_ESTATE);
				$pField2->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pField2);

				$pField3 = new Field('objekttyp', onOfficeSDK::MODULE_ESTATE);
				$pField3->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pField3);
				return $this->_pFieldsCollectionBuilderShort;
			}));

			$this->_pFieldsCollectionBuilderShort->method('addFieldsSearchCriteria')
			->with($this->anything())
			->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
				$pField1 = new Field('objektart', onOfficeSDK::MODULE_SEARCHCRITERIA);
				$pField1->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pField1);

				$pField2 = new Field('vermarktungsart', onOfficeSDK::MODULE_SEARCHCRITERIA);
				$pField2->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pField2);
				return $this->_pFieldsCollectionBuilderShort;
			}));

		$pHandlerEnvironment = new DistinctFieldsHandlerEnvironment();
		$pHandlerEnvironment->setDistinctFields(self::DISTINCT_FIELDS);
		$pHandlerEnvironment->setGeoPositionFields(self::GEO_POSITION_FIELDS);
		$pHandlerEnvironment->setInputValues(self::INPUT_VALUES);
		$pHandlerEnvironment->setModule(self::MODULE);

		$this->_pInstance = new DistinctFieldsHandler($pSDKWrapperMocker,
					$this->_pFieldsCollectionBuilderShort,
					$pHandlerEnvironment);
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandler::check
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandler::retrieveValues
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandler::buildParameters
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandler::editMultiselectableField
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandler::getValues
	 *
	 */

	public function testCheck()
	{
		$values['objekttyp[]'] = [
			'etage' => 'Etagenwohnung',
			'maisonette' => 'Maisonette',
			'erdgeschoss' => 'Erdgeschosswohnung',
			'dachgeschoss' => 'Dachgeschoss',
			'rohdachboden' => 'Rohdachboden',
			'appartment' => 'Apartment',
			'terrassen' => 'Terrasse',
			'attikawohnung' => 'Attikawohnung',
			'hochparterre' => 'Hochparterre',
			'souterrain' => 'Souterrain',
		];

		$this->_pInstance->check();
		$this->assertEquals($this->_pInstance->getValues(), $values);
	}
}