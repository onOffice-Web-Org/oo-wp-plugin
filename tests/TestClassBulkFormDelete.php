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

use onOffice\WPlugin\Controller\UserCapabilities;
use onOffice\WPlugin\Form\BulkFormDelete;
use onOffice\WPlugin\Record\RecordManagerDeleteForm;
use onOffice\WPlugin\WP\WPQueryWrapper;
use WP_UnitTestCase;

/**
 *
 */

class TestClassBulkFormDelete
	extends WP_UnitTestCase
{
	/** @var BulkFormDelete */
	private $_pSubject = null;

	/** @var RecordManagerDeleteForm */
	private $_pRecordManagerDeleteForm = null;

	/** @var UserCapabilities */
	private $_pUserCapabilities = null;

	/** @var WPQueryWrapper */
	private $_pWPQueryWrapper = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pRecordManagerDeleteForm = $this->getMockBuilder(RecordManagerDeleteForm::class)
			->disableOriginalConstructor()
			->getMock();
		$this->_pWPQueryWrapper = $this->getMockBuilder(WPQueryWrapper::class)->getMock();
		$this->_pUserCapabilities = $this->getMockBuilder(UserCapabilities::class)
			->setMethods(['getCapabilityForRule'])
			->getMock();

		$this->_pSubject = new BulkFormDelete
			($this->_pRecordManagerDeleteForm, $this->_pWPQueryWrapper, $this->_pUserCapabilities);
	}


	/**
	 *
	 */

	public function testDelete()
	{
		wp_get_current_user()->add_role('edit_pages');
		$this->_pUserCapabilities->expects($this->exactly(3))->method('getCapabilityForRule')
			->will($this->returnValue('edit_pages'));
		$this->assertSame(2, $this->_pSubject->delete('bulk_delete', [13, 14]));
		$this->assertSame(1, $this->_pSubject->delete('delete', [11]));
		$this->assertSame(0, $this->_pSubject->delete('asdf', [11]));
	}


	/**
	 *
	 * @expectedException \Exception
	 * @expectedExceptionMessage Not allowed
	 *
	 */

	public function testDeleteNoRight()
	{
		$this->_pUserCapabilities->method('getCapabilityForRule')
			->will($this->returnValue('other_role'));
		$this->_pSubject->delete('bulk_delete', [13, 14]);
	}


	/**
	 *
	 */

	public function tearDown()
	{
		wp_get_current_user()->remove_role('edit_pages');

		parent::tearDown();
	}
}
