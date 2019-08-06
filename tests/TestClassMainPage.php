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

use onOffice\WPlugin\Controller\MainPage;
use onOffice\WPlugin\Controller\MainPageFileMapping;
use onOffice\WPlugin\Filesystem\Filesystem;
use onOffice\WPlugin\Language;
use WP_UnitTestCase;

/**
 *
 */

class TestClassMainPage
	extends WP_UnitTestCase
{
	/** @var MainPageFileMapping */
	private $_pFileMapping = null;

	/** @var Language */
	private $_pLanguage = null;

	/** @var Filesystem */
	private $_pFilesystem = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pFileMapping = $this->getMockBuilder(MainPageFileMapping::class)
			->setMethods(['getMapping'])
			->getMock();
		$this->_pFileMapping->method('getMapping')->will($this->onConsecutiveCalls(
			[
				'es_ES' => 'es.html',
				'en_US' => 'en_US.html',
				'en_GB' => 'en_GB.html',
				'de_DE' => 'de.html',
				'zh_CN' => 'zh_CN.html',
			],
			[
				'en_US' => 'en_US.html',
				'en_GB' => 'en_GB.html',
				'de_DE' => 'de.html',
				'zh_CN' => 'zh_CN.html',
			],
			[
				'en_GB' => 'en_GB.html',
				'de_DE' => 'de.html',
				'zh_CN' => 'zh_CN.html',
			],
			[
				'de_DE' => 'de.html',
				'zh_CN' => 'zh_CN.html',
			]
		));

		$this->_pFilesystem = $this->getMockBuilder(Filesystem::class)
			->getMock();

		$this->_pFilesystem->method('getContents')->with($this->anything())
			->will($this->returnCallback(function(string $file): string {
				return 'contents of '.$file;
			}));

		$this->_pLanguage = $this->getMockBuilder(Language::class)->getMock();
		$this->_pLanguage->expects($this->atLeast(1))->method('getLocale')->will($this->returnValue('es_ES'));

	}


	/**
	 *
	 */

	public function testRender()
	{
		$pMainPage = new MainPage($this->_pLanguage, $this->_pFileMapping, $this->_pFilesystem);
		$this->assertStringStartsWith('<div class="card">contents of es.html</div>', $pMainPage->render());
		$this->assertStringStartsWith('<div class="card">contents of en_US.html</div>', $pMainPage->render());
		$this->assertStringStartsWith('<div class="card">contents of en_GB.html</div>', $pMainPage->render());
		$this->assertStringStartsWith('<div class="card">contents of de.html</div>', $pMainPage->render());
	}
}
