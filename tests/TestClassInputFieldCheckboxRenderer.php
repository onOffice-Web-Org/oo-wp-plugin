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

declare (strict_types=1);

namespace onOffice\tests;

use DI\Container;
use DI\ContainerBuilder;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Renderer\InputFieldCheckboxRenderer;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use WP_UnitTestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class TestClassInputFieldCheckboxRenderer
	extends WP_UnitTestCase
{
	/** @var Container */
	private $_pContainer;

	/**
	 * @before
	 */
	public function prepare()
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();
	}

	public function testRenderEmptyValues()
	{
		$pMockCheckboxFieldRenderer = $this->getMockBuilder(InputFieldCheckboxRenderer::class)
			->setConstructorArgs(['testRenderer', ''])
			->setMethods(['buildFieldsCollection', 'isMultipleSelect'])
			->getMock();
		$fieldCollection = new FieldsCollection();
		$pMockCheckboxFieldRenderer
			->method('buildFieldsCollection')
			->willReturn($fieldCollection);
		$pMockCheckboxFieldRenderer
			->method('isMultipleSelect')
			->will($this->returnValue(false));
		ob_start();
		$pMockCheckboxFieldRenderer->render();
		$output = ob_get_clean();
		$this->assertEquals('<input type="checkbox" name="testRenderer" value="" id="checkbox_1">', $output);
	}

	public function testRenderWithValues()
	{
		$pMockCheckboxFieldRenderer = $this->getMockBuilder(InputFieldCheckboxRenderer::class)
			->setConstructorArgs(['testRenderer', 1])
			->setMethods(['buildFieldsCollection', 'isMultipleSelect'])
			->getMock();
		$fieldCollection = new FieldsCollection();
		$pMockCheckboxFieldRenderer
			->method('buildFieldsCollection')
			->willReturn($fieldCollection);
		$pMockCheckboxFieldRenderer
			->method('isMultipleSelect')
			->will($this->returnValue(false));
		ob_start();
		$pMockCheckboxFieldRenderer->render();
		$output = ob_get_clean();
		$this->assertEquals('<input type="checkbox" name="testRenderer" value="1" id="checkbox_1">', $output);
	}

	public function testRenderWithArrayValue()
	{
		$pMockCheckboxFieldRenderer = $this->getMockBuilder(InputFieldCheckboxRenderer::class)
			->setConstructorArgs(['testRenderer', [1,2]])
			->setMethods(['buildFieldsCollection', 'isMultipleSelect'])
			->getMock();
		$fieldCollection = new FieldsCollection();
		$pMockCheckboxFieldRenderer
			->method('buildFieldsCollection')
			->willReturn($fieldCollection);
		$pMockCheckboxFieldRenderer
			->method('isMultipleSelect')
			->will($this->returnValue(false));
		ob_start();
		$pMockCheckboxFieldRenderer->render();
		$output = ob_get_clean();
		$this->assertEquals('<input type="checkbox" name="testRenderer" value="0" onoffice-multipleSelectType="0" id="labelcheckbox_1b0"><label for="labelcheckbox_1b0">1</label><br><input type="checkbox" name="testRenderer" value="1" onoffice-multipleSelectType="0" id="labelcheckbox_1b1"><label for="labelcheckbox_1b1">2</label><br>', $output);
	}

	public function testIsMultipleSelect()
	{
		$pMockCheckboxFieldRenderer = $this->getMockBuilder(InputFieldCheckboxRenderer::class)
			->setConstructorArgs(['testRenderer', 1])
			->setMethods(['getOoModule'])
			->getMock();
		$pMockCheckboxFieldRenderer
			->method('getOoModule')
			->willReturn('address');

		$fieldCollection = new FieldsCollection();

		$result = $pMockCheckboxFieldRenderer->isMultipleSelect('Addr', $fieldCollection);
		$this->assertFalse($result);
	}

	public function testIsMultipleSelectIsTrue()
	{
		$pMockCheckboxFieldRenderer = $this->getMockBuilder(InputFieldCheckboxRenderer::class)
			->setConstructorArgs(['testRenderer', 1])
			->setMethods(['getOoModule'])
			->getMock();
		$pMockCheckboxFieldRenderer
			->method('getOoModule')
			->willReturn('address');

		$field = new Field('Addr', 'address', 'Addr');
		$field->setType('multiselect');
		$fieldCollection = new FieldsCollection();
		$fieldCollection->addField($field);
		$result = $pMockCheckboxFieldRenderer->isMultipleSelect('Addr', $fieldCollection);
		$this->assertTrue($result);
	}

	public function testSetCheckedValues()
	{
		$instance = new InputFieldCheckboxRenderer('testRenderer', 1);
		$instance->setCheckedValues([1,2]);
		$this->assertEquals([1,2], $instance->getCheckedValues());
	}

}
