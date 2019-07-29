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

use onOffice\SDK\onOfficeSDK;
use onOffice\tests\SDKWrapperMocker;
use onOffice\WPlugin\Field\DistinctFieldsHandler;
use onOffice\WPlugin\Field\DistinctFieldsHandlerConfigurationTest;
use onOffice\WPlugin\Field\FieldnamesEnvironment;
use onOffice\WPlugin\Field\FieldnamesEnvironmentTest;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Types\FieldsCollection;
use WP_UnitTestCase;
use function json_decode;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassDistinctFieldsHandler
	extends WP_UnitTestCase
{
	/** @var FieldnamesEnvironment */
	private $_pFieldnamesEnvironment = null;

	/** @var DistinctFieldsHandler */
	private $_pInstance = null;


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

		$pExtraFieldsCollection = new FieldsCollection();

		$pFieldnames = new Fieldnames
			($pExtraFieldsCollection, false, $this->_pFieldnamesEnvironment);
		$pFieldnames->loadLanguage();

		$pConfigTest = new DistinctFieldsHandlerConfigurationTest();
		$pConfigTest->setSDKWrapper($pSDKWrapperMocker);
		$pConfigTest->setFieldnames($pFieldnames);
		$this->_pInstance = new DistinctFieldsHandler($pConfigTest);
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandler::__construct
	 *
	 */

	public function checkConstruct()
	{
		$this->assertInstanceOf($this->_pInstance, DistinctFieldsHandler::class);
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandler::setModule
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandler::getModule
	 *
	 */

	public function testSetModule()
	{
		$this->_pInstance->setModule('estate');
		$this->assertEquals($this->_pInstance->getModule(), 'estate');
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandler::setDistinctFields
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandler::getDistinctFields
	 *
	 */

	public function testSetDistinctFields()
	{
		$distinctFields = ['objekttyp'];
		$this->_pInstance->setDistinctFields($distinctFields);
		$this->assertEquals($distinctFields, $this->_pInstance->getDistinctFields());
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandler::setInputValues
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandler::getInputValues
	 *
	 */

	public function testsetInputValues()
	{
		$inputValues = [
			'objektart' => ['wohnung'],
			'vermarktungsart' => ['kauf']
		];

		$this->_pInstance->setInputValues($inputValues);
		$this->assertEquals($inputValues, $this->_pInstance->getInputValues());
	}


	/**
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandler::setGeoPositionFields
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandler::getGeoPositionFields
	 *
	 */

	public function testSetGeoPositionFields()
	{
		$geoFields = ['range'];
		$this->_pInstance->setGeoPositionFields($geoFields);
		$this->assertEquals($this->_pInstance->getGeoPositionFields(), $geoFields);

		$geoFields = [];
		$this->_pInstance->setGeoPositionFields($geoFields);
		$this->assertEquals($this->_pInstance->getGeoPositionFields(), $geoFields);
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
		$this->_pInstance->setModule('estate');
		$this->_pInstance->setDistinctFields(['objekttyp']);
		$this->_pInstance->setGeoPositionFields(['range']);

		$inputValues = [
			'objektart' => ['wohnung'],
			'vermarktungsart' => ['kauf']
		];

		$this->_pInstance->setInputValues($inputValues);
		$values = [];

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