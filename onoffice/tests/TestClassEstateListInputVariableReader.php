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
use onOffice\WPlugin\Controller\EstateListInputVariableReader;
use onOffice\WPlugin\Controller\EstateListInputVariableReaderConfigTest;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 * @covers \onOffice\WPlugin\Controller\EstateListInputVariableReader
 *
 */

class TestClassEstateListInputVariableReader
	extends WP_UnitTestCase
{
	/** @var string */
	private $_localeBackup = null;


	/**
	 *
	 */

	public function setUp()
	{
		parent::setUp();
		$this->_localeBackup = get_locale();

		$this->switchLocale('de_DE');
	}


	/**
	 *
	 * @param string $locale
	 * @return bool
	 *
	 */

	private function switchLocale(string $locale)
	{
		$pLocaleSwitcher = new WP_Locale_Switcher();
		$pLocaleSwitcher->init();
		return $pLocaleSwitcher->switch_to_locale($locale);
	}


	/**
	 *
	 */

	public function testGetNumericSingle()
	{
		$this->switchLocale('de_DE');
		$pEstateListInputVariableReaderConfig = new EstateListInputVariableReaderConfigTest();
		$module = onOfficeSDK::MODULE_ESTATE;
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('kaufpreis', $module, FieldTypes::FIELD_TYPE_FLOAT);
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('etage', $module, FieldTypes::FIELD_TYPE_INTEGER);
		$pEstateListInputVariableReaderConfig->setValue('kaufpreis', '399,99');
		$pEstateListInputVariableReaderConfig->setValue('etage', '3');

		$pEstateListInputVariableReader = new EstateListInputVariableReader
			($pEstateListInputVariableReaderConfig);

		$valueKaufpreis = $pEstateListInputVariableReader->getFieldValue('kaufpreis');
		$valueEtage = $pEstateListInputVariableReader->getFieldValue('etage');

		$this->assertEquals(399.99, $valueKaufpreis);
		$this->assertEquals(3, $valueEtage);
	}


	/**
	 *
	 */

	public function testGetNumericMultiple()
	{
		$this->switchLocale('de_DE');

		$pEstateListInputVariableReaderConfig = new EstateListInputVariableReaderConfigTest();
		$module = onOfficeSDK::MODULE_ESTATE;
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('kaufpreis', $module, FieldTypes::FIELD_TYPE_FLOAT);
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('etage', $module, FieldTypes::FIELD_TYPE_INTEGER);
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('warmmiete', $module, FieldTypes::FIELD_TYPE_FLOAT);
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('kaltmiete', $module, FieldTypes::FIELD_TYPE_FLOAT);
		$pEstateListInputVariableReaderConfig->setValue('kaufpreis__von', '10');
		$pEstateListInputVariableReaderConfig->setValue('kaufpreis__bis', '32.000,99');
		$pEstateListInputVariableReaderConfig->setValue('etage__von', '3');
		$pEstateListInputVariableReaderConfig->setValue('etage__bis', '9');
		$pEstateListInputVariableReaderConfig->setValue('warmmiete__von', '');
		$pEstateListInputVariableReaderConfig->setValue('warmmiete__bis', '900');
		$pEstateListInputVariableReaderConfig->setValue('kaltmiete__von', '1000');
		$pEstateListInputVariableReaderConfig->setValue('kaltmiete__bis', '');

		$pEstateListInputVariableReader = new EstateListInputVariableReader
			($pEstateListInputVariableReaderConfig);

		$valueKaufpreis = $pEstateListInputVariableReader->getFieldValue('kaufpreis');
		$valueEtage = $pEstateListInputVariableReader->getFieldValue('etage');
		$valueWarmmiete = $pEstateListInputVariableReader->getFieldValue('warmmiete');
		$valueKaltmiete = $pEstateListInputVariableReader->getFieldValue('kaltmiete');

		$this->assertEquals([10, 32000.99], $valueKaufpreis);
		$this->assertEquals([3, 9], $valueEtage);
		$this->assertEquals([0, 900], $valueWarmmiete);
		$this->assertEquals([1000, 0], $valueKaltmiete);
	}


	/**
	 *
	 */

	public function testFloatUSLocale()
	{
		$this->switchLocale('en_US');

		$pEstateListInputVariableReaderConfig = new EstateListInputVariableReaderConfigTest();
		$module = onOfficeSDK::MODULE_ESTATE;
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('kaufpreis', $module, FieldTypes::FIELD_TYPE_FLOAT);
		$pEstateListInputVariableReaderConfig->setValue('kaufpreis', '399.99');

		$pEstateListInputVariableReader = new EstateListInputVariableReader
			($pEstateListInputVariableReaderConfig);

		$valueKaufpreis = $pEstateListInputVariableReader->getFieldValue('kaufpreis');
		$this->assertEquals(399.99, $valueKaufpreis);
	}


	/**
	 *
	 */

	public function testArray()
	{
		$pEstateListInputVariableReaderConfig = new EstateListInputVariableReaderConfigTest();
		$module = onOfficeSDK::MODULE_ESTATE;
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('individualTexts', $module, FieldTypes::FIELD_TYPE_TEXT);

		$values = ['Text1', 'text2', 'text 3'];

		$pEstateListInputVariableReaderConfig->setValueArray('individualTexts', $values);

		$pEstateListInputVariableReader = new EstateListInputVariableReader
			($pEstateListInputVariableReaderConfig);

		$valueTexts = $pEstateListInputVariableReader->getFieldValue('individualTexts');
		$this->assertEquals($values, $valueTexts);
	}


	/**
	 *
	 */

	public function testBool()
	{
		$pEstateListInputVariableReaderConfig = new EstateListInputVariableReaderConfigTest();
		$module = onOfficeSDK::MODULE_ESTATE;
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('kabel_sat_tv', $module, FieldTypes::FIELD_TYPE_BOOLEAN);

		$pEstateListInputVariableReaderConfig->setValue('kabel_sat_tv', 'u');

		$pEstateListInputVariableReader = new EstateListInputVariableReader
			($pEstateListInputVariableReaderConfig);

		$this->assertNull($pEstateListInputVariableReader->getFieldValue('kabel_sat_tv'));
		$pEstateListInputVariableReaderConfig->setValue('kabel_sat_tv', 'y');
		$this->assertTrue($pEstateListInputVariableReader->getFieldValue('kabel_sat_tv'));
		$pEstateListInputVariableReaderConfig->setValue('kabel_sat_tv', 'n');
		$this->assertFalse($pEstateListInputVariableReader->getFieldValue('kabel_sat_tv'));
	}


	/**
	 *
	 */

	public function tearDown()
	{
		parent::tearDown();
		$this->switchLocale($this->_localeBackup);
	}
}
