<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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

namespace onOffice\WPlugin\Record;

use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Model\InputModelBase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */
class RecordStructure
{
	/** */
	const HTML_TYPE = 'htmlType';

	/** */
	const NULL_ALLOWED = 'nullAllowed';

	/** */
	const EMPTY_VALUE = 'emptyValue';


	/** @var array */
	static private $_fieldsListview = array
		(
			'name' => array
				(
					self::NULL_ALLOWED => false,
					self::HTML_TYPE => InputModelBase::HTML_TYPE_TEXT,
					self::EMPTY_VALUE => '',
				),

			'filterId' => array
				(
					self::NULL_ALLOWED => true,
					self::HTML_TYPE => InputModelBase::HTML_TYPE_SELECT,
					self::EMPTY_VALUE => 0,
				),

			'sortby' => array
				(
					self::NULL_ALLOWED => false,
					self::HTML_TYPE => InputModelBase::HTML_TYPE_SELECT,
					self::EMPTY_VALUE => 'ASC',
				),

			'is_reference' => array
				(
					self::NULL_ALLOWED => false,
					self::HTML_TYPE => InputModelBase::HTML_TYPE_CHECKBOX,
					self::EMPTY_VALUE => 0,
				),

			'show_status' => array
				(
					self::NULL_ALLOWED => false,
					self::HTML_TYPE => InputModelBase::HTML_TYPE_CHECKBOX,
					self::EMPTY_VALUE => 0,
				),

			'template' => array
				(
					self::NULL_ALLOWED => false,
					self::HTML_TYPE => InputModelBase::HTML_TYPE_SELECT,
					self::EMPTY_VALUE => '',
				),

			'expose' => array
				(
					self::NULL_ALLOWED => true,
					self::HTML_TYPE => InputModelBase::HTML_TYPE_SELECT,
					self::EMPTY_VALUE => '',
				),

			'recordsPerPage' => array
				(
					self::NULL_ALLOWED => false,
					self::HTML_TYPE => InputModelBase::HTML_TYPE_SELECT,
					self::EMPTY_VALUE => 5,
				),

			'list_type' => array
				(
					self::NULL_ALLOWED => false,
					self::HTML_TYPE => InputModelBase::HTML_TYPE_SELECT,
					self::EMPTY_VALUE => '',
				)
		);

	/** @var array */
	static private $_fieldsFieldconfig = array
		(
			'order' => array
				(
					self::NULL_ALLOWED => false,
					self::HTML_TYPE => null,
					self::EMPTY_VALUE => 0,
				),

			'fieldname' => array
				(
					self::NULL_ALLOWED => false,
					self::HTML_TYPE => InputModelBase::HTML_TYPE_CHECKBOX,
					self::EMPTY_VALUE => '',
				),
		);

	/** @var array */
	static private $_fieldsPicturetypes = array
		(
			'picturetype' => array
				(
					self::HTML_TYPE => InputModelBase::HTML_TYPE_CHECKBOX,
					self::NULL_ALLOWED => false,
					self::EMPTY_VALUE => '',
				),
		);


	/**
	 *
	 * @param string $table
	 * @param string $field
	 * @return string
	 *
	 */

	public static function getFieldByTable($table, $field)
	{
		$fields = null;

		switch ($table)
		{
			case RecordManager::TABLENAME_LIST_VIEW:
				$fields = self::$_fieldsListview;
				break;

			case RecordManager::TABLENAME_FIELDCONFIG:
			case RecordManager::TABLENAME_LISTVIEW_CONTACTPERSON:
				$fields = self::$_fieldsFieldconfig;
				break;

			case RecordManager::TABLENAME_PICTURETYPES:
				$fields = self::$_fieldsPicturetypes;
				break;
		}

		if (null != $fields)
		{
			if (array_key_exists($field, $fields))
			{
				return $fields[$field];
			}
		}
	}


	/**
	 *
	 * @param string $table
	 * @param string $field
	 * @return bool
	 *
	 */

	public static function isNullAllowed($table, $field)
	{
		$returnValue = false;
		$fieldValues = self::getFieldByTable($table, $field);

		if (is_array($fieldValues))
		{
			$returnValue = $fieldValues[self::NULL_ALLOWED];
		}

		return $returnValue;
	}



	/**
	 *
	 * @param string $table
	 * @param string $field
	 * @return mixed
	 *
	 */
	
	public static function getEmptyValue($table, $field)
	{
		$returnValue = false;

		$fieldValues = self::getFieldByTable($table, $field);

		if (is_array($fieldValues))
		{
			$returnValue = $fieldValues[self::EMPTY_VALUE];
		}

		return $returnValue;
	}
}
