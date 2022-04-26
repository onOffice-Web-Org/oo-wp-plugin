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

use onOffice\WPlugin\Renderer\InputFieldCheckboxRenderer;
use onOffice\WPlugin\Installer\DatabaseChanges;
use onOffice\WPlugin\WP\WPOptionWrapperTest;
/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class TestClassInputFieldCheckboxRenderer
	extends \WP_UnitTestCase
{
	/**
	 * @before
	 */
	public function prepare()
	{
		global $wpdb;
		
		$pWpOption = new WPOptionWrapperTest();
		$pDbChanges = new DatabaseChanges($pWpOption, $wpdb);
		$pDbChanges->install();
	}
	
	/**
	 * @throws \Exception
	 */
	public function testRenderEmptyValues()
	{
		$pSubject = new InputFieldCheckboxRenderer('testRenderer',true);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<input type="checkbox" name="testRenderer" value="1" id="checkbox_1">', $output);
	}
	
	/**
	 * @throws \Exception
	 */
	public function testRenderWithValues()
	{
		$pSubject = new InputFieldCheckboxRenderer('testRenderer', true);
		$pSubject->setValue(['johndoe' => 'John Doe', 'konradzuse' => 'Konrad Zuse']);
		$pSubject->setCheckedValues(['johndoe']);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<input type="checkbox" name="testRenderer" value="johndoe" checked="checked"  onoffice-multipleSelectType="0" id="labelcheckbox_1bjohndoe">'
			. '<label for="labelcheckbox_1bjohndoe">John Doe</label>'
			. '<br><input type="checkbox" name="testRenderer" value="konradzuse" onoffice-multipleSelectType="0" id="labelcheckbox_1bkonradzuse"><label for="labelcheckbox_1bkonradzuse">Konrad Zuse</label><br>', $output);
	}
	
	/**
	 *
	 */
	public function testGetHintHTML()
	{
		$pSubject = new InputFieldCheckboxRenderer('testRenderer', true);
		$pSubject->setHintHtml('testRenderer');
		$this->assertEquals('testRenderer',$pSubject->getHintHtml());
	}
}
