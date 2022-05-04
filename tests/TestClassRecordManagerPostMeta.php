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

namespace onOffice\tests;

use onOffice\WPlugin\Record\RecordManagerPostMeta;
use WP_UnitTestCase;
use wpdb;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassRecordManagerPostMeta
    extends WP_UnitTestCase
{
	/**
	* @var object
	*/
	private $_pRecordManagerPostMeta;

	/** @var wpdb */
	private $_pWPDB = null;

	/**
	*
	* @before
	*
	*/
	public function prepare()
	{
		$this->_pWPDB = $this->getMockBuilder(wpdb::class)
			->setMethods(['delete'])
			->disableOriginalConstructor()
			->getMock();
		$this->_pWPDB->prefix = 'wp_test_';
		$this->_pRecordManagerPostMeta = new RecordManagerPostMeta($this->_pWPDB);
	}

	public function testConstruct()
	{
		$pRecordManagerPostMeta = new RecordManagerPostMeta($this->_pWPDB);
		$this->assertInstanceOf(wpdb::class, $pRecordManagerPostMeta->getWPDB());
		$this->assertEquals('[oo_estate view="detail"]', $pRecordManagerPostMeta->getShortCodePageDetail());
	}
	
	/**
	 * @covers \onOffice\WPlugin\Record\RecordManagerPostMeta::getPageId
	 */
	public function testGetPageIdInPostMeta()
	{
		$pFieldsPostMeta = $this->_pRecordManagerPostMeta->getPageId();
		$this->assertEquals([], $pFieldsPostMeta);
	}

	/**
	 * @covers \onOffice\WPlugin\Record\RecordManagerPostMeta::deletePostMetaUseCustomField
	 */
	public function testDeletePostMetaUseCustomField()
	{
		$this->_pWPDB->expects($this->once())->method('delete')
			->with('wp_test_postmeta', ['meta_key' => 'shortcode','meta_value' => '[oo_estate view="detail"]']);
		$this->_pRecordManagerPostMeta->deletePostMetaUseCustomField('shortcode');
	}
}
