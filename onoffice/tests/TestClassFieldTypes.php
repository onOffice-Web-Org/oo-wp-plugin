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

use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassFieldTypes
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testSanitizers()
	{
		$pReflection = new ReflectionClass(FieldTypes::class);
		$constants = $pReflection->getConstants();

		$sanitizers = FieldTypes::getInputVarSanitizers();

		$this->assertCount(count($constants), $sanitizers);
		$this->assertEqualSets($constants, array_keys($sanitizers));
	}


	/**
	 *
	 */

	public function testIsNumericType()
	{
		$this->assertTrue(FieldTypes::isNumericType(FieldTypes::FIELD_TYPE_FLOAT));
		$this->assertTrue(FieldTypes::isNumericType(FieldTypes::FIELD_TYPE_INTEGER));
		$this->assertFalse(FieldTypes::isNumericType(FieldTypes::FIELD_TYPE_BLOB));
	}


	/**
	 *
	 */

	public function testIsDateOrDateTime()
	{
		$this->assertTrue(FieldTypes::isDateOrDateTime(FieldTypes::FIELD_TYPE_DATE));
		$this->assertTrue(FieldTypes::isDateOrDateTime(FieldTypes::FIELD_TYPE_DATETIME));
		$this->assertFalse(FieldTypes::isDateOrDateTime(FieldTypes::FIELD_TYPE_BOOLEAN));
	}


	/**
	 *
	 */

	public function testIsRangeType()
	{
		$this->assertTrue(FieldTypes::isRangeType('urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:decimal'));
		$this->assertTrue(FieldTypes::isRangeType('urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:float'));
		$this->assertTrue(FieldTypes::isRangeType('urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:integer'));
		$this->assertTrue(FieldTypes::isRangeType(FieldTypes::FIELD_TYPE_FLOAT));
		$this->assertTrue(FieldTypes::isRangeType(FieldTypes::FIELD_TYPE_INTEGER));
		$this->assertFalse(FieldTypes::isRangeType(FieldTypes::FIELD_TYPE_MULTISELECT));
		$this->assertFalse(FieldTypes::isRangeType(FieldTypes::FIELD_TYPE_TEXT));
	}


	/**
	 *
	 */

	public function testIsMultipleSelectType()
	{
		$this->assertTrue(FieldTypes::isMultipleSelectType(FieldTypes::FIELD_TYPE_SINGLESELECT));
		$this->assertTrue(FieldTypes::isMultipleSelectType(FieldTypes::FIELD_TYPE_MULTISELECT));
		$this->assertFalse(FieldTypes::isMultipleSelectType(FieldTypes::FIELD_TYPE_VARCHAR));
	}
}
