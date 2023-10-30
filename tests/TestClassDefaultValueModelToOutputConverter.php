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

use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueRead;
use onOffice\WPlugin\Field\DefaultValue\ModelToOutputConverter\DefaultValueModelToOutputConverter;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldTypes;
use WP_UnitTestCase;

/**
 *
 */

class TestClassDefaultValueModelToOutputConverter extends WP_UnitTestCase
{
	/** @var DefaultValueModelToOutputConverter */
	private $_pSubject = null;

	/** @var Field */
	private $_pField = null;

	/** @var Container */
	private $_pContainer = null;

	/**
	 *
	 * @before
	 * @throws DependencyException
	 * @throws NotFoundException
	 *
	 */

	public function prepare()
	{
		$pDefaultValueReader = $this->getMockBuilder(DefaultValueRead::class)
			->disableOriginalConstructor()
			->getMock();

		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainerBuilder->addDefinitions([DefaultValueRead::class => $pDefaultValueReader]);
		$this->_pContainer = $pContainerBuilder->build();

		$this->_pField = new Field('testField', 'testModule');
		$this->_pSubject = $this->_pContainer->get(DefaultValueModelToOutputConverter::class);
	}


	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedMultiFieldsForNonEmptyNumericRangeField()
	{
		$row = [
			(object)[
				'defaults_id' => '13',
				'value' => '11.0',
				'fieldname' => 'testField',
			],
			(object)[
				'defaults_id' => '13',
				'value' => '14.0',
				'fieldname' => 'testField',
			],
			(object)[
				'defaults_id' => '16',
				'value' => 'abc',
				'fieldname' => 'fieldname4',
				'type' => 'singleselect',
				'locale' => 'en_US',
			],
			(object)[
				'defaults_id' => '17',
				'value' => 'abc',
				'fieldname' => 'fieldname5',
				'type' => 'singleselect',
				'locale' => 'en_US',
			],
		];
		$pDefaultValueReader = $this->_pContainer->get(DefaultValueRead::class);
		$this->_pField->setIsRangeField(true);
		$pDefaultValueReader->expects($this->once())
			->method('readDefaultMultiValuesSingleSelect')->will($this->returnValue($row));
		$result = $this->_pSubject->getConvertedMultiFields(13, [$this->_pField]);
		$this->assertEquals(['testField__von'=>'11.0', 'testField__bis'=>'14.0', 'testField'=>''], $result);
	}


	/**
	 *
	 * @throws DependencyException
	 * @throws NotFoundException
	 *
	 */

	public function testGetConvertedMultiFieldsForNonEmptySingleSelectField()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_SINGLESELECT);
		$row = [
			(object)[
				'defaults_id' => '13',
				'value' => 'Monday',
				'fieldname' => 'testField',
				'type' => 'date',
				'locale' => 'en_US',
			],
		];
		$pDefaultValueReader = $this->_pContainer->get(DefaultValueRead::class);
		$pDefaultValueReader->expects($this->once())
			->method('readDefaultMultiValuesSingleSelect')->will($this->returnValue($row));
		$result = $this->_pSubject->getConvertedMultiFields(13, [$this->_pField]);
		$this->assertEquals(['testField'=> 'Monday'], $result);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedMultiFieldsForNonEmptyMultiSelectField()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
		$row = [
			(object)[
				'defaults_id' => '13',
				'value' => 'Monday',
				'fieldname' => 'testField',
				'type' => 'date',
				'locale' => '',
			],
			(object)[
				'defaults_id' => '13',
				'value' => 'Tuesday',
				'fieldname' => 'testField',
				'type' => 'date',
				'locale' => '',
			],
			(object)[
				'defaults_id' => '13',
				'value' => 'Wednesday',
				'fieldname' => 'testField',
				'type' => 'date',
				'locale' => '',
			],
		];
		$pDefaultValueReader = $this->_pContainer->get(DefaultValueRead::class);
		$pDefaultValueReader->expects($this->once())
			->method('readDefaultMultiValuesSingleSelect')->will($this->returnValue($row));
		$result = $this->_pSubject->getConvertedMultiFields(13, [$this->_pField]);
		$this->assertEquals(['testField'=> ['Monday','Tuesday','Wednesday']], $result);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedMultiFieldsForNonEmptyBoolField()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_BOOLEAN);
		$row = [
			(object)[
				'defaults_id' => '13',
				'value' => true,
				'fieldname' => 'testField',
				'type' => 'boolean',
			],
		];
		$pDefaultValueReader = $this->_pContainer->get(DefaultValueRead::class);
		$pDefaultValueReader->expects($this->once())
			->method('readDefaultMultiValuesSingleSelect')->will($this->returnValue($row));
		$result = $this->_pSubject->getConvertedMultiFields(13, [$this->_pField]);
		$this->assertEquals(['testField'=> true], $result);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedMultiFieldsForNonEmptyTextField()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_TEXT);
		$row = [
			(object)[
				'defaults_id' => '13',
				'value' => 'Spider Man',
				'fieldname' => 'testField',
				'type' => 'text',
				'locale' => 'native',
			],
		];

		$pDefaultValueReader = $this->_pContainer->get(DefaultValueRead::class);
		$pDefaultValueReader->expects($this->once())
			->method('readDefaultMultiValuesSingleSelect')->will($this->returnValue($row));
		$result = $this->_pSubject->getConvertedMultiFields(13, [$this->_pField]);
		$this->assertEquals(['testField' => 'Spider Man'], $result);
	}


	/**
	 *
	 * @throws DependencyException
	 * @throws NotFoundException
	 *
	 */

	public function testGetConvertedMultiFieldsForRegZusatz()
	{
		$this->_pField->setType('displayAll');
		$row = [
			(object)[
				'defaults_id' => '13',
				'value' => 'Aachen',
				'fieldname' => 'testField',
				'type' => 'displayAll',
				'locale' => '',
			],
		];
		$pDefaultValueReader = $this->_pContainer->get(DefaultValueRead::class);
		$pDefaultValueReader->expects($this->once())
			->method('readDefaultMultiValuesSingleSelect')->will($this->returnValue($row));
		$result = $this->_pSubject->getConvertedMultiFields(13, [$this->_pField]);
		$this->assertEquals(['testField' => 'Aachen'], $result);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedMultiFieldsForAdminForNonEmptyNumericRangeField()
	{
		$row = [
			(object)[
				'defaults_id' => '13',
				'value' => '11.0',
				'fieldname' => 'testField',
			],
			(object)[
				'defaults_id' => '13',
				'value' => '14.0',
				'fieldname' => 'testField',
			],
			(object)[
				'defaults_id' => '16',
				'value' => 'abc',
				'fieldname' => 'fieldname4',
				'type' => 'singleselect',
				'locale' => 'en_US',
			],
			(object)[
				'defaults_id' => '17',
				'value' => 'abc',
				'fieldname' => 'fieldname5',
				'type' => 'singleselect',
				'locale' => 'en_US',
			],
		];
		$pDefaultValueReader = $this->_pContainer->get(DefaultValueRead::class);
		$this->_pField->setIsRangeField(true);
		$pDefaultValueReader->expects($this->once())
			->method('readDefaultMultiValuesSingleSelect')->will($this->returnValue($row));
		$result = $this->_pSubject->GetConvertedMultiFieldsForAdmin(13, [$this->_pField]);
		$this->assertEquals(['testField'=>['min'=>'11.0', 'max'=>'14.0']], $result);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedMultiFieldsForAdminForNonEmptySingleSelectField()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_SINGLESELECT);
		$row = [
			(object)[
				'defaults_id' => '13',
				'value' => 'Monday',
				'fieldname' => 'testField',
				'type' => 'date',
				'locale' => 'en_US',
			],
		];
		$pDefaultValueReader = $this->_pContainer->get(DefaultValueRead::class);
		$pDefaultValueReader->expects($this->once())
			->method('readDefaultMultiValuesSingleSelect')->will($this->returnValue($row));
		$result = $this->_pSubject->GetConvertedMultiFieldsForAdmin(13, [$this->_pField]);
		$this->assertEquals(['testField'=> ['Monday']], $result);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedMultiFieldsForAdminForNonEmptyMultiSelectField()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
		$row = [
			(object)[
				'defaults_id' => '13',
				'value' => 'Monday',
				'fieldname' => 'testField',
				'type' => 'date',
				'locale' => '',
			],
			(object)[
				'defaults_id' => '13',
				'value' => 'Tuesday',
				'fieldname' => 'testField',
				'type' => 'date',
				'locale' => '',
			],
			(object)[
				'defaults_id' => '13',
				'value' => 'Wednesday',
				'fieldname' => 'testField',
				'type' => 'date',
				'locale' => '',
			],
		];
		$pDefaultValueReader = $this->_pContainer->get(DefaultValueRead::class);
		$pDefaultValueReader->expects($this->once())
			->method('readDefaultMultiValuesSingleSelect')->will($this->returnValue($row));
		$result = $this->_pSubject->GetConvertedMultiFieldsForAdmin(13, [$this->_pField]);
		$this->assertEquals(['testField' => ['Monday', 'Tuesday', 'Wednesday']], $result);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedMultiFieldsForAdminForNonEmptyBoolField()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_BOOLEAN);
		$row = [
			(object)[
				'defaults_id' => '13',
				'value' => true,
				'fieldname' => 'testField',
				'type' => 'boolean',
				'locale' => '',
			],
		];
		$pDefaultValueReader = $this->_pContainer->get(DefaultValueRead::class);
		$pDefaultValueReader->expects($this->once())
			->method('readDefaultMultiValuesSingleSelect')->will($this->returnValue($row));
		$result = $this->_pSubject->GetConvertedMultiFieldsForAdmin(13, [$this->_pField]);
		$this->assertEquals(['testField' => [true]], $result);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedMultiFieldsForAdminForNonEmptyTextField()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_TEXT);
		$row = [
			(object)[
				'defaults_id' => '13',
				'value' => 'Spider Man',
				'fieldname' => 'testField',
				'type' => 'text',
				'locale' => 'native',
			],
		];
		$pDefaultValueReader = $this->_pContainer->get(DefaultValueRead::class);
		$pDefaultValueReader->expects($this->once())
			->method('readDefaultMultiValuesSingleSelect')->will($this->returnValue($row));
		$result = $this->_pSubject->GetConvertedMultiFieldsForAdmin(13, [$this->_pField]);
		$this->assertEquals(['testField' => ['native' => 'Spider Man']], $result);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedMultiFieldsForAdminForRegZusatz()
	{
		$this->_pField->setType('displayAll');
		$row = [
			(object)[
				'defaults_id' => '13',
				'value' => 'Aachen',
				'fieldname' => 'testField',
				'type' => 'displayAll',
				'locale' => '',
			],
		];
		$pDefaultValueReader = $this->_pContainer->get(DefaultValueRead::class);
		$pDefaultValueReader->expects($this->once())
			->method('readDefaultMultiValuesSingleSelect')->will($this->returnValue($row));
		$result = $this->_pSubject->GetConvertedMultiFieldsForAdmin(13, [$this->_pField]);
		$this->assertEquals(['testField' => ['Aachen']], $result);
	}
}
