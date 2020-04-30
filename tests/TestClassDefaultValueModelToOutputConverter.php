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
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelBool;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelMultiselect;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelNumericRange;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelSingleselect;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelText;
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
	public function testGetConvertedFieldForUnknownFieldType()
	{
		$this->_pField->setType('unknown');
		$result = $this->_pSubject->getConvertedField(13, $this->_pField);
		$this->assertEmpty($result);
	}


	/**
	 *
	 * @throws DependencyException
	 * @throws NotFoundException
	 *
	 */

	public function testGetConvertedFieldForEmptyTextField()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_TEXT);
		$result = $this->_pSubject->getConvertedField(13, $this->_pField);
		$this->assertEmpty($result);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedFieldForBoolField()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_BOOLEAN);
		$result = $this->_pSubject->getConvertedField(13, $this->_pField);
		$this->assertEquals(['0'], $result);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedFieldForNonEmptyBoolField()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_BOOLEAN);
		$pBoolFieldModel = new DefaultValueModelBool(13, $this->_pField);
		$pBoolFieldModel->setValue(true);

		$pDefaultValueReader = $this->_pContainer->get(DefaultValueRead::class);
		$pDefaultValueReader->expects($this->once())
			->method('readDefaultValuesBool')->will($this->returnValue($pBoolFieldModel));
		$result = $this->_pSubject->getConvertedField(13, $this->_pField);
		$this->assertEquals(['1'], $result);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedFieldForNonEmptyTextField()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_TEXT);
		$pTextFieldModel = new DefaultValueModelText(13, $this->_pField);
		$pTextFieldModel->addValueByLocale('de_DE', 'Die menschliche Spinne');
		$pTextFieldModel->addValueByLocale('en_US', 'Spider Man');
		$pTextFieldModel->addValueByLocale('fr_BE', 'Homme araignée');

		$pDefaultValueReader = $this->_pContainer->get(DefaultValueRead::class);
		$pDefaultValueReader->expects($this->once())
			->method('readDefaultValuesText')->will($this->returnValue($pTextFieldModel));
		$result = $this->_pSubject->getConvertedField(13, $this->_pField);
		$this->assertEquals([
			'de_DE' => 'Die menschliche Spinne',
			'fr_BE' => 'Homme araignée',
			'native' => 'Spider Man',
		], $result);
	}


	/**
	 *
	 * @throws DependencyException
	 * @throws NotFoundException
	 *
	 */

	public function testGetConvertedFieldForEmptySingleSelectField()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_SINGLESELECT);
		$result = $this->_pSubject->getConvertedField(13, $this->_pField);
		$this->assertEquals([''], $result);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedFieldForEmptyMultiSelectField()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
		$result = $this->_pSubject->getConvertedField(13, $this->_pField);
		$this->assertSame([], $result);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedFieldForNonEmptyMultiSelectField()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
		$pMultiSelectFieldModel = new DefaultValueModelMultiselect(13, $this->_pField);
		$pMultiSelectFieldModel->setValues(['Monday', 'Tuesday', 'Wednesday', 'Saturday']);

		$pDefaultValueReader = $this->_pContainer->get(DefaultValueRead::class);
		$pDefaultValueReader->expects($this->once())
			->method('readDefaultValuesMultiSelect')->will($this->returnValue($pMultiSelectFieldModel));
		$result = $this->_pSubject->getConvertedField(13, $this->_pField);
		$this->assertEquals(['Monday', 'Tuesday', 'Wednesday', 'Saturday'], $result);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedFieldForNonEmptySingleSelectField()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_SINGLESELECT);
		$pSingleSelectFieldModel = new DefaultValueModelSingleselect(13, $this->_pField);
		$pSingleSelectFieldModel->setValue('Monday');

		$pDefaultValueReader = $this->_pContainer->get(DefaultValueRead::class);
		$pDefaultValueReader->expects($this->once())
			->method('readDefaultValuesSingleselect')->will($this->returnValue($pSingleSelectFieldModel));
		$result = $this->_pSubject->getConvertedField(13, $this->_pField);
		$this->assertEquals(['Monday'], $result);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedFieldForNonEmptyNumericRangeField()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_FLOAT);
		$this->_pField->setIsRangeField(true);
		$pRangeFieldModel = new DefaultValueModelNumericRange(13, $this->_pField);
		$pRangeFieldModel->setValueFrom(11.);
		$pRangeFieldModel->setValueTo(14.);

		$pDefaultValueReader = $this->_pContainer->get(DefaultValueRead::class);
		$pDefaultValueReader->expects($this->once())
			->method('readDefaultValuesNumericRange')->will($this->returnValue($pRangeFieldModel));
		$result = $this->_pSubject->getConvertedField(13, $this->_pField);
		$this->assertEquals(['min' => 11.0, 'max' => 14.0], $result);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedFieldForEmptyNumericRangeField()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_FLOAT);
		$this->_pField->setIsRangeField(true);
		$result = $this->_pSubject->getConvertedField(13, $this->_pField);
		$this->assertEquals(['min' => .0, 'max' => .0], $result);
	}
}
