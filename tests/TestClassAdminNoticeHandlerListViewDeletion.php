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

use onOffice\WPlugin\Gui\AdminNoticeHandlerListViewDeletion;
use WP_UnitTestCase;

/**
 *
 */

class TestClassAdminNoticeHandlerListViewDeletion
	extends WP_UnitTestCase
{
	/** @var AdminNoticeHandlerListViewDeletion */
	private $_pAdminNoticeHandlerListViewDeletion = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pAdminNoticeHandlerListViewDeletion = new AdminNoticeHandlerListViewDeletion();
	}


	/**
	 *
	 */

	public function testHandleListViewSuccess()
	{
		$this->assertStringContainsString('1 list view has been deleted.',
			$this->_pAdminNoticeHandlerListViewDeletion->handleListView(1));
		$this->assertStringContainsString('2 list views have been deleted.',
			$this->_pAdminNoticeHandlerListViewDeletion->handleListView(2));
		$this->assertStringContainsString('notice-success',
			$this->_pAdminNoticeHandlerListViewDeletion->handleListView(1));
	}


	/**
	 *
	 */

	public function testHandleListViewError()
	{
		$this->checkHandleError('list view', $this->_pAdminNoticeHandlerListViewDeletion->handleListView(0));
	}


	/**
	 *
	 */

	public function testHandleFormSuccess()
	{
		$this->assertStringContainsString('1 form has been deleted.',
			$this->_pAdminNoticeHandlerListViewDeletion->handleFormView(1));
		$this->assertStringContainsString('2 forms have been deleted.',
			$this->_pAdminNoticeHandlerListViewDeletion->handleFormView(2));
		$this->assertStringContainsString('notice-success',
			$this->_pAdminNoticeHandlerListViewDeletion->handleFormView(1));
	}


	/**
	 *
	 */

	public function testHandleFormError()
	{
		$this->checkHandleError('form', $this->_pAdminNoticeHandlerListViewDeletion->handleFormView(0));
	}


	/**
	 *
	 */

	private function checkHandleError(string $term, string $result)
	{
		$this->assertStringContainsString('No '.$term.' was deleted.', $result);
		$this->assertStringContainsString('notice-error', $result);
	}
}
