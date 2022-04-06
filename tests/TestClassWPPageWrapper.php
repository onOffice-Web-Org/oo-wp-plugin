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

use onOffice\WPlugin\WP\UnknownPageException;
use onOffice\WPlugin\WP\WPPageWrapper;
use WP_UnitTestCase;
use onOffice\WPlugin\Installer\DatabaseChanges;
use onOffice\WPlugin\Utility\__String;
use onOffice\WPlugin\WP\WPOptionWrapperTest;

/**
 * @preserveGlobalState disabled
 * @runTestsInSeparateProcesses
 */

class TestClassWPPageWrapper
	extends WP_UnitTestCase
{
	/** @var WPPageWrapper */
	private $_pSubject = null;

	/** @var int */
	private $_postId = 0;

	/** @var array */
	const POST_DATA = [
		'post_parent' => 0,
		'post_title' => 'onOffice Test Post',
		'post_content' => 'Hello. This is a test.',
		'post_status' => 'publish',
		'post_date' => '2019-05-09 13:37:37',
		'post_type' => 'page',
	];

	/** @var int */
	private $_ancestorId = 0;


	/**
	 * @before
	 */
	public function prepare()
	{
		global $wpdb;

		$pWpOption = new WPOptionWrapperTest();
		$pDbChanges = new DatabaseChanges($pWpOption, $wpdb);
		$pDbChanges->install();
		$this->set_permalink_structure('/%year%/%monthnum%/%day%/%postname%/');
		$this->_pSubject = new WPPageWrapper();
		// set this even though the permalink for pages always is %postname%
		$this->_ancestorId = wp_insert_post([
			'post_name' => 'test_parent_post',
			'post_title' => 'My Test Post',
			'post_type' => 'page',
			'post_status' => 'publish',
			'post_date' => '2016-05-01 13:37:37',
		]);
		$postData = self::POST_DATA;
		$postData['post_parent'] = $this->_ancestorId;
		$this->_postId = wp_insert_post($postData);
		$this->assertInternalType('integer', $this->_postId);
		$this->assertGreaterThan(0, $this->_postId);
	}

	public function testGetPageByPath()
	{
		$this->assertEquals($this->_ancestorId, $this->_pSubject->getPageByPath('test_parent_post')->ID);
		$this->assertEquals($this->_postId, $this->_pSubject->getPageByPath
			('test_parent_post/onoffice-test-post')->ID);
	}

	public function testGetPageByPathUnknown()
	{
		$this->expectException(UnknownPageException::class);
		$this->_pSubject->getPageByPath('unknown-page');
	}

	public function testGetPageLinkByPost()
	{
		$expectedLinkChild = 'http://example.org/test_parent_post/onoffice-test-post/';
		$this->assertSame($expectedLinkChild,
			$this->_pSubject->getPageLinkByPost(get_post($this->_postId)));
		$expectedLinkParent = 'http://example.org/test_parent_post/';
		$this->assertSame($expectedLinkParent,
			$this->_pSubject->getPageLinkByPost(get_post($this->_ancestorId)));
	}

	public function testGetPageLinkById()
	{
		$expectedLinkChild = 'http://example.org/test_parent_post/onoffice-test-post/';
		$this->assertSame($expectedLinkChild, $this->_pSubject->getPageLinkById($this->_postId));
		$expectedLinkParent = 'http://example.org/test_parent_post/';
		$this->assertSame($expectedLinkParent,
			$this->_pSubject->getPageLinkById($this->_ancestorId));
	}

	public function testGetPageUriByPageId()
	{
		$expectedLinkChild = 'test_parent_post/onoffice-test-post';
		$this->assertSame($expectedLinkChild, $this->_pSubject->getPageUriByPageId($this->_postId));
		$expectedLinkParent = 'test_parent_post';
		$this->assertSame($expectedLinkParent,
			$this->_pSubject->getPageUriByPageId($this->_ancestorId));
	}
}