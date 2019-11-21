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

declare(strict_types=1);

namespace onOffice\tests;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueCreate;
use onOffice\WPlugin\Field\DefaultValue\ModelToOutputConverter\DefaultValueRowSaver;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\Record\RecordManagerInsertException;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;

class TestClassDefaultValueRowSaver
	extends \WP_UnitTestCase
{
	/** @var array */
	const EXAMPLE_RECORDS = [
		'testSingleselect' => 'singlevalue',
		'testText' => [
			'native' => 'testEN',
			'de_DE' => 'testDE',
		],
	];

	/** @var DefaultValueRowSaver */
	private $_pSubject = null;

	/** @var DefaultValueCreate */
	private $_pDefaultValueCreate = null;

	/** @var Language */
	private $_pLanguage = null;


	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pDefaultValueCreate = $this->getMockBuilder(DefaultValueCreate::class)
			->disableOriginalConstructor()
			->getMock();
		$this->_pLanguage = $this->getMockBuilder(Language::class)
			->getMock();
		$this->_pSubject = new DefaultValueRowSaver($this->_pDefaultValueCreate, $this->_pLanguage);
	}

	/**
	 *
	 */
	public function testSaveDefaultValuesSingleSelect()
	{
		$this->_pDefaultValueCreate->expects($this->once())->method('createForSingleselect');
		$pFieldsCollection = $this->buildFieldsCollection();
		$this->_pSubject->saveDefaultValues(13, [
			'testSingleselect' => self::EXAMPLE_RECORDS['testSingleselect'],
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
	 * @return FieldsCollection
	 */
	private function buildFieldsCollection(): FieldsCollection
	{
		$pFieldsCollection = new FieldsCollection;
		$pFieldSingleSelect = new Field('testSingleselect', onOfficeSDK::MODULE_ESTATE);
		$pFieldSingleSelect->setType(FieldTypes::FIELD_TYPE_SINGLESELECT);
		$pFieldsCollection->addField($pFieldSingleSelect);
		$pFieldVarchar = new Field('testVarchar', onOfficeSDK::MODULE_ESTATE);
		$pFieldVarchar->setType(FieldTypes::FIELD_TYPE_VARCHAR);
		$pFieldsCollection->addField($pFieldVarchar);
		$pFieldText = new Field('testText', onOfficeSDK::MODULE_ESTATE);
		$pFieldText->setType(FieldTypes::FIELD_TYPE_TEXT);
		$pFieldsCollection->addField($pFieldText);
		return $pFieldsCollection;
	}
}
