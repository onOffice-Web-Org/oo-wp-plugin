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

use onOffice\WPlugin\Record\RecordManager;
use onOffice\WPlugin\Record\RecordStructure;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 * @covers onOffice\WPlugin\Record\RecordStructure
 *
 */

class TestClassRecordStructure
	extends WP_UnitTestCase
{
	/** @var array */
	private static $_expectationsNull = array(
		RecordManager::TABLENAME_LIST_VIEW => array(
			'name' => false,
			'filterId' => true,
			'sortby' => false,
			'is_reference' => false,
			'show_status' => false,
			'template' => false,
			'expose' => true,
			'recordsPerPage' =>  false,
			'list_type' => false,
		),
		RecordManager::TABLENAME_FIELDCONFIG => array(
			'order' => false,
			'fieldname' =>  false,
		),
		RecordManager::TABLENAME_LISTVIEW_CONTACTPERSON => array(
			'order' =>  false,
			'fieldname' => false,
		),
		RecordManager::TABLENAME_PICTURETYPES => array(
			'picturetype' => false,
		),
		RecordManager::TABLENAME_LIST_VIEW_ADDRESS => array(
			'name' => false,
			'filterId' => true,
			'sortby' => true,
			'sortorder' => false,
			'template' => true,
			'recordsPerPage' => false,
			'showPhoto' => false,
		),
		RecordManager::TABLENAME_FIELDCONFIG_ADDRESS => array(
			'order' => false,
			'fieldname' => false,
		),
		RecordManager::TABLENAME_FORMS => array(
			'name' => false,
			'form_type' => false,
			'template' => false,
			'recipient' => true,
			'subject' => true,
			'createaddress' => false,
			'limitresults' => true,
			'checkduplicates' => false,
			'pages' => false,
		),
	);

	/** @var array */
	private static $_expectationsEmpty =  array(
		RecordManager::TABLENAME_LIST_VIEW => array(
			'name' => '',
			'filterId' =>  0,
			'sortby' => 'ASC',
			'is_reference' => 0,
			'show_status' => 0,
			'template' => '',
			'expose' => '',
			'recordsPerPage' => 5,
			'list_type' => '',
		),
		RecordManager::TABLENAME_FIELDCONFIG => array(
			'order' => 0,
			'fieldname' => '',
		),
		RecordManager::TABLENAME_LISTVIEW_CONTACTPERSON => array(
			'order' => 0,
			'fieldname' => '',
		),
		RecordManager::TABLENAME_PICTURETYPES => array(
			'picturetype' => '',
		),
		RecordManager::TABLENAME_LIST_VIEW_ADDRESS => array(
			'name' => null,
			'filterId' => null,
			'sortby' => null,
			'sortorder' => 'ASC',
			'template' => null,
			'recordsPerPage' => 10,
			'showPhoto' => 0,
		),
		RecordManager::TABLENAME_FIELDCONFIG_ADDRESS => array(
			'order' => 0,
			'fieldname' => '',
		),
		RecordManager::TABLENAME_FORMS => array(
			'name' => null,
			'form_type' => 'contact',
			'template' => null,
			'recipient' => null,
			'subject' => null,
			'createaddress' => 0,
			'limitresults' => null,
			'checkduplicates' => 0,
			'pages' => 0,
		),
	);


	/**
	 *
	 * @covers onOffice\WPlugin\Record\RecordStructure::isNullAllowed
	 *
	 */

	public function testIsNullAllowed()
	{
		foreach (self::$_expectationsNull as $table => $fields) {
			foreach ($fields as $field => $expectedResult) {
				$result = RecordStructure::isNullAllowed($table, $field);
				$this->assertEquals($expectedResult, $result, 'Field: '.$field);
			}
		}
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Record\RecordStructure::getEmptyValue
	 *
	 */

	public function testGetEmptyValue()
	{
		foreach (self::$_expectationsEmpty as $table => $fields) {
			foreach ($fields as $field => $expectedResult) {
				$result = RecordStructure::getEmptyValue($table, $field);
				$this->assertEquals($expectedResult, $result, 'Field: '.$field);
			}
		}
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Record\RecordStructure::getFieldByTable
	 *
	 */

	public function testGetFieldByValue()
	{
		foreach (self::$_expectationsEmpty as $table => $fields) {
			foreach (array_keys($fields) as $field) {
				$result = RecordStructure::getFieldByTable($table, $field);
				$this->assertArrayHasKey(RecordStructure::NULL_ALLOWED, $result, 'Field: '.$field);
				$this->assertArrayHasKey(RecordStructure::EMPTY_VALUE, $result, 'Field: '.$field);
			}
		}
	}
}
