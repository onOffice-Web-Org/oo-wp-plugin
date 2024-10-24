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

use onOffice\WPlugin\Installer\DatabaseChanges;
use onOffice\WPlugin\WP\WPOptionWrapperTest;
use DI\Container;
use DI\ContainerBuilder;
use WP_UnitTestCase;
use onOffice\WPlugin\Renderer\InputFieldTextAreaRenderer;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class TestClassInputFieldTextAreaRenderer
	extends WP_UnitTestCase
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
		$pSubject = new InputFieldTextAreaRenderer('textarea', 'testRenderer');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<textarea name="testRenderer" id="textarea_1" ></textarea>', $output);
	}
	
	/**
	 * @throws \Exception
	 */
	public function testRenderWithValues()
	{
		$pSubject = new InputFieldTextAreaRenderer('textarea', 'testRenderer');
		$pSubject->setValue('test1');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<textarea name="testRenderer" id="textarea_1" >test1</textarea>', $output);
	}
}
