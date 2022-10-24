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

use onOffice\WPlugin\Renderer\InputFieldCheckButtonRenderer;
use onOffice\WPlugin\Installer\DatabaseChanges;
use onOffice\WPlugin\WP\WPOptionWrapperTest;
use DI\Container;
use DI\ContainerBuilder;
use WP_UnitTestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class TestClassInputFieldCheckButtonRenderer
	extends WP_UnitTestCase
{
	/** @var Container */
	private $_pContainer;

	/**
	 * @before
	 */
	public function prepare()
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();
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
		$pSubject = new InputFieldCheckButtonRenderer('testRenderer',true);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('', $output);
	}
	
	/**
	 * @throws \Exception
	 */
	public function testRenderWithValues()
	{
		$pSubject = new InputFieldCheckButtonRenderer('testRenderer', true);
		$pSubject->setValue(['johndoe' => 'John Doe', 'konradzuse' => 'Konrad Zuse']);
		$pSubject->setCheckedValues(['johndoe']);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<span name="testRenderer"class="inputFieldAddFieldButton dashicons dashicons-remove" onoffice-multipleSelectType="0" check="2" value="johndoe"data-onoffice-category="" id="labelbuttonHandleField_1bjohndoe"> </span><label for="labelbuttonHandleField_1bjohndoe">John Doe</label><br><span name="testRenderer"class="inputFieldAddFieldButton dashicons dashicons-insert" onoffice-multipleSelectType="0" value="konradzuse" check="1"data-onoffice-category="" id="labelbuttonHandleField_1bkonradzuse"> </span><label for="labelbuttonHandleField_1bkonradzuse">Konrad Zuse</label><br>', $output);
	}
	
	/**
	 *
	 */
	public function testGetHint()
	{
		$pSubject = new InputFieldCheckButtonRenderer('testRenderer', true);
		$pSubject->setHint('testRenderer');
		$this->assertEquals('testRenderer',$pSubject->getHint());
	}
	
	public function testRenderWithArrayValue()
	{
		ob_start();
		$pCheckboxFieldRenderer = new InputFieldCheckButtonRenderer('testRenderer',[1,2]);
		$pCheckboxFieldRenderer->render();
		$output = ob_get_clean();
		$this->assertEquals('<span name="testRenderer"class="inputFieldAddFieldButton dashicons dashicons-insert" onoffice-multipleSelectType="0" value="0" check="1"data-onoffice-category="" id="labelbuttonHandleField_1b0"> </span><label for="labelbuttonHandleField_1b0">1</label><br><span name="testRenderer"class="inputFieldAddFieldButton dashicons dashicons-insert" onoffice-multipleSelectType="0" value="1" check="1"data-onoffice-category="" id="labelbuttonHandleField_1b1"> </span><label for="labelbuttonHandleField_1b1">2</label><br>', $output);
	}
	
	public function testSetCheckedValues()
	{
		$instance = new InputFieldCheckButtonRenderer('testRenderer', 1);
		$instance->setCheckedValues([1,2]);
		$this->assertEquals([1,2], $instance->getCheckedValues());
	}
}
