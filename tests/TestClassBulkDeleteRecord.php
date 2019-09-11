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
use onOffice\WPlugin\Form\BulkDeleteRecord;
use onOffice\WPlugin\Record\RecordManagerDeleteForm;
use WP_UnitTestCase;


/**
 *
 */

class TestClassBulkDeleteRecord
	extends WP_UnitTestCase
{
	/** @var BulkDeleteRecord */
	private $_pSubject = null;

	/** @var RecordManagerDeleteForm */
	private $_pRecordManagerDeleteForm = null;

	/** @var UserCapabilities */
	private $_pUserCapabilities = null;


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
		$this->_pUserCapabilities = $this->getMockBuilder(UserCapabilities::class)
			->setMethods(['checkIfCurrentUserCan'])
			->getMock();

		$this->_pSubject = new BulkDeleteRecord($this->_pUserCapabilities);
	}


	/**
	 *
	 */

	public function testDelete()
	{
		$this->_pUserCapabilities->expects($this->exactly(2))->method('checkIfCurrentUserCan')
			->with(UserCapabilities::RULE_EDIT_VIEW_FORM);
		$this->assertSame(2, $this->_pSubject->delete($this->_pRecordManagerDeleteForm,
			UserCapabilities::RULE_EDIT_VIEW_FORM, [13, 14]));
		$this->assertSame(1, $this->_pSubject->delete($this->_pRecordManagerDeleteForm,
			UserCapabilities::RULE_EDIT_VIEW_FORM, [11]));
	}


	/**
	 *
	 * @expectedException \onOffice\WPlugin\Controller\Exception\UserCapabilitiesException
	 *
	 */

	public function testDeleteNoRight()
	{
		$pException = new \onOffice\WPlugin\Controller\Exception\UserCapabilitiesException();
		$this->_pUserCapabilities->method('checkIfCurrentUserCan')
			->with(UserCapabilities::RULE_EDIT_VIEW_FORM)
			->will($this->throwException($pException));
		$this->_pSubject->delete($this->_pRecordManagerDeleteForm, UserCapabilities::RULE_EDIT_VIEW_FORM, [13, 14]);
	}
}
