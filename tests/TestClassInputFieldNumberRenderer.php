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

use onOffice\WPlugin\Renderer\InputFieldNumberRenderer;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */

class TestClassInputFieldNumberRenderer
	extends \WP_UnitTestCase
{
	/**
	 *
	 */
	public function testRenderEmptyValues()
	{
		$pSubject = new InputFieldNumberRenderer('testRenderer');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<input type="number" name="testRenderer" value="" id="number_1" >', $output);
	}

	/**
	 *
	 */
	public function testRenderWithValueInteger()
	{
		$pSubject = new InputFieldNumberRenderer('testRenderer');
		$pSubject->setValue(123);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<input type="number" name="testRenderer" value="123" id="number_1" >', $output);
	}

	/**
	 *
	 */
	public function testRenderWithValueDecimal()
	{
		$pSubject = new InputFieldNumberRenderer('testRenderer');
		$pSubject->setValue(123.4);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<input type="number" name="testRenderer" value="123.4" id="number_1" >', $output);
	}
}