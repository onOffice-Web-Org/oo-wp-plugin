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

use onOffice\WPlugin\Renderer\InputFieldSelectRenderer;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */

class TestClassInputFieldSelectRenderer
	extends \WP_UnitTestCase
{
	/**
	 *
	 */
	public function testRenderEmptyValues()
	{
		$pSubject = new InputFieldSelectRenderer('testRenderer');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<select name="testRenderer"  id="select_1"></select>', $output);
	}

	/**
	 *
	 */
	public function testRenderWithValues()
	{
		$pSubject = new InputFieldSelectRenderer('testRenderer');
		$pSubject->setValue(['johndoe' => 'John Doe', 'konradzuse' => 'Konrad Zuse']);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<select name="testRenderer"  id="select_1">'
			.'<option value="johndoe" >John Doe</option><option value="konradzuse" >'
			.'Konrad Zuse</option></select>', $output);
	}

	/**
	 *
	 */
	public function testRenderWithValuesAndLabelOnlyValues()
	{
		$pSubject = new InputFieldSelectRenderer('testRenderer');
		$pSubject->setValue(['johndoe' => 'John Doe', 'konradzuse' => 'Konrad Zuse']);
		$pSubject->setLabelOnlyValues(['johndoe']);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<select name="testRenderer"  id="select_1">'
			.'<optgroup label="John Doe" ></optgroup><option value="konradzuse" >'
			.'Konrad Zuse</option></select>', $output);
	}

	/**
	 *
	 */
	public function testRenderWithValuesAndLabelOnlyValuesAndSelectedValue()
	{
		$pSubject = new InputFieldSelectRenderer('testRenderer');
		$pSubject->setValue(['johndoe' => 'John Doe', 'konradzuse' => 'Konrad Zuse']);
		$pSubject->setSelectedValue('konradzuse');
		$pSubject->setLabelOnlyValues(['johndoe']);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<select name="testRenderer"  id="select_1">'
			.'<optgroup label="John Doe" ></optgroup><option value="konradzuse"  selected="selected" >'
			.'Konrad Zuse</option></select>', $output);
	}

	/**
	 *
	 */
	public function testRenderWithValuesAndHint()
	{
		$pSubject = new InputFieldSelectRenderer('testRenderer');
		$pSubject->setValue(['johndoe' => 'John Doe', 'konradzuse' => 'Konrad Zuse']);
		$pSubject->setHint('test');
		$pSubject->getName('test');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<select name="testRenderer"  id="select_1">'
			.'<option value="johndoe" >John Doe</option><option value="konradzuse" >Konrad Zuse</option>'
			.'</select><div>test</div>', $output);
	}
	/**
	 *
	 */
	public function testRenderWithValuesAndNameShowReferenceEstate()
	{
		$pSubject = new InputFieldSelectRenderer('oopluginlistviews-showreferenceestate');
		$pSubject->setValue(['johndoe' => 'John Doe', 'konradzuse' => 'Konrad Zuse']);
		$pSubject->setHint('test');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<script>if (jQuery("select[name=oopluginlistviews-showreferenceestate").val() === "0") {jQuery(".memssageReference").hide();}</script>'
			.'<select name="oopluginlistviews-showreferenceestate"  id="select_1">'
			.'<option value="johndoe" >John Doe</option><option value="konradzuse" >Konrad Zuse</option>'
			.'</select><div class="memssageReference">test</div>', $output);
	}
}