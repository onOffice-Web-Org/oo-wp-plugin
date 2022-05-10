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

use onOffice\WPlugin\Renderer\InputFieldRadioRenderer;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class TestClassInputFieldRadioRenderer
	extends \WP_UnitTestCase
{
	public function testConstruct()
	{
		$pInputFieldRadioRenderer = new InputFieldRadioRenderer('testInputName','testInputValue');
		$this->assertEquals('testInputName', $pInputFieldRadioRenderer->getName());
		$this->assertEquals('testInputValue', $pInputFieldRadioRenderer->getValue());
	}
	/**
	 *
	 */
	public function testRenderEmptyValues()
	{
		$pSubject = new InputFieldRadioRenderer('testRenderer', '');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<input type="radio" name="testRenderer" value="">', $output);
	}

	/**
	 *
	 */
	public function testRenderWithValues()
	{
		$pSubject = new InputFieldRadioRenderer('testRenderer', []);
		$pSubject->setValue(['johndoe' => 'John Doe', 'konradzuse' => 'Konrad Zuse']);
		$pSubject->setCheckedValue(['johndoe']);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals(['johndoe'], $pSubject->getCheckedValue());
		$this->assertEquals( '<input type="radio" name="testRenderer" value="johndoe" id="labelradio_1bjohndoetestRenderer">'
		                     . '<label for="labelradio_1bjohndoetestRenderer">John Doe</label>'
		                     . ' <input type="radio" name="testRenderer" value="konradzuse" id="labelradio_1bkonradzusetestRenderer">'
		                     . '<label for="labelradio_1bkonradzusetestRenderer">Konrad Zuse</label> ', $output );
	}
}