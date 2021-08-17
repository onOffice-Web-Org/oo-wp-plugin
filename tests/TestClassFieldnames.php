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
use onOffice\tests\SDKWrapperMocker;
use onOffice\WPlugin\Field\FieldModuleCollection;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorFormContact;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorGeoPositionFrontend;
use onOffice\WPlugin\Field\FieldnamesEnvironmentTest;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Types\FieldsCollection;
use WP_UnitTestCase;
use function json_decode;

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
	 * @see ticket #1560699
	 */
	public function testGetFieldsWithEmptyLabelUseNameInParenthesis()
	{
		$pFieldnames = $this->getNewFieldnames();
		$pFieldnames->loadLanguage();

		$label = $pFieldnames->getFieldLabel
			('EmptyLabelField', onOfficeSDK::MODULE_ADDRESS);
			$this->assertEquals('(EmptyLabelField)', $label);
		$fieldInformation = $pFieldnames->getFieldInformation
			('EmptyLabelField', onOfficeSDK::MODULE_ADDRESS);
			$this->assertEquals('(EmptyLabelField)', $fieldInformation['label']);
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
		$unknownType = $pFieldnames->getType('unknown', onOfficeSDK::MODULE_ADDRESS);
		var_dump($unknownType);
		$this->assertEquals('unknown', $unknownType);

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
			'label' => 'Country',
			'type' => 'singleselect',
			'default' => null,
			'length' => 250,
			'permittedvalues' => [
				'AUT' => 'Austria',
				'BEL' => 'Belgium',
				'DEU' => 'Germany',
			],
			'content' => 'Form Specific Fields',
			'module' => 'estate',
			'rangefield' => false,
			'additionalTranslations' => [],
			'labelOnlyValues' => [],
			'compoundFields' => [],
		];
		$actualResult = $pFieldNames->getFieldInformation('country', onOfficeSDK::MODULE_ESTATE);
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
		];
	}


	/**
	 *
	 * @param FieldModuleCollection $pExtraFieldsCollection
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
