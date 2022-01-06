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

use onOffice\WPlugin\Renderer\InputFieldComplexSortableListRenderer;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class TestClassInputFieldComplexSortableListRenderer
	extends \WP_UnitTestCase
{
	/**
	 *
	 */
	public function testRenderEmptyValues()
	{
		$pSubject = new InputFieldComplexSortableListRenderer('testRenderer', true);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<ul class="filter-fields-list"></ul>', $output);
	}

	/**
	 *
	 */
	public function testRenderWithValues()
	{
		$pSubject = new InputFieldComplexSortableListRenderer('testRenderer', true);
		$pSubject->setValue(['johndoe' => 'John Doe', 'konradzuse' => 'Konrad Zuse']);
		$pSubject->setCheckedValues(['johndoe']);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<ul class="filter-fields-list"><li class="sortable-item" >'
			. '<input type="checkbox" name="testRenderer[]" value="johndoe" checked="checked"  id="labelcheckbox_1bjohndoe">'
			. 'John Doe<input type="hidden" name="filter_fields_order1[id]" value="1">'
			. '<input type="hidden" name="filter_fields_order1[name]" value="John Doe">'
			. '<input type="hidden" name="filter_fields_order1[slug]" value="johndoe"></li></ul>', $output);
	}
}