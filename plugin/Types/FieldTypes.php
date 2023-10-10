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

	/** */
	const FIELD_TYPE_TINYINT = 'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:tinyint';

	/** */
	const FIELD_TYPE_INPUT_DATE = 'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:date';

	/** */
	const FIELD_TYPE_SELECT_USER = 'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:User';

	/** @var array */
	const TYPES_STRING = [
		self::FIELD_TYPE_TEXT,
		self::FIELD_TYPE_VARCHAR,
		'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:varchar',
		'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:Text',
	];

	/** @var array */
	const INPUT_VAR_SANITIZERS = [
		self::FIELD_TYPE_MULTISELECT => FILTER_SANITIZE_STRING,
		self::FIELD_TYPE_SINGLESELECT => FILTER_SANITIZE_STRING,
		self::FIELD_TYPE_TEXT => FILTER_SANITIZE_STRING,
		self::FIELD_TYPE_INTEGER => FILTER_VALIDATE_INT,
		self::FIELD_TYPE_VARCHAR => FILTER_SANITIZE_STRING,
		self::FIELD_TYPE_FLOAT => FILTER_SANITIZE_STRING, // locale-specific processing needed
		self::FIELD_TYPE_BOOLEAN => FILTER_SANITIZE_STRING, // needs difference between null and false
		self::FIELD_TYPE_DATE => FILTER_SANITIZE_STRING,
		self::FIELD_TYPE_BLOB => FILTER_UNSAFE_RAW,
		self::FIELD_TYPE_DATETIME => FILTER_SANITIZE_STRING,
		self::FIELD_TYPE_SELECT_USER => FILTER_SANITIZE_STRING,
		self::FIELD_TYPE_TINYINT => FILTER_VALIDATE_INT,
		self::FIELD_TYPE_INPUT_DATE => FILTER_SANITIZE_STRING
	];

	/** @var array */
	const TYPES_NUMERIC = [
		'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:decimal',
		'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:float',
		'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:integer',
		'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:int',
		self::FIELD_TYPE_INTEGER,
		self::FIELD_TYPE_FLOAT,
	];

	/** @var string[] */
	const TYPES_RANGE = [
		'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:decimal',
		'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:float',
		'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:integer',
		'urn:onoffice-de-ns:smart:2.5:dbAccess:dataType:int',
		FieldTypes::FIELD_TYPE_FLOAT,
		FieldTypes::FIELD_TYPE_INTEGER,
	];

	/** @var string[] */
	const TYPES_MULTI_SELECT = [
		FieldTypes::FIELD_TYPE_MULTISELECT,
		FieldTypes::FIELD_TYPE_SINGLESELECT,
		'displayAll',
		'displayLive',
		'limitExceeded',
	];

	/** @var string[] */
	const TYPES_REG_ADDITION_SEARCHCRITERIA = [
		'displayAll',
		'displayLive',
		'limitExceeded',
	];

	const TYPES_SUPPORT = [
		'integer',
		'float',
		'varchar',
		'text',
		'date',
		'datetime',
		'boolean'
	];

	/**
	 * @param string $type
	 * @return bool
	 */
	static public function isRegZusatzSearchcritTypes(string $type): bool
	{
		return in_array($type, self::TYPES_REG_ADDITION_SEARCHCRITERIA);
	}

	/**
	 * @return array
	 */
	static public function getInputVarSanitizers(): array
	{
		return self::INPUT_VAR_SANITIZERS;
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	static public function isSupportType(string $type): bool
	{
		return in_array($type, self::TYPES_SUPPORT, true);
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	static public function isNumericType(string $type): bool
	{
		return in_array($type, self::TYPES_NUMERIC, true);
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	static public function isDateOrDateTime(string $type): bool
	{
		return $type === self::FIELD_TYPE_DATE || $type === self::FIELD_TYPE_DATETIME;
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	static public function isRangeType(string $type): bool
	{
		return in_array($type, self::TYPES_RANGE);
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	static public function isMultipleSelectType(string $type): bool
	{
		return in_array($type, self::TYPES_MULTI_SELECT);
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	static public function isStringType(string $type): bool
	{
		return in_array($type, self::TYPES_STRING);
	}
}