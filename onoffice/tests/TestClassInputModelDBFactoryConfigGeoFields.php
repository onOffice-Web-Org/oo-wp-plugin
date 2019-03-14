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

namespace onOffice\tests;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigBase;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigGeoFields;
use onOffice\WPlugin\Utility\__String;
use ReflectionClass;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassInputModelDBFactoryConfigGeoFields
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGetConfigEstate()
	{
		$pSubject = new InputModelDBFactoryConfigGeoFields(onOfficeSDK::MODULE_ESTATE);
		$config = $pSubject->getConfig();
		$this->checkConfigForTableAndField('oo_plugin_listviews', $config);
	}


	/**
	 *
	 */

	public function testGetConfigSearchCriteria()
	{
		$pSubject = new InputModelDBFactoryConfigGeoFields(onOfficeSDK::MODULE_SEARCHCRITERIA);
		$config = $pSubject->getConfig();
		$this->checkConfigForTableAndField('oo_plugin_forms', $config);
	}


	/**
	 *
	 * @expectedException \onOffice\WPlugin\Controller\Exception\UnknownModuleException
	 *
	 */

	public function testGetConfigUnknownModule()
	{
		$pSubject = new InputModelDBFactoryConfigGeoFields('unknown');
		$pSubject->getConfig();
	}


	/**
	 *
	 */

	public function testGetBooleanFields()
	{
		$pSubject = new InputModelDBFactoryConfigGeoFields(onOfficeSDK::MODULE_ESTATE);
		$this->assertEquals($this->getExpectedFields(), $pSubject->getBooleanFields());
	}


	/**
	 *
	 * @param string $table
	 * @param array $fields
	 *
	 */

	private function checkConfigForTableAndField(string $table, array $fields)
	{
		$constantValues = $this->getExpectedFields();
		$tablesReference = array_fill(0, count($fields), $table);
		$this->assertEquals(array_column($fields, InputModelDBFactoryConfigBase::KEY_FIELD), $constantValues);
		$this->assertEquals(array_column($fields, InputModelDBFactoryConfigBase::KEY_TABLE), $tablesReference);
		$this->assertEquals(array_keys($fields), $constantValues);
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getExpectedFields(): array
	{
		$pReflection = new ReflectionClass(InputModelDBFactoryConfigGeoFields::class);
		$constants = array_filter($pReflection->getConstants(), function($input) {
			return __String::getNew($input)->startsWith('FIELDNAME_');
		}, ARRAY_FILTER_USE_KEY);
		return array_values($constants);
	}
}
