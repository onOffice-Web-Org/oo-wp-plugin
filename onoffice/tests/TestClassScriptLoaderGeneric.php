<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

use onOffice\WPlugin\ScriptLoader\IncludeFileModel;
use onOffice\WPlugin\ScriptLoader\ScriptLoaderGeneric;
use onOffice\WPlugin\ScriptLoader\ScriptLoaderGenericConfiguration;
use WP_UnitTestCase;
use function wp_styles;


/**
 *
 */

class TestClassScriptLoaderGeneric
	extends WP_UnitTestCase
{
	/** @var ScriptLoaderGeneric */
	private $_pSubject = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$pConfig = $this->getMockBuilder(ScriptLoaderGenericConfiguration::class)->getMock();
		$pConfig->method('getScriptLoaderGenericConfiguration')
			->will($this->returnCallback(function(): array {
				$pModel1 = new IncludeFileModel(IncludeFileModel::TYPE_SCRIPT, 'script1', '/test/file.js');
				$pModel1->setDependencies(['testdep']);
				$pModel1->setLoadInFooter(true);
				$pModel2 = new IncludeFileModel(IncludeFileModel::TYPE_STYLE, 'style2', '/test/file.css');
				return [$pModel1, $pModel2];
		}));
		$this->_pSubject = new ScriptLoaderGeneric($pConfig);
	}


	/**
	 *
	 */

	public function testRegister()
	{
		wp_styles()->registered = [];
		wp_scripts()->registered = [];

		$this->_pSubject->register();
		$this->assertEquals(['style2'], array_keys(wp_styles()->registered));
		$this->assertEquals(['script1'], array_keys(wp_scripts()->registered));
	}


	/**
	 *
	 * @depends testRegister
	 *
	 */

	public function testEnqueue()
	{
		wp_styles()->queue = [];
		wp_scripts()->queue = [];

		$this->_pSubject->enqueue();
		$this->assertEquals(['style2'], wp_styles()->queue);
		$this->assertEquals(['script1'], wp_scripts()->queue);
	}
}
