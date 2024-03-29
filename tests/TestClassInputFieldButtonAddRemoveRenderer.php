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

use onOffice\WPlugin\Renderer\InputFieldButtonAddRemoveRenderer;
use onOffice\WPlugin\Installer\DatabaseChanges;
use onOffice\WPlugin\WP\WPOptionWrapperTest;
use DI\Container;
use DI\ContainerBuilder;
use WP_UnitTestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class TestClassInputFieldButtonAddRemoveRenderer
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
		$pSubject = new InputFieldButtonAddRemoveRenderer('testRenderer',true);
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
		$pSubject = new InputFieldButtonAddRemoveRenderer('testRenderer', true);
		$pSubject->setValue(['johndoe' => 'John Doe', 'konradzuse' => 'Konrad Zuse']);
		$pSubject->setCheckedValues(['johndoe']);
		$pSubject->setOoModule('address');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<span class="inputFieldButton labelButtonHandleField-johndoe"name="testRenderer"data-onoffice-module="address"value="johndoe"data-onoffice-category=""data-onoffice-label="John Doe"id="labelbuttonHandleField_1bjohndoe"><span class="dashicons dashicons-remove" typeField="2"></span><label class="field-item-detail" style="opacity: 0.5;">John Doe</label></span><br><span class="inputFieldButton labelButtonHandleField-konradzuse"name="testRenderer"data-onoffice-module="address"value="konradzuse"data-onoffice-category=""data-onoffice-label="Konrad Zuse"id="labelbuttonHandleField_1bkonradzuse"><span class="dashicons dashicons-insert" typeField="1"></span><label class="field-item-detail" style="opacity: 1;">Konrad Zuse</label></span><br>', $output);
	}
	
	/**
	 *
	 */
	public function testGetHint()
	{
		$pSubject = new InputFieldButtonAddRemoveRenderer('testRenderer', true);
		$pSubject->setHint('testRenderer');
		$this->assertEquals('testRenderer',$pSubject->getHint());
	}
	
	/**
	 *
	 */
	public function testRenderWithArrayValue()
	{
		ob_start();
		$pCheckboxFieldRenderer = new InputFieldButtonAddRemoveRenderer('testRenderer',[1,2]);
		$pCheckboxFieldRenderer->setOoModule('address');
		$pCheckboxFieldRenderer->render();
		$output = ob_get_clean();
		$this->assertEquals('<span class="inputFieldButton labelButtonHandleField-0"name="testRenderer"data-onoffice-module="address"value="0"data-onoffice-category=""data-onoffice-label="1"id="labelbuttonHandleField_1b0"><span class="dashicons dashicons-insert" typeField="1"></span><label class="field-item-detail" style="opacity: 1;">1</label></span><br><span class="inputFieldButton labelButtonHandleField-1"name="testRenderer"data-onoffice-module="address"value="1"data-onoffice-category=""data-onoffice-label="2"id="labelbuttonHandleField_1b1"><span class="dashicons dashicons-insert" typeField="1"></span><label class="field-item-detail" style="opacity: 1;">2</label></span><br>', $output);
	}
	
	/**
	 *
	 */	
	public function testSetCheckedValues()
	{
		$instance = new InputFieldButtonAddRemoveRenderer('testRenderer', 1);
		$instance->setCheckedValues([1,2]);
		$this->assertEquals([1,2], $instance->getCheckedValues());
	}
}
