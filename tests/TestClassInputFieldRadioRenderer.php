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
	use HtmlNormalizerTrait;

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
		$this->assertHtmlEquals('<input type="radio" name="testRenderer" value="">', $output);
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
		$this->assertHtmlEquals( '<input type="radio" name="testRenderer" value="johndoe" checked="checked" id="labelradio_1bjohndoetestRenderer">'
		                     . '<label for="labelradio_1bjohndoetestRenderer">John Doe</label>'
		                     . ' <input type="radio" name="testRenderer" value="konradzuse" id="labelradio_1bkonradzusetestRenderer">'
		                     . '<label for="labelradio_1bkonradzusetestRenderer">Konrad Zuse</label> ', $output );
	}

	/**
	 *
	 */
	public function testRenderWithDescription()
	{
		$pSubject = new InputFieldRadioRenderer('testRenderer', [], ['johndoe' => 'John Doe', 'konradzuse' => 'Konrad Zuse']);
		$pSubject->setValue(['johndoe' => 'John Doe', 'konradzuse' => 'Konrad Zuse']);
		$pSubject->setCheckedValue(['johndoe']);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals(['johndoe'], $pSubject->getCheckedValue());
		$this->assertHtmlEquals( '<input type="radio" name="testRenderer" value="johndoe" checked="checked" id="labelradio_1bjohndoetestRenderer">'
		                     . '<label for="labelradio_1bjohndoetestRenderer">John Doe</label> '
		                     . '<p class="description">John Doe</p><br>'
		                     . '<input type="radio" name="testRenderer" value="konradzuse" id="labelradio_1bkonradzusetestRenderer">'
		                     . '<label for="labelradio_1bkonradzusetestRenderer">Konrad Zuse</label> '
		                     . '<p class="description">Konrad Zuse</p><br>', $output );
	}

	/**
	 * PHP casts numeric array keys to int, the checked value is a string.
	 */
	public function testRenderWithNumericKeys()
	{
		$pSubject = new InputFieldRadioRenderer('testRenderer', []);
		$pSubject->setValue([0 => 'First choice', 1 => 'Second choice']);
		$pSubject->setCheckedValue('1');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertHtmlEquals( '<input type="radio" name="testRenderer" value="0" id="labelradio_1b0testRenderer">'
		                     . '<label for="labelradio_1b0testRenderer">First choice</label> '
		                     . '<input type="radio" name="testRenderer" value="1" checked="checked" id="labelradio_1b1testRenderer">'
		                     . '<label for="labelradio_1b1testRenderer">Second choice</label> ', $output );
	}

	/**
	 * Numeric keys combined with the array form of the checked value.
	 */
	public function testRenderWithNumericKeysAndArrayCheckedValue()
	{
		$pSubject = new InputFieldRadioRenderer('testRenderer', []);
		$pSubject->setValue([0 => 'First choice', 1 => 'Second choice']);
		$pSubject->setCheckedValue([0]);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertHtmlEquals( '<input type="radio" name="testRenderer" value="0" checked="checked" id="labelradio_1b0testRenderer">'
		                     . '<label for="labelradio_1b0testRenderer">First choice</label> '
		                     . '<input type="radio" name="testRenderer" value="1" id="labelradio_1b1testRenderer">'
		                     . '<label for="labelradio_1b1testRenderer">Second choice</label> ', $output );
	}
}
