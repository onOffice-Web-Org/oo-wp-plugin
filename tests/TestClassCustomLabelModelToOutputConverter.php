<?php

/**
 *
 *    Copyright (C) 2021 onOffice GmbH
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
use onOffice\WPlugin\Field\CustomLabel\CustomLabelModelField;
use onOffice\WPlugin\Field\CustomLabel\CustomLabelRead;
use onOffice\WPlugin\Field\CustomLabel\ModelToOutputConverter\CustomLabelModelToOutputConverter;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldTypes;
use WP_UnitTestCase;

/**
 *
 */
class TestClassCustomLabelModelToOutputConverter extends WP_UnitTestCase
{
	/** @var CustomLabelModelToOutputConverter */
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
		$pCustomLabelReader = $this->getMockBuilder(CustomLabelRead::class)
			->disableOriginalConstructor()
			->getMock();

		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainerBuilder->addDefinitions([CustomLabelRead::class => $pCustomLabelReader]);
		$this->_pContainer = $pContainerBuilder->build();

		$this->_pField = new Field('testField', 'testModule');
		$this->_pSubject = $this->_pContainer->get(CustomLabelModelToOutputConverter::class);
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

	public function testGetConvertedFieldForEmptyField()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_TEXT);
		$result = $this->_pSubject->getConvertedField(13, $this->_pField);
		$this->assertEmpty($result);
	}


	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testGetConvertedFieldForNonEmptyField()
	{
		$this->_pField->setType(FieldTypes::FIELD_TYPE_TEXT);
		$pTextFieldModel = new CustomLabelModelField(13, $this->_pField);
		$pTextFieldModel->addValueByLocale('de_DE', 'Custom Label DE');
		$pTextFieldModel->addValueByLocale('en_US', 'Custom Label');
		$pTextFieldModel->addValueByLocale('fr_BE', 'Custom Label BE');

		$pCustomLabelReader = $this->_pContainer->get(CustomLabelRead::class);
		$pCustomLabelReader->expects($this->once())
			->method('readCustomLabelsField')->will($this->returnValue($pTextFieldModel));
		$result = $this->_pSubject->getConvertedField(13, $this->_pField);
		$this->assertEquals([
			'de_DE' => 'Custom Label DE',
			'fr_BE' => 'Custom Label BE',
			'native' => 'Custom Label',
		], $result);
	}
}
