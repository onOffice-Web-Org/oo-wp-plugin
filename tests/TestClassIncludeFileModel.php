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
use WP_UnitTestCase;

/**
 *
 */

class TestClassIncludeFileModel
	extends WP_UnitTestCase
{
	/**
	 *
	 * @return IncludeFileModel
	 *
	 */

	public function testDefaultValues(): IncludeFileModel
	{
		$pIncludeFileModel = new IncludeFileModel(IncludeFileModel::TYPE_SCRIPT, 'testInclude', '/test/include');
		$this->assertEquals('script', $pIncludeFileModel->getType());
		$this->assertEquals('testInclude', $pIncludeFileModel->getIdentifier());
		$this->assertEquals('/test/include', $pIncludeFileModel->getFilePath());
		$this->assertSame([], $pIncludeFileModel->getDependencies());
		$this->assertFalse($pIncludeFileModel->getLoadInFooter());
		return $pIncludeFileModel;
	}


	/**
	 *
	 * @depends testDefaultValues
	 *
	 */

	public function testSetter(IncludeFileModel $pIncludeFileModel)
	{
		$this->assertSame($pIncludeFileModel, $pIncludeFileModel->setDependencies(['test1', 'test2']));
		$this->assertEquals(['test1', 'test2'], $pIncludeFileModel->getDependencies());
		$this->assertSame($pIncludeFileModel, $pIncludeFileModel->setLoadInFooter(true));
		$this->assertTrue($pIncludeFileModel->getLoadInFooter());
		$this->assertSame($pIncludeFileModel, $pIncludeFileModel->setLoadAsynchronous('defer'));
		$this->assertEquals('defer', $pIncludeFileModel->getLoadAsynchronous());
	}
}
