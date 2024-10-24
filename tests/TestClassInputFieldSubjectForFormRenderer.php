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

use onOffice\WPlugin\Renderer\InputFieldSubjectForFormRenderer;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */

class TestClassInputFieldSubjectForFormRenderer
	extends \WP_UnitTestCase
{
	/**
	 *
	 */
	public function testRenderEmptyValues()
	{
		$pSubject = new InputFieldSubjectForFormRenderer('testRenderer');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<div class="oo-email-subject-container"><button class="oo-insert-variable-button">Insert variable</button><div class="oo-email-subject-title" contenteditable="true"></div><div class="oo-email-subject-suggestions"></div><input type="hidden" class="oo-email-subject-output" name="testRenderer" value=""></div>', $output);
	}

	/**
	 *
	 */
	public function testRenderWithValues()
	{
		$pSubject = new InputFieldSubjectForFormRenderer('testRenderer');
		$pSubject->setValue('%%Strasse%%');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<div class="oo-email-subject-container"><button class="oo-insert-variable-button">Insert variable</button><div class="oo-email-subject-title" contenteditable="true"></div><div class="oo-email-subject-suggestions"></div><input type="hidden" class="oo-email-subject-output" name="testRenderer" value="%%Strasse%%"></div>', $output);
	}

	/**
	 *
	 */
	public function testRenderWithValuesAndHint()
	{
		$pSubject = new InputFieldSubjectForFormRenderer('testRenderer');
		$pSubject->setValue('%%Strasse%%');
		$pSubject->setHint('test');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<div class="oo-email-subject-container"><button class="oo-insert-variable-button">Insert variable</button><div class="oo-email-subject-title" contenteditable="true"></div><div class="oo-email-subject-suggestions"></div><input type="hidden" class="oo-email-subject-output" name="testRenderer" value="%%Strasse%%"></div><p class="hint-text">test</p>', $output);
	}
}