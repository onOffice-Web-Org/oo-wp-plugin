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

use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelDBAdapterRow;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 * @covers \onOffice\WPlugin\Model\InputModelDBAdapterRow
 *
 */

class TestClassInputModelDBAdapterRow
	extends WP_UnitTestCase
{
	/**
	 *
	 * @covers \onOffice\WPlugin\Model\InputModelDBAdapterRow::getPrimaryKeys
	 *
	 */

	public function testGetPrimaryKeys()
	{
		$pInputModelDBAdapterRow = new InputModelDBAdapterRow();
		$primaryKeys = $pInputModelDBAdapterRow->getPrimaryKeys();
		$subset = array(
			'oo_plugin_listviews' => 'listview_id',
			'oo_plugin_forms' => 'form_id',
		);
		$this->assertArraySubset($subset, $primaryKeys);
	}


	/**
	 *
	 * @covers \onOffice\WPlugin\Model\InputModelDBAdapterRow::getForeignKeys
	 *
	 */

	public function testGetForeignKeys()
	{
		$pInputModelDBAdapterRow = new InputModelDBAdapterRow();
		$foreignKeys = $pInputModelDBAdapterRow->getForeignKeys();

		foreach ($foreignKeys as $table => $columns) {
			$this->assertNotInternalType('int', $table);

			foreach (array_keys($columns) as $column) {
				$this->assertInternalType('string', $column);
			}

			$firstColumn = array_shift($columns);
			$this->assertNull($firstColumn);

			foreach ($columns as $column => $foreignColums) {
				$this->assertInternalType('string', $column);
				$this->assertInternalType('array', $foreignColums);
			}
		}
	}


	/**
	 *
	 * @covers \onOffice\WPlugin\Model\InputModelDBAdapterRow::addInputModelDB
	 * @covers \onOffice\WPlugin\Model\InputModelDBAdapterRow::createUpdateValuesByTable
	 *
	 */

	public function testCreateUpdateValuesByTable()
	{
		$pInputModelDBAdapterRow = new InputModelDBAdapterRow();
		$this->addInputModels($pInputModelDBAdapterRow);

		$values = $pInputModelDBAdapterRow->createUpdateValuesByTable();

		$this->assertArrayHasKey('testTable', $values);
		$this->assertArrayHasKey('oo_plugin_forms', $values);
		$this->assertArrayHasKey('oo_plugin_address_fieldconfig', $values);

		$this->assertArraySubset(array(
			'testColumn' => 'asdf',
			'otherTestColumn' => 'bonjour'
		), $values['testTable'], true);

		$this->assertArraySubset(array(
			'createaddress' => 0, // null to intcast here
		), $values['oo_plugin_forms'], true);

		$this->assertArraySubset(array(
			'form_id' => [3],
		), $values['oo_plugin_forms'], true);

		$this->assertArraySubset(array(
			array (
			  'fieldname' => 'test',
			  'listview_address_id' => 1337,
			),
			array (
			  'fieldname' => 'hello',
			  'listview_address_id' => 1337,
			),
		), $values['oo_plugin_address_fieldconfig'], true);
	}


	/**
	 *
	 * @param \onOffice\WPlugin\Model\InputModelDBAdapterRow $pInputModelDBAdapterRow
	 *
	 */

	private function addInputModels(InputModelDBAdapterRow $pInputModelDBAdapterRow)
	{
		$pInputModelDBfictional = new InputModelDB('test', 'Test');
		$pInputModelDBfictional->setTable('testTable');
		$pInputModelDBfictional->setField('testColumn');
		$pInputModelDBfictional->setValue('asdf');
		$pInputModelDBfictional->setMainRecordId(1337);

		$pInputModelDBfictional1 = new InputModelDB('test', 'Test');
		$pInputModelDBfictional1->setTable('testTable');
		$pInputModelDBfictional1->setField('otherTestColumn');
		$pInputModelDBfictional1->setValue('bonjour');
		$pInputModelDBfictional1->setMainRecordId(1337);

		$pInputModelDBforeign = new InputModelDB('test', 'Test');
		$pInputModelDBforeign->setTable('oo_plugin_address_fieldconfig');
		$pInputModelDBforeign->setField('fieldname');
		$pInputModelDBforeign->setMainRecordId(1337);
		$pInputModelDBforeign->setValue(array('test', 'hello'));

		$pInputModelDBforeign2 = new InputModelDB('test', 'Test');
		$pInputModelDBforeign2->setTable('oo_plugin_form_fieldconfig');
		$pInputModelDBforeign2->setField('form_id');
		$pInputModelDBforeign2->setMainRecordId(3);

		$pInputModelDBNullValue = new InputModelDB('test', 'Test');
		$pInputModelDBNullValue->setTable('oo_plugin_forms');
		$pInputModelDBNullValue->setField('createaddress');
		$pInputModelDBNullValue->setMainRecordId(1337);
		$pInputModelDBNullValue->setValue(null); // intcast expected

		$pInputModelDBAdapterRow->addInputModelDB($pInputModelDBfictional);
		$pInputModelDBAdapterRow->addInputModelDB($pInputModelDBfictional1);
		$pInputModelDBAdapterRow->addInputModelDB($pInputModelDBforeign2);
		$pInputModelDBAdapterRow->addInputModelDB($pInputModelDBforeign);
		$pInputModelDBAdapterRow->addInputModelDB($pInputModelDBNullValue);
	}
}
