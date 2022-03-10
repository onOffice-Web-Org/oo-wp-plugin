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

use onOffice\WPlugin\Renderer\InputFieldChosenRenderer;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class TestClassInputFieldChosenRenderer
	extends \WP_UnitTestCase
{
	/**
	 *
	 */
	public function testRenderEmptyValues()
	{
		$pSubject = new InputFieldChosenRenderer('testRenderer');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<select name="testRenderer" id="select_1" multiple ></select>', $output);
	}

	/**
	 *
	 */
	public function testRenderWithValues()
	{
		$pSubject = new InputFieldChosenRenderer('testRenderer');
		$pSubject->setValue(['johndoe' => 'John Doe', 'konradzuse' => 'Konrad Zuse']);
		$pSubject->setSelectedValue(['johndoe']);
		$pSubject->setOoModule('johndoe');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('johndoe', $pSubject->getOoModule());
		$this->assertEquals('<select name="testRenderer" id="select_1" multiple >'
			. '<option value="johndoe" selected="selected">John Doe</option><option value="konradzuse" >'
			. 'Konrad Zuse</option></select>', $output);
	}
}