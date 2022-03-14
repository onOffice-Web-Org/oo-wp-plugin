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

use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationFactory;
use onOffice\WPlugin\Renderer\InputFieldTemplateListRenderer;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class TestClassInputTemplateListRenderer
	extends \WP_UnitTestCase
{
	/**
	 *
	 */
	public function testRenderEmptyValues()
	{
		$_GET = [
			'page' => 'onoffice-estates',
			'tab' => 'similar-estates'
		];
		$pSubject = new InputFieldTemplateListRenderer('testRenderer', []);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<div class="template-list"></div>', $output);
	}

	/**
	 *
	 */
	public function testRenderEmptyChecked()
	{
		$_GET = [
			'page' => 'onoffice-estates',
			'tab' => 'detail'
		];
		$pSubject = new InputFieldTemplateListRenderer('testRenderer', []);
		$pSubject->setValue([
			[
				'path' => [
					'abc' => 'abc',
				],
				'title' => 'abc',
				'folder' => 'abc'
			]
		]);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<div class="template-list"><input type="radio" name="testRenderer" '
			.'value="abc" id="labelradio_1babc"><label for="labelradio_1babc">abc'
			.'</label><br><p class="oo-template-folder-path">(in the folder abc)</p></div>', $output);
	}

	/**
	 *
	 */
	public function testRenderWithOneValue()
	{
		$pSubject = new InputFieldTemplateListRenderer('testRenderer', []);
		$pSubject->setCheckedValue('abc');

		$pSubject->setValue([
			[
				'path' => [
					'abc' => 'abc',
				],
				'title' => 'abc',
				'folder' => 'abc'
			]
		]);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<div class="template-list">'
			. '<input type="radio" name="testRenderer" value="abc" checked="checked"  id="labelradio_1babc">'
			. '<label for="labelradio_1babc">abc</label><br><p class="oo-template-folder-path">(in the folder abc)</p></div>', $output);
	}

	/**
	 *
	 */
	public function testRenderWithMoreValues()
	{
		$pSubject = new InputFieldTemplateListRenderer('testRenderer', []);
		$pSubject->setCheckedValue('abc');

		$pSubject->setValue([
			[
				'path' => [
					'abc' => 'abc',
				],
				'title' => 'abc',
				'folder' => 'abc'
			],
			[
				'path' => [
					'qwe' => 'qwe',
				],
				'title' => 'qwe',
				'folder' => 'qwe'
			]
		]);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<div class="template-list"><details open><summary>abc</summary>'
			. '<input type="radio" name="testRenderer" value="abc" checked="checked"  id="labelradio_1babc">'
			. '<label for="labelradio_1babc">abc</label><br><p class="oo-template-folder-path">(in the folder abc)</p></details><details>'
			. '<summary>qwe</summary><input type="radio" name="testRenderer" value="qwe" id="labelradio_1bqwe">'
			. '<label for="labelradio_1bqwe">qwe</label><br><p class="oo-template-folder-path">(in the folder qwe)</p></details></div>', $output);
	}

	/**
	 *
	 */
	public function testRenderWithCheckedNotMatchValues()
	{
		$_GET = [
			'page' => 'onoffice-editform',
			'tab' => 'detail',
			'type' => 'contact',
			'id' => null
		];
		$pSubject = new InputFieldTemplateListRenderer('testRenderer', []);
		$pSubject->setCheckedValue('asd');

		$pSubject->setValue([
			[
				'path' => [
					'abc' => 'abc',
				],
				'title' => 'abc',
				'folder' => 'abc'
			],
			[
				'path' => [
					'qwe' => 'qwe',
				],
				'title' => 'qwe',
				'folder' => 'qwe'
			]
		]);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<div class="template-list"><details><summary>abc</summary>'
			.'<input type="radio" name="testRenderer" value="abc" id="labelradio_1babc">'
			.'<label for="labelradio_1babc">abc</label><br><p class="oo-template-folder-path">(in the folder abc)</p></details><details>'
			.'<summary>qwe</summary><input type="radio" name="testRenderer" value="qwe" id="labelradio_1bqwe">'
			.'<label for="labelradio_1bqwe">qwe</label><br><p class="oo-template-folder-path">(in the folder qwe)</p></details></div>', $output);
	}


	/**
	 *
	 */
	public function testRenderWithAddressType()
	{
		$_GET = [
			'page' => 'onoffice-editlistviewaddress',
		];
		$pSubject = new InputFieldTemplateListRenderer('testRenderer', []);
		$pSubject->setCheckedValue('asd');
		$pSubject->setValue([
			[
				'path' => [
					'abc' => 'abc',
				],
				'title' => 'abc',
				'folder' => 'abc'
			],
			[
				'path' => [
					'qwe' => 'qwe',
				],
				'title' => 'qwe',
				'folder' => 'qwe'
			]
		]);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<div class="template-list"><details><summary>abc</summary>'
			.'<input type="radio" name="testRenderer" value="abc" id="labelradio_1babc">'
			.'<label for="labelradio_1babc">abc</label><br><p class="oo-template-folder-path">(in the folder abc)</p></details><details>'
			.'<summary>qwe</summary><input type="radio" name="testRenderer" value="qwe" id="labelradio_1bqwe">'
			.'<label for="labelradio_1bqwe">qwe</label><br><p class="oo-template-folder-path">(in the folder qwe)</p></details></div>', $output);
	}
}