<?php

/**
 *
 *    Copyright (C) 2023 onOffice GmbH
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
use onOffice\WPlugin\Renderer\InputSearchFieldForFieldListsRenderer;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class TestClassInputSearchFieldForFieldListsRenderer
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

	public function testRenderEmptyValues()
	{
		$pSubject = new InputSearchFieldForFieldListsRenderer('testRenderer', true);
		$pSubject->render();
		ob_start();
		$output = ob_get_clean();
		$this->assertEquals('', $output);
	}
	
	/**
	 * @throws \Exception
	 */
	public function testRenderWithValues()
	{
		$pSubject = new InputSearchFieldForFieldListsRenderer('testRenderer', true);
		$pSubject->setValue([
			'field1' => ['label' => 'Field 1', 'content' => 'Content 1'],
			'field2' => ['label' => 'Field 2', 'content' => 'Content 2']
		]);
		$pSubject->setCheckedValues(['janedoe']);
		$pSubject->setOoModule('address');
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<div class="box-search"><input type="text" class="input-search"><span class="dashicons dashicons-search clear-icon" id="clear-input"></span></div><label>Search the field list for the desired fields.</label><div class="field-lists"><div class="line-bottom-content"></div><ul class="search-field-list"><li class="search-field-item" data-label="Field 1" data-key="field1" data-content="Content 1"><span class="field-item inputFieldButton labelButtonHandleField-field1"name="testRenderer"data-onoffice-module="address"value="field1"data-onoffice-category="Content 1"data-onoffice-label="Field 1"id="labelsearchFieldForFieldLists_1bfield1"><span class="dashicons dashicons-insert check-action" typeField="1"></span><div class="field-item-detail" style="opacity: 1;"><span class="field-item-detail-category">Content 1</span><span class="field-item-detail-name">Field 1 (field1)</span></span></div></li><li class="search-field-item" data-label="Field 2" data-key="field2" data-content="Content 2"><span class="field-item inputFieldButton labelButtonHandleField-field2"name="testRenderer"data-onoffice-module="address"value="field2"data-onoffice-category="Content 2"data-onoffice-label="Field 2"id="labelsearchFieldForFieldLists_1bfield2"><span class="dashicons dashicons-insert check-action" typeField="1"></span><div class="field-item-detail" style="opacity: 1;"><span class="field-item-detail-category">Content 2</span><span class="field-item-detail-name">Field 2 (field2)</span></span></div></li></ul></div>', $output);
	}
	
	/**
	 *
	 */	
	public function testSetCheckedValues()
	{
		$instance = new InputSearchFieldForFieldListsRenderer('testRenderer', 1);
		$instance->setCheckedValues([1,2]);
		$this->assertEquals([1,2], $instance->getCheckedValues());
	}
}
