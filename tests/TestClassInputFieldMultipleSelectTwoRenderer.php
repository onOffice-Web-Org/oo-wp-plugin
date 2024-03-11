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

use onOffice\WPlugin\Renderer\InputFieldMultipleSelectTwoRenderer;

class TestClassInputFieldMultipleSelectTwoRenderer
	extends \WP_UnitTestCase
{
	/**
	 *
	 */
	public function testRenderWithoutValues()
	{
		$pSubject = new InputFieldMultipleSelectTwoRenderer('multiSelect2');
		ob_start();
		$pSubject->setMultiple(true);
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<select class="custom-multi-select2" name="multiSelect2[]" id="select_1" multiple></select>', $output);
	}

	/**
	 *
	 */
	public function testRenderWithoutSelectedValue()
	{
		$pSubject = new InputFieldMultipleSelectTwoRenderer('multiSelect2');
		$values = [
			'Owner' => 'Eigentümer',
			'Investor' => 'Investor'
		];
		$pSubject->setValue($values);
		ob_start();
		$pSubject->setMultiple(true);
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<select class="custom-multi-select2" name="multiSelect2[]" id="select_2" multiple><option value="Owner" >Eigentümer</option><option value="Investor" >Investor</option></select>',
			$output);
	}

	/**
	 *
	 */
	public function testRenderWithSelectedValues()
	{
		$pSubject = new InputFieldMultipleSelectTwoRenderer('multiSelect2');
		$values = [
			'Owner' => 'Eigentümer',
			'Investor' => 'Investor'
		];
		$pSubject->setValue($values);
		$pSubject->setSelectedValue(['Owner']);
		ob_start();
		$pSubject->setMultiple(true);
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<select class="custom-multi-select2" name="multiSelect2[]" id="select_3" multiple><option value="Owner" selected="selected">Eigentümer</option><option value="Investor" >Investor</option></select>',
			$output);
	}
}