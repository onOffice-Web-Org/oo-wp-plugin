<?php

/**
 *
 *    Copyright (C) 2023 onOffice GmbH
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

declare(strict_types=1);

namespace onOffice\tests;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueEstateCreate;
use onOffice\WPlugin\Field\DefaultValue\ModelToOutputConverter\DefaultValueEstateRowSaver;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\Record\RecordManagerInsertException;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;

class TestClassDefaultValueEstateRowSaver
	extends \WP_UnitTestCase
{
	/** @var array */
	const EXAMPLE_RECORDS = [
		'testBoolean' => '0',
		'testDate' => ['min' => '2023/04/19', 'max' => '2023/04/20'],
		'testDate1' => ['min' => '2023/04/19'],
		'testDate2' => ['max' => '2023/04/20'],
		'testDate3' => [],
		'testMultiSelect' => ['value1', 'value3'],
		'testText' => [
			'native' => 'testEN',
			'de_DE' => 'testDE',
		],
		'testNumericRange1' => ['min' => 3, 'max' => 1337],
		'testNumericRange2' => ['min' => 3],
		'testNumericRange3' => ['max' => 4],
		'testNumericRange4' => [],
	];

	/** @var DefaultValueEstateRowSaver */
	private $_pSubject = null;

	/** @var DefaultValueEstateCreate */
	private $_pDefaultValueCreate = null;

	/** @var Language */
	private $_pLanguage = null;


	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pDefaultValueCreate = $this->getMockBuilder(DefaultValueEstateCreate::class)
			->disableOriginalConstructor()
			->getMock();
		$this->_pLanguage = $this->getMockBuilder(Language::class)
			->getMock();
		$this->_pSubject = new DefaultValueEstateRowSaver($this->_pDefaultValueCreate, $this->_pLanguage);
	}

	/**
	 *
	 */
	public function testSaveDefaultValuesDate()
	{
		$this->_pDefaultValueCreate->expects($this->once())->method('createForDate');
		$pFieldsCollection = $this->buildFieldsCollection();
		$this->_pSubject->saveDefaultValues(13, [
			'testDate' => self::EXAMPLE_RECORDS['testDate'],
		], $pFieldsCollection);
	}

	/**
	 * @throws RecordManagerInsertException
	 * @throws UnknownFieldException
	 */
	public function testSaveDefaultValuesDateMinOnly()
	{
		$this->_pDefaultValueCreate->expects($this->once())->method('createForNumericRange');
		$pFieldsCollection = $this->buildFieldsCollection();
		$this->_pSubject->saveDefaultValues(13, [
			'testInteger' => self::EXAMPLE_RECORDS['testNumericRange2'],
		], $pFieldsCollection);
	}

	/**
	 * @throws RecordManagerInsertException
	 * @throws UnknownFieldException
	 */
	public function testSaveDefaultValuesDateMaxOnly()
	{
		$this->_pDefaultValueCreate->expects($this->once())->method('createForNumericRange');
		$pFieldsCollection = $this->buildFieldsCollection();
		$this->_pSubject->saveDefaultValues(13, [
			'testInteger' => self::EXAMPLE_RECORDS['testNumericRange3'],
		], $pFieldsCollection);
	}

	/**
	 * @throws RecordManagerInsertException
	 * @throws UnknownFieldException
	 */
	public function testSaveDefaultValuesDateEmpty()
	{
		$this->_pDefaultValueCreate->expects($this->never())->method('createForNumericRange');
		$pFieldsCollection = $this->buildFieldsCollection();
		$this->_pSubject->saveDefaultValues(13, [
			'testInteger' => self::EXAMPLE_RECORDS['testNumericRange4'],
		], $pFieldsCollection);
	}
	/**
	 *
	 */
	public function testSaveDefaultValuesText()
	{
		$this->_pDefaultValueCreate->expects($this->once())->method('createForText');
		$pFieldsCollection = $this->buildFieldsCollection();
		$this->_pSubject->saveDefaultValues(13, [
			'testText' => self::EXAMPLE_RECORDS['testText'],
		], $pFieldsCollection);
	}

	/**
	 *
	 */
	public function testSaveDefaultValuesBoolean()
	{
		$this->_pDefaultValueCreate->expects($this->once())->method('createForSingleselect');
		$pFieldsCollection = $this->buildFieldsCollection();
		$this->_pSubject->saveDefaultValues(13, [
			'testBoolean' => self::EXAMPLE_RECORDS['testBoolean'],
		], $pFieldsCollection);
	}

	/**
	 * @throws UnknownFieldException
	 * @throws RecordManagerInsertException
	 */
	public function testSaveDefaultValuesVarchar()
	{
		$this->_pDefaultValueCreate->expects($this->once())->method('createForText');
		$pFieldsCollection = $this->buildFieldsCollection();
		$this->_pSubject->saveDefaultValues(13, [
			'testVarchar' => self::EXAMPLE_RECORDS['testText'],
		], $pFieldsCollection);
	}

	/**
	 * @throws RecordManagerInsertException
	 * @throws UnknownFieldException
	 */
	public function testSaveDefaultValuesIntegerRange()
	{
		$this->_pDefaultValueCreate->expects($this->once())->method('createForNumericRange');
		$pFieldsCollection = $this->buildFieldsCollection();
		$this->_pSubject->saveDefaultValues(13, [
			'testInteger' => self::EXAMPLE_RECORDS['testNumericRange1'],
		], $pFieldsCollection);
	}

	/**
	 * @throws RecordManagerInsertException
	 * @throws UnknownFieldException
	 */
	public function testSaveDefaultValuesIntegerRangeMinOnly()
	{
		$this->_pDefaultValueCreate->expects($this->once())->method('createForNumericRange');
		$pFieldsCollection = $this->buildFieldsCollection();
		$this->_pSubject->saveDefaultValues(13, [
			'testInteger' => self::EXAMPLE_RECORDS['testNumericRange2'],
		], $pFieldsCollection);
	}

	/**
	 * @throws RecordManagerInsertException
	 * @throws UnknownFieldException
	 */
	public function testSaveDefaultValuesIntegerRangeMaxOnly()
	{
		$this->_pDefaultValueCreate->expects($this->once())->method('createForNumericRange');
		$pFieldsCollection = $this->buildFieldsCollection();
		$this->_pSubject->saveDefaultValues(13, [
			'testInteger' => self::EXAMPLE_RECORDS['testNumericRange3'],
		], $pFieldsCollection);
	}

	/**
	 * @throws RecordManagerInsertException
	 * @throws UnknownFieldException
	 */
	public function testSaveDefaultValuesIntegerRangeEmpty()
	{
		$this->_pDefaultValueCreate->expects($this->never())->method('createForNumericRange');
		$pFieldsCollection = $this->buildFieldsCollection();
		$this->_pSubject->saveDefaultValues(13, [
			'testInteger' => self::EXAMPLE_RECORDS['testNumericRange4'],
		], $pFieldsCollection);
	}

	/**
	 * @throws RecordManagerInsertException
	 * @throws UnknownFieldException
	 */
	public function testSaveDefaultValuesFloatRange()
	{
		$this->_pDefaultValueCreate->expects($this->once())->method('createForNumericRange');
		$pFieldsCollection = $this->buildFieldsCollection();
		$this->_pSubject->saveDefaultValues(13, [
			'testFloat' => self::EXAMPLE_RECORDS['testNumericRange1'],
		], $pFieldsCollection);
	}

	/**
	 * @throws RecordManagerInsertException
	 * @throws UnknownFieldException
	 */
	public function testSaveDefaultValuesMultiSelect()
	{
		$this->_pDefaultValueCreate->expects($this->once())->method('createForMultiSelect');
		$pFieldsCollection = $this->buildFieldsCollection();
		$this->_pSubject->saveDefaultValues(13, [
			'testMultiSelect' => self::EXAMPLE_RECORDS['testMultiSelect'],
		], $pFieldsCollection);
	}


	/**
	 * @return FieldsCollection
	 */
	private function buildFieldsCollection(): FieldsCollection
	{
		$pFieldsCollection = new FieldsCollection;
		$pFieldDate = new Field('testDate', onOfficeSDK::MODULE_ESTATE);
		$pFieldDate->setType(FieldTypes::FIELD_TYPE_DATE);
		$pFieldsCollection->addField($pFieldDate);
		$pFieldVarchar = new Field('testVarchar', onOfficeSDK::MODULE_ESTATE);
		$pFieldVarchar->setType(FieldTypes::FIELD_TYPE_VARCHAR);
		$pFieldsCollection->addField($pFieldVarchar);
		$pFieldText = new Field('testText', onOfficeSDK::MODULE_ESTATE);
		$pFieldText->setType(FieldTypes::FIELD_TYPE_TEXT);
		$pFieldsCollection->addField($pFieldText);
		$pFieldInteger = new Field('testInteger', onOfficeSDK::MODULE_ESTATE);
		$pFieldInteger->setIsRangeField(true);
		$pFieldInteger->setType(FieldTypes::FIELD_TYPE_INTEGER);
		$pFieldsCollection->addField($pFieldInteger);
		$pFieldFloat = new Field('testFloat', onOfficeSDK::MODULE_ESTATE);
		$pFieldFloat->setIsRangeField(true);
		$pFieldFloat->setType(FieldTypes::FIELD_TYPE_FLOAT);
		$pFieldsCollection->addField($pFieldFloat);
		$pFieldBool = new Field('testBoolean', onOfficeSDK::MODULE_ESTATE);
		$pFieldBool->setType(FieldTypes::FIELD_TYPE_BOOLEAN);
		$pFieldsCollection->addField($pFieldBool);
		$pFieldMultiSelect = new Field('testMultiSelect', onOfficeSDK::MODULE_ESTATE);
		$pFieldMultiSelect->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
		$pFieldMultiSelect->setPermittedvalues(['value1', 'value2', 'value3']);
		$pFieldsCollection->addField($pFieldMultiSelect);
		return $pFieldsCollection;
	}
}
