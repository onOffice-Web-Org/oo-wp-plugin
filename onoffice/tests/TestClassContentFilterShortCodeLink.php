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

use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeLink;
use onOffice\WPlugin\Controller\ContentFilter\LinkBuilderPage;
use onOffice\WPlugin\Utility\Logger;
use onOffice\WPlugin\WP\UnknownPageException;
use WP_UnitTestCase;

/**
 *
 */

class TestClassContentFilterShortCodeLink
	extends WP_UnitTestCase
{
	/** @var ContentFilterShortCodeLink */
	private $_pSubject = null;

	/** @var Logger */
	private $_pLogger = null;

	/** @var LinkBuilderPage */
	private $_pLinkBuilderPage = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pLogger = $this->getMockBuilder(Logger::class)
			->setMethods(['logErrorAndDisplayMessage'])
			->getMock();

		$this->_pLinkBuilderPage = $this->getMockBuilder(LinkBuilderPage::class)
			->setMethods(['buildLinkByPath'])
			->getMock();

		$this->_pSubject = new ContentFilterShortCodeLink($this->_pLogger, $this->_pLinkBuilderPage);
	}


	/**
	 *
	 */

	public function testReplaceShortCodesDefault()
	{
		$this->_pLogger->expects($this->once())
			->method('logErrorAndDisplayMessage')
			->with($this->isInstanceOf(UnknownPageException::class))
			->will($this->returnValue('exc'));

		$this->_pLinkBuilderPage->expects($this->once())->method('buildLinkByPath')
			->with('', false)
			->will($this->throwException(new UnknownPageException()));

		$this->assertEquals('exc', $this->_pSubject->replaceShortCodes([]));
	}


	/**
	 *
	 */

	public function testReplaceShortCodesWithEstate()
	{
		$this->_pLinkBuilderPage->expects($this->once())->method('buildLinkByPath')
			->with('/estates/', true)
			->will($this->returnValue('https://localhost/estates/?estate_id=1337'));

		$result = $this->_pSubject->replaceShortCodes([
			'path' => '/estates/',
			'contexts' => 'estate',
		]);

		$this->assertEquals('https://localhost/estates/?estate_id=1337', $result);
	}


	/**
	 *
	 */

	public function testGetTag()
	{
		$this->assertEquals('oo_link', $this->_pSubject->getTag());
	}
}
