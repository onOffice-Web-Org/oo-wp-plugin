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

use onOffice\WPlugin\Controller\ContentFilter\LinkBuilderPage;
use onOffice\WPlugin\WP\WPPageWrapper;
use onOffice\WPlugin\WP\WPQueryWrapper;
use WP_Post;
use WP_Query;
use WP_UnitTestCase;


/**
 *
 */

class TestClassLinkBuilderPage
	extends WP_UnitTestCase
{
	/** @var LinkBuilderPage */
	private $_pSubject = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$pWPQuery = $this->getMockBuilder(WP_Query::class)
			->setMethods(['get'])
			->getMock();
		$pWPQuery->method('get')
			->with('estate_id', 0)->will($this->onConsecutiveCalls('123', '0', '123'));

		$pWPQueryWrapper = $this->getMockBuilder(WPQueryWrapper::class)
			->setMethods(['getWPQuery'])
			->getMock();
		$pWPQueryWrapper->method('getWPQuery')->will($this->returnValue($pWPQuery));

		$pWPPageWrapper = $this->getMockBuilder(WPPageWrapper::class)
			->setMethods(['getPageByPath', 'getPageLinkByPost'])
			->getMock();
		$pPost = $this->getWPPost();
		$pWPPageWrapper->method('getPageByPath')->with('hello')->will($this->returnValue($pPost));
		$pWPPageWrapper->method('getPageLinkByPost')
			->with($pPost)->will($this->returnValue('hello'));

		$this->_pSubject = new LinkBuilderPage($pWPPageWrapper, $pWPQueryWrapper);
	}


	/**
	 *
	 * @return WP_Post
	 *
	 */

	private function getWPPost(): WP_Post
	{
		$postValues = [
			'ID' => 15,
			'post_author' => 3,
			'post_date' => '0000-00-00 00:00:00',
			'post_date_gmt' => '0000-00-00 00:00:00',
			'post_content' => '',
			'post_title' => 'Hello',
			'post_excerpt' => 'He...',
			'post_status' => 'publish',
			'comment_status' => 'open',
			'ping_status' => 'open',
			'post_password' => '',
			'post_name' => 'hello',
			'post_parent' => 0,
			'guid' => 'hello',
		];

		return new WP_Post((object)$postValues);
	}


	/**
	 *
	 */

	public function testBuildLinkByPath()
	{
		$resultWithEstate = $this->_pSubject->buildLinkByPath('hello', true);
		$this->assertEquals('hello?estate_id=123', $resultWithEstate);
		$resultWithoutEstate = $this->_pSubject->buildLinkByPath('hello', true);
		$this->assertEquals('hello', $resultWithoutEstate);
		$resultWithoutEstateContext = $this->_pSubject->buildLinkByPath('hello', false);
		$this->assertEquals('hello', $resultWithoutEstateContext);
	}
}