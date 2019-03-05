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

use onOffice\SDK\onOfficeSDK;
use onOffice\tests\SDKWrapperMocker;
use onOffice\WPlugin\Field\FieldModuleCollection;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorFormContact;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorGeoPositionFrontend;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorReadAddress;
use onOffice\WPlugin\Field\FieldnamesEnvironmentTest;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\Types\FieldsCollection;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassFieldnames
	extends WP_UnitTestCase
{
	/** @var FieldnamesEnvironmentTest */
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


	/**
	 *
	 */

	public function testConstruct()
	{
		$pFieldnamesDefault = $this->getNewFieldnames();
		$this->assertFalse($pFieldnamesDefault->getInactiveOnly());

		$pFieldnames = $this->getNewFieldnames(null, true);
		$this->assertTrue($pFieldnames->getInactiveOnly());
	}


	/**
	 *
	 */

	public function testLoadLanguage()
	{
		$pFieldnames = $this->getNewFieldnames();
		$pFieldnames->loadLanguage();

		foreach ($this->getFullModuleList() as $module) {
			$fieldListOfModule = $pFieldnames->getFieldList($module);
			$this->assertTrue(count($fieldListOfModule) > 0);
		}
	}


	/**
	 *
	 */

	public function testGetFields()
	{
		$pFieldnames = $this->getNewFieldnames();
		$pFieldnames->loadLanguage();

		foreach ($this->getFullModuleList() as $module) {
			$fieldListOfModule = $pFieldnames->getFieldList($module);
			$this->assertTrue(count($fieldListOfModule) > 0);
				$this->checkFieldListStructure($fieldListOfModule, $module);
		}
	}


	/**
	 *
	 * @param array $fieldListOfModule
	 * @param string $module
	 *
	 */

	private function checkFieldListStructure(array $fieldListOfModule, string $module)
	{
		foreach ($fieldListOfModule as $fieldName => $fieldProperties) {
			$message = 'Field: '.$fieldName.', module: '.$module;
			$this->assertTrue(strlen($fieldName) > 0);
			$this->assertArrayHasKey('type', $fieldProperties, $message);
			$this->assertArrayHasKey('length', $fieldProperties, $message);
			$this->assertArrayHasKey('permittedvalues', $fieldProperties, $message);
			$this->assertArrayHasKey('default', $fieldProperties, $message);
			$this->assertArrayHasKey('label', $fieldProperties, $message);
			$this->assertArrayHasKey('tablename', $fieldProperties, $message);
			$this->assertArrayHasKey('content', $fieldProperties, $message);
			$this->assertEquals($fieldProperties['module'], $module, $message);
		}
	}


	/**
	 *
	 */

	public function testGetFieldInformation()
	{
		$pFieldnames = $this->getNewFieldnames();
		$pFieldnames->loadLanguage();

		$information = $pFieldnames->getFieldInformation('kaufpreis', onOfficeSDK::MODULE_ESTATE);
		$expectedInformation = [
			'type' => 'float',
			'length' => null,
			'permittedvalues' => null,
			'default' => null,
			'label' => 'Purchase price',
			'tablename' => 'ObjPreise',
			'content' => 'Preise',
			'module' => 'estate',
		];
		$this->assertEqualSets($expectedInformation, $information);

	}


	/**
	 *
	 * @expectedException \onOffice\WPlugin\Field\UnknownFieldException
	 *
	 */

	public function testGetFieldInformationUnknown()
	{
		$pFieldnames = $this->getNewFieldnames();
		$pFieldnames->loadLanguage();
		// get unknown field
		$pFieldnames->getFieldInformation('unknown', 'test');
	}


	/**
	 *
	 */

	public function testGetFieldLabel()
	{
		$pFieldnames = $this->getNewFieldnames();
		$pFieldnames->loadLanguage();
		$labelKabelSat = $pFieldnames->getFieldLabel('kabel_sat_tv', onOfficeSDK::MODULE_ESTATE);
		$this->assertEquals('Cable/satellite TV', $labelKabelSat);

		$labelFirstname = $pFieldnames->getFieldLabel('Vorname', onOfficeSDK::MODULE_ADDRESS);
		$this->assertEquals('First name', $labelFirstname);

		$labelRegion = $pFieldnames->getFieldLabel('regionaler_zusatz', onOfficeSDK::MODULE_SEARCHCRITERIA);
		$this->assertEquals('Regional addition', $labelRegion);
	}


	/**
	 *
	 */

	public function testGetFieldLabelUnknown()
	{
		$pFieldnames = $this->getNewFieldnames();
		$pFieldnames->loadLanguage();
		$unknownLabel = $pFieldnames->getFieldLabel('unknown', onOfficeSDK::MODULE_ESTATE);
		$this->assertEquals('unknown', $unknownLabel);
	}


	/**
	 *
	 */

	public function testGetFieldWithMode()
	{
		$module = onOfficeSDK::MODULE_SEARCHCRITERIA;
		$pFieldnames = $this->getNewFieldnames();
		$pFieldnames->loadLanguage();
		$fieldList = $pFieldnames->getFieldList($module, GeoPosition::MODE_TYPE_ADMIN_SEARCH_CRITERIA);
		$this->assertArrayHasKey('geoPosition', $fieldList);
		$this->assertGreaterThanOrEqual(5, count($fieldList));
		$this->checkFieldListStructure($fieldList, $module);
	}


	/**
	 *
	 */

	public function testGetModuleContainsField()
	{
		$pFieldnames = $this->getNewFieldnames();
		$pFieldnames->loadLanguage();
		$moduleEstate = onOfficeSDK::MODULE_ESTATE;
		$moduleAddress = onOfficeSDK::MODULE_ADDRESS;
		$moduleSearchCriteria = onOfficeSDK::MODULE_SEARCHCRITERIA;
		$this->assertTrue($pFieldnames->getModuleContainsField('kaufpreis', $moduleEstate));
		$this->assertTrue($pFieldnames->getModuleContainsField('kaufpreis', $moduleSearchCriteria));
		$this->assertFalse($pFieldnames->getModuleContainsField('kaufpreis__von', $moduleSearchCriteria));
		$this->assertTrue($pFieldnames->getModuleContainsField('Vorname', $moduleAddress));
		$this->assertTrue($pFieldnames->getModuleContainsField('Beziehung', $moduleAddress));
		$this->assertTrue($pFieldnames->getModuleContainsField('range_ort', $moduleSearchCriteria));
		$this->assertTrue($pFieldnames->getModuleContainsField('range', $moduleSearchCriteria));

		$this->assertFalse($pFieldnames->getModuleContainsField('newsletter', $moduleAddress));
		$this->assertFalse($pFieldnames->getModuleContainsField('defaultphone', $moduleAddress));

		$pFieldsCollection = new FieldModuleCollectionDecoratorReadAddress
			(new FieldModuleCollectionDecoratorFormContact(new FieldsCollection()));

		$pFieldnamesWithApiOnly = $this->getNewFieldnames($pFieldsCollection);
		$pFieldnamesWithApiOnly->loadLanguage();
		$this->assertTrue($pFieldnamesWithApiOnly->getModuleContainsField('newsletter', $moduleAddress));
		$this->assertTrue($pFieldnamesWithApiOnly->getModuleContainsField('defaultphone', $moduleAddress));
	}


	/**
	 *
	 */

	public function testGetPermittedValues()
	{
		$pFieldnames = $this->getNewFieldnames();
		$pFieldnames->loadLanguage();
		$resultBefeuerung = $pFieldnames->getPermittedValues('befeuerung', onOfficeSDK::MODULE_ESTATE);

		$expectationBefeuerung = [
			'luftwp' => 'air/water heat pump',
			'pellet' => 'Pellet',
			'oel' => 'Oil',
			'gas' => 'Gas',
			'elektro' => 'Electrical',
			'alternativ' => 'Alternative',
			'solar' => 'Solar',
			'erdwaerme' => 'Geothermal',
		];

		$this->assertEquals($expectationBefeuerung, $resultBefeuerung);

		$resultNoSelect = $pFieldnames->getPermittedValues('Vorname', onOfficeSDK::MODULE_ADDRESS);
		$this->assertEquals([], $resultNoSelect);
	}


	/**
	 *
	 */

	public function testGetRangeSearchcriteriaInfosForField()
	{
		$pFieldnames = $this->getNewFieldnames();
		$pFieldnames->loadLanguage();

		$kaufpreisRangeExpectation = [
			'kaufpreis__von' => 'min. Sales price',
			'kaufpreis__bis' => 'max. Sales price',
		];

		$kaufpreisRangeResult = $pFieldnames->getRangeSearchcriteriaInfosForField('kaufpreis');
		$this->assertEquals($kaufpreisRangeExpectation, $kaufpreisRangeResult);
		$this->assertEquals([], $pFieldnames->getRangeSearchcriteriaInfosForField('Vorname'));
	}


	/**
	 *
	 */

	public function testGetSearchcriteriaRangeInfos()
	{
		$pFieldnames = $this->getNewFieldnames();
		$pFieldnames->loadLanguage();

		$expectation = [
			'kaufpreis' => [
				'kaufpreis__von' => 'min. Sales price',
				'kaufpreis__bis' => 'max. Sales price',
			],
			'kaltmiete' => [
				'kaltmiete__von' => 'Kaltmiete von',
				'kaltmiete__bis' => 'Kaltmiete bis',
			],
			'ust_satz_bk' => [
				'ust_satz_bk__von' => 'Ust. -Satz BK von',
				'ust_satz_bk__bis' => 'Ust. -Satz BK bis',
			],
			'wohnflaeche' => [
				'wohnflaeche__von' => 'Living space from',
				'wohnflaeche__bis' => 'Living space (max)',
			],
		];

		$this->assertEquals($expectation, $pFieldnames->getSearchcriteriaRangeInfos());
	}


	/**
	 *
	 */

	public function testGetType()
	{
		$pFieldnamesDefault = $this->getNewFieldnames(new FieldsCollection());
		$pFieldnamesDefault->loadLanguage();
		$this->assertEquals('float', $pFieldnamesDefault->getType('kaufpreis', onOfficeSDK::MODULE_ESTATE));
		$this->assertEquals('multiselect', $pFieldnamesDefault->getType('ArtDaten', onOfficeSDK::MODULE_ADDRESS));

		$pFieldnamesContact = $this->getNewFieldnames
			(new FieldModuleCollectionDecoratorFormContact(new FieldsCollection()));
		$pFieldnamesContact->loadLanguage();
		$this->assertEquals('float', $pFieldnamesContact->getType('kaufpreis', onOfficeSDK::MODULE_ESTATE));
		$this->assertEquals('multiselect', $pFieldnamesContact->getType('ArtDaten', onOfficeSDK::MODULE_ADDRESS));
		$this->assertEquals('boolean', $pFieldnamesContact->getType('newsletter', onOfficeSDK::MODULE_ADDRESS));
	}


	/**
	 *
	 * @expectedException \onOffice\WPlugin\Field\UnknownFieldException
	 *
	 */

	public function testGetTypeUnknown()
	{
		$pFieldnames = $this->getNewFieldnames();
		$pFieldnames->loadLanguage();
		$pFieldnames->getType('Unknown', onOfficeSDK::MODULE_ADDRESS);
	}


	/**
	 *
	 */

	public function testGetUmkreisFields()
	{
		$pFieldnames = $this->getNewFieldnames();
		$pFieldnames->loadLanguage();
		$rangeFields = $pFieldnames->getUmkreisFields();
		$this->assertArrayHasKey('range_plz', $rangeFields);
		$this->assertArrayHasKey('range_ort', $rangeFields);
		$this->assertArrayHasKey('range_strasse', $rangeFields);
		$this->assertArrayHasKey('range_hausnummer', $rangeFields);
		$this->assertArrayHasKey('range', $rangeFields);
		$this->assertArrayHasKey('range_land', $rangeFields);
		$this->checkFieldListStructure($rangeFields, onOfficeSDK::MODULE_SEARCHCRITERIA);
	}


	/**
	 *
	 */

	public function testInRangeSearchcriteriaInfos()
	{
		$pFieldnames = $this->getNewFieldnames();
		$pFieldnames->loadLanguage();
		$this->assertTrue($pFieldnames->inRangeSearchcriteriaInfos('kaufpreis'));
		$this->assertTrue($pFieldnames->inRangeSearchcriteriaInfos('wohnflaeche'));
		$this->assertFalse($pFieldnames->inRangeSearchcriteriaInfos('Id'));
		$this->assertFalse($pFieldnames->inRangeSearchcriteriaInfos('ort'));
	}


	/**
	 *
	 */

	public function testMergeCountryForEstate()
	{
		$pCollection = new FieldModuleCollectionDecoratorGeoPositionFrontend(new FieldsCollection());
		$pFieldNames = $this->getNewFieldnames($pCollection);
		$pFieldNames->loadLanguage();
		$expectedResult = [
			'AUT' => 'Austria',
			'BEL' => 'Belgium',
			'DEU' => 'Germany',
		];
		$actualResult = $pFieldNames->getPermittedValues('country', onOfficeSDK::MODULE_ESTATE);
		$this->assertEquals($expectedResult, $actualResult);
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getFullModuleList(): array
	{
		return [
			onOfficeSDK::MODULE_ADDRESS,
			onOfficeSDK::MODULE_ESTATE,
			onOfficeSDK::MODULE_SEARCHCRITERIA,
		];
	}


	/**
	 *
	 * @param bool $addApiOnlyFields
	 * @param bool $internalAnnotations
	 * @param bool $inactiveOnly
	 * @return Fieldnames
	 *
	 */

	private function getNewFieldnames(
		FieldModuleCollection $pExtraFieldsCollection = null,
		bool $inactiveOnly = false): Fieldnames
	{
		if ($pExtraFieldsCollection === null) {
			$pExtraFieldsCollection = new FieldsCollection();
		}

		$pFieldnames = new Fieldnames
			($pExtraFieldsCollection, $inactiveOnly, $this->_pFieldnamesEnvironment);
		return $pFieldnames;
	}
}
