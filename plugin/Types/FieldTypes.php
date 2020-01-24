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

namespace onOffice\WPlugin\Types;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

abstract class FieldTypes
{
	/** */
	const FIELD_TYPE_MULTISELECT = 'multiselect';

	/** */
	const FIELD_TYPE_SINGLESELECT = 'singleselect';

	/** */
	const FIELD_TYPE_TEXT = 'text';

	/** */
	const FIELD_TYPE_INTEGER = 'integer';

	/** */
	const FIELD_TYPE_VARCHAR = 'varchar';

	/** */
	const FIELD_TYPE_FLOAT = 'float';

	/** */
	const FIELD_TYPE_BOOLEAN = 'boolean';

	/** */
	const FIELD_TYPE_DATE = 'date';

	/** */
	const FIELD_TYPE_BLOB = 'blob';

	/** */
	const FIELD_TYPE_DATETIME = 'datetime';


	/** @var array */
	private static $_inputVarSanitizers = [
		self::FIELD_TYPE_MULTISELECT => FILTER_SANITIZE_STRING,
		self::FIELD_TYPE_SINGLESELECT => FILTER_SANITIZE_STRING,
		self::FIELD_TYPE_TEXT => FILTER_SANITIZE_STRING,
		self::FIELD_TYPE_INTEGER => FILTER_VALIDATE_INT,
		self::FIELD_TYPE_VARCHAR =>FILTER_SANITIZE_STRING,
		self::FIELD_TYPE_FLOAT => FILTER_SANITIZE_STRING, // locale-specific processing needed
		self::FIELD_TYPE_BOOLEAN => FILTER_SANITIZE_STRING, // needs difference between null and false
		self::FIELD_TYPE_DATE => FILTER_SANITIZE_STRING,
		self::FIELD_TYPE_BLOB => FILTER_UNSAFE_RAW,
		self::FIELD_TYPE_DATETIME => FILTER_SANITIZE_STRING,
	];


	/** @var array */
	private static $_numericTypes = [
		self::FIELD_TYPE_INTEGER,
		self::FIELD_TYPE_FLOAT,
	];


	/** @var string[] */
	private static $_rangeTypes = [
		'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:decimal',
		'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:float',
		'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:integer',
		'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:int',
		FieldTypes::FIELD_TYPE_FLOAT,
		FieldTypes::FIELD_TYPE_INTEGER,
	];


	/** @var string[] */
	private static $_multipleSelectTypes = [
		FieldTypes::FIELD_TYPE_MULTISELECT,
		FieldTypes::FIELD_TYPE_SINGLESELECT,
		'displayAll',
		'displayLive',
		'limitExceeded',
	];


	/** @var string[] */
	private static $_regZusatzSearchcritTypes = [
		'displayAll',
		'displayLive',
		'limitExceeded',
	];


	/** @return bool */
	static public function isRegZusatzSearchcritTypes($type): bool
		{ return in_array($type, self::$_regZusatzSearchcritTypes); }


	/**
	 *
	 * @return array
	 *
	 */

	static public function getInputVarSanitizers(): array
	{
		return self::$_inputVarSanitizers;
	}


	/**
	 *
	 * @param string $type
	 * @return bool
	 *
	 */

	static public function isNumericType(string $type): bool
	{
		return in_array($type, self::$_numericTypes, true);
	}


	/**
	 *
	 * @param string $type
	 * @return bool
	 *
	 */

	static public function isDateOrDateTime(string $type): bool
	{
		return $type === self::FIELD_TYPE_DATE || $type === self::FIELD_TYPE_DATETIME;
	}


	/**
	 *
	 * @param string $type
	 * @return bool
	 *
	 */

	static public function isRangeType(string $type): bool
	{
		return in_array($type, self::$_rangeTypes);
	}


	/**
	 *
	 * @param string $type
	 * @return bool
	 *
	 */

	static public function isMultipleSelectType(string $type): bool
	{
		return in_array($type, self::$_multipleSelectTypes);
	}
}