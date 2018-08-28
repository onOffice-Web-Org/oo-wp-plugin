<?php

/**
 *
 *    Copyright (C) 2016 onOffice Software AG
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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2016, onOffice(R) Software AG
 *
 */


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


	/** @var array */
	private static $_inputVarSanitizers = array(
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
	);


	/** @var array */
	private static $_numericTypes = array(
		self::FIELD_TYPE_INTEGER,
		self::FIELD_TYPE_FLOAT,
	);


	/**
	 *
	 * @return array
	 *
	 */

	static public function getInputVarSanitizers()
	{
		return self::$_inputVarSanitizers;
	}


	/**
	 *
	 * @param string $type
	 * @return bool
	 *
	 */

	static public function isNumericType($type)
	{
		return in_array($type, self::$_numericTypes, true);
	}


	/**
	 *
	 * @param string $type
	 * @return bool
	 *
	 */

	static public function isDateOrDateTime($type)
	{
		return $type === self::FIELD_TYPE_DATE || $type === self::FIELD_TYPE_DATETIME;
	}
}
