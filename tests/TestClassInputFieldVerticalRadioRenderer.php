<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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

use onOffice\WPlugin\Renderer\InputFieldVerticalRadioRenderer;
use WP_UnitTestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class TestClassInputFieldVerticalRadioRenderer extends WP_UnitTestCase
{
	/**
	 *
	 */
	public function testConstruct()
	{
		$pInputFieldVerticalRadioRenderer = new InputFieldVerticalRadioRenderer('testInputName', ['testInputValue']);
		$this->assertEquals('testInputName', $pInputFieldVerticalRadioRenderer->getName());
		$this->assertEquals(['testInputValue'], $pInputFieldVerticalRadioRenderer->getValue());
	}

	/**
	 *
	 */
	public function testRenderEmptyValues()
	{
		$pSubject = new InputFieldVerticalRadioRenderer('testRenderer', []);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<div class="oo-vertical-radio"></div>', $output);
	}

	/**
	 *
	 */
	public function testRenderWithValues()
	{
		$pSubject = new InputFieldVerticalRadioRenderer('testRenderer', []);
		$pSubject->setValue(['0' => 'Show all contact persons', '1' => 'Show main contact person only']);
		$pSubject->setCheckedValue('1');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('1', $pSubject->getCheckedValue());
		$this->assertEquals('<div class="oo-vertical-radio"><input type="radio" name="testRenderer" value="0" id="labelradio_1b0testRenderer"><label for="labelradio_1b0testRenderer">Show all contact persons</label><br><input type="radio" name="testRenderer" value="1" checked="checked"  id="labelradio_1b1testRenderer"><label for="labelradio_1b1testRenderer">Show main contact person only</label><br></div>', $output);
	}

	/**
	 *
	 */
	public function testRenderWithInformationText()
	{
		$pSubject = new InputFieldVerticalRadioRenderer('testRenderer', []);
		$pSubject->setValue(['0' => 'Show all contact persons', '1' => 'Show main contact person only']);
		$pSubject->setCheckedValue('1');
		$pSubject->setHint('Test information text');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('1', $pSubject->getCheckedValue());
		$this->assertEquals('<div class="oo-vertical-radio"><input type="radio" name="testRenderer" value="0" id="labelradio_1b0testRenderer"><label for="labelradio_1b0testRenderer">Show all contact persons</label><br><input type="radio" name="testRenderer" value="1" checked="checked"  id="labelradio_1b1testRenderer"><label for="labelradio_1b1testRenderer">Show main contact person only</label><br><p class="oo-information-text">Test information text</p></div>', $output);
	}
}