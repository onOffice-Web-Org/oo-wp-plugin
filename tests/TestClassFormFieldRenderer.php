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

use onOffice\WPlugin\Form;
use onOffice\WPlugin\Form\FormFieldRenderer;
use WP_UnitTestCase;

/**
 *
 * test class for FormFieldRenderer
 *
 */

class TestClassFormFieldRenderer
	extends WP_UnitTestCase
{

	/**
	 *
	 * @covers onOffice\WPlugin\Form\FormFieldRenderer::render
	 *
	 */

	public function testRender()
	{
		$pFormMock = $this->getMockBuilder(Form::class)
				->disableOriginalConstructor()
				->setMethods(['isSearchcriteriaField', 'getFieldValue', 'isRequiredField', 'getPermittedValues', 'getFieldType'])
				->getMock();

		$pFormMock->method('isSearchcriteriaField')->will($this->returnValue(false));
		$pFormMock->method('getFieldValue')->will($this->returnValue('Mustermann'));
		$pFormMock->method('isRequiredField')->will($this->returnValue(false));
		$pFormMock->method('getPermittedValues')->will($this->returnValue([]));
		$pFormMock->method('getFieldType')->will($this->returnValue('varchar'));

		$pRenderer = new FormFieldRenderer($pFormMock, 'Name', false);
		$result = $pRenderer->render();
		$expectedResult = '<input type="text"  name="Name" value="Mustermann">';

		$this->assertEquals($expectedResult, $result);
	}
}