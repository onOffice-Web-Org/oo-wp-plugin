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

use onOffice\WPlugin\WP\WPPageWrapper;
use WP_UnitTestCase;


/**
 *
 */

class TestClassWPPageWrapper
	extends WP_UnitTestCase
{
	/** @var WPPageWrapper */
	private $_pSubject = null;

	/** @var int */
	private $_postId = 0;

	/** @var array */
	private $_postData = [
		'post_title' => 'onOffice Test Post',
		'post_content' => 'Hello. This is a test.',
		'post_status' => 'published',
		'post_date' => '2019-05-09 13:37:37',
		'post_type' => 'page',
	];


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pSubject = new WPPageWrapper();
		// set this even though the permalink for pages always is %postname%
		$this->set_permalink_structure('/%year%/%monthnum%/%day%/%postname%/');
		$this->_postId = wp_insert_post($this->_postData);
		$this->assertInternalType('integer', $this->_postId);
		$this->assertGreaterThan(0, $this->_postId);
	}


	/**
	 *
	 */

	public function testGetPageByPath()
	{
		$this->assertEquals($this->_postId, $this->_pSubject->getPageByPath('onoffice-test-post')->ID);
	}


	/**
	 *
	 * @expectedException \onOffice\WPlugin\WP\UnknownPageException
	 *
	 */

	public function testGetPageByPathUnknown()
	{
		$this->_pSubject->getPageByPath('unknown-page');
	}


	/**
	 *
	 */

	public function testGetPageLinkByPost()
	{
		$pPost = get_post($this->_postId);
		$expectedLink = 'http://example.org/onoffice-test-post/';
		$this->assertEquals($expectedLink, $this->_pSubject->getPageLinkByPost($pPost));
	}
}