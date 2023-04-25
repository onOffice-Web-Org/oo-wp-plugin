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

declare (strict_types=1);

namespace onOffice\tests;

use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelDate;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelMultiselect;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelNumericRange;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelSingleselect;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelText;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueEstateRead;
use onOffice\WPlugin\Field\DefaultValue\ModelToOutputConverter\DefaultValueEstateModelToOutputConverter;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldTypes;
use WP_UnitTestCase;

/**
 *
 */

class TestClassDefaultValueEstateModelToOutputConverter extends WP_UnitTestCase
{
	/** @var DefaultValueEstateModelToOutputConverter */
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
		$pDefaultValueEstateReader = $this->getMockBuilder(DefaultValueEstateRead::class)
			->disableOriginalConstructor()
			->getMock();

		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainerBuilder->addDefinitions([DefaultValueEstateRead::class => $pDefaultValueEstateReader]);
		$this->_pContainer = $pContainerBuilder->build();

		$this->_pField = new Field('testField', 'testModule');
		$this->_pSubject = $this->_pContainer->get(DefaultValueEstateModelToOutputConverter::class);
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
		$this->assertEquals([''], $result);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedFieldForNonEmptyBoolField()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_BOOLEAN);
		$pBoolFieldModel = new DefaultValueModelSingleselect(13, $this->_pField);
		$pBoolFieldModel->setValue('2');

		$pDefaultValueEstateReader = $this->_pContainer->get(DefaultValueEstateRead::class);
		$pDefaultValueEstateReader->expects($this->once())
			->method('readDefaultValuesSingleSelect')->will($this->returnValue($pBoolFieldModel));
		$result = $this->_pSubject->getConvertedField(13, $this->_pField);
		$this->assertEquals(['2'], $result);
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

		$pDefaultValueEstateReader = $this->_pContainer->get(DefaultValueEstateRead::class);
		$pDefaultValueEstateReader->expects($this->once())
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

	public function testGetConvertedFieldForEmptyBooleanField()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_BOOLEAN	);
		$result = $this->_pSubject->getConvertedField(13, $this->_pField);
		$this->assertEquals([''], $result);
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

		$pDefaultValueEstateReader = $this->_pContainer->get(DefaultValueEstateRead::class);
		$pDefaultValueEstateReader->expects($this->once())
			->method('readDefaultValuesMultiSelect')->will($this->returnValue($pMultiSelectFieldModel));
		$result = $this->_pSubject->getConvertedField(13, $this->_pField);
		$this->assertEquals(['Monday', 'Tuesday', 'Wednesday', 'Saturday'], $result);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedFieldForRegZusatz()
	{
		$this->_pField->setType('displayAll');
		$pMultiSelectFieldModel = new DefaultValueModelMultiselect(13, $this->_pField);
		$pMultiSelectFieldModel->setValues(['Aachen', 'Würselen', 'Herzogenrath']);

		$pDefaultValueEstateReader = $this->_pContainer->get(DefaultValueEstateRead::class);
		$pDefaultValueEstateReader->expects($this->once())
			->method('readDefaultValuesMultiSelect')->will($this->returnValue($pMultiSelectFieldModel));
		$result = $this->_pSubject->getConvertedField(13, $this->_pField);
		$this->assertEquals(['Aachen', 'Würselen', 'Herzogenrath'], $result);
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

		$pDefaultValueEstateReader = $this->_pContainer->get(DefaultValueEstateRead::class);
		$pDefaultValueEstateReader->expects($this->once())
			->method('readDefaultValuesSingleSelect')->will($this->returnValue($pSingleSelectFieldModel));
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

		$pDefaultValueEstateReader = $this->_pContainer->get(DefaultValueEstateRead::class);
		$pDefaultValueEstateReader->expects($this->once())
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

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedFieldForNonEmptyDate()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_DATE);
		$pRangeFieldModel = new DefaultValueModelDate(13, $this->_pField);
		$pRangeFieldModel->setValueFrom('2023/04/19');
		$pRangeFieldModel->setValueTo('2023/04/20');

		$pDefaultValueEstateReader = $this->_pContainer->get(DefaultValueEstateRead::class);
		$pDefaultValueEstateReader->expects($this->once())
			->method('readDefaultValuesDate')->will($this->returnValue($pRangeFieldModel));
		$result = $this->_pSubject->getConvertedField(13, $this->_pField);
		$this->assertEquals(['min' => '2023/04/19', 'max' => '2023/04/20'], $result);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedFieldForEmptyDate()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_DATE);
		$result = $this->_pSubject->getConvertedField(13, $this->_pField);
		$this->assertEquals(['min' => '', 'max' => ''], $result);
	}
}
