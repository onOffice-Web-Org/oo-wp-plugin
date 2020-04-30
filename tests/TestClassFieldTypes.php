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

use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Utility\__String;
use ReflectionClass;
use WP_UnitTestCase;

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
		$fieldTypeConstants = array_filter($constants, function(string $key): bool {
			return __String::getNew($key)->startsWith('FIELD_TYPE_');
		}, ARRAY_FILTER_USE_KEY);

		$sanitizers = FieldTypes::getInputVarSanitizers();
		$this->assertCount(count($fieldTypeConstants), $sanitizers);
		$this->assertEqualSets($fieldTypeConstants, array_keys($sanitizers));
	}


	/**
	 *
	 */

	public function testIsNumericType()
	{
		$this->assertTrue(FieldTypes::isNumericType(FieldTypes::FIELD_TYPE_FLOAT));
		$this->assertTrue(FieldTypes::isNumericType(FieldTypes::FIELD_TYPE_INTEGER));
		$this->assertTrue(FieldTypes::isNumericType('urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:decimal'));
		$this->assertTrue(FieldTypes::isNumericType('urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:float'));
		$this->assertTrue(FieldTypes::isNumericType('urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:integer'));
		$this->assertTrue(FieldTypes::isNumericType('urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:int'));
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
		$this->assertTrue(FieldTypes::isRangeType('urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:int'));
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


	/**
	 *
	 */

	public function testIsRegZusatzSearchcritTypes()
	{
		$this->assertTrue(FieldTypes::isRegZusatzSearchcritTypes('displayAll'));
		$this->assertTrue(FieldTypes::isRegZusatzSearchcritTypes('displayLive'));
		$this->assertTrue(FieldTypes::isRegZusatzSearchcritTypes('limitExceeded'));
		$this->assertFalse(FieldTypes::isRegZusatzSearchcritTypes(FieldTypes::FIELD_TYPE_SINGLESELECT));
		$this->assertFalse(FieldTypes::isRegZusatzSearchcritTypes(FieldTypes::FIELD_TYPE_MULTISELECT));
	}

	/**
	 *
	 */
	public function testIsStringType()
	{
		$this->assertTrue(FieldTypes::isStringType(FieldTypes::FIELD_TYPE_TEXT));
		$this->assertTrue(FieldTypes::isStringType(FieldTypes::FIELD_TYPE_VARCHAR));
		$this->assertTrue(FieldTypes::isStringType('urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:varchar'));
		$this->assertTrue(FieldTypes::isStringType('urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:Text'));
		$this->assertFalse(FieldTypes::isStringType(FieldTypes::FIELD_TYPE_SINGLESELECT));
	}
}
