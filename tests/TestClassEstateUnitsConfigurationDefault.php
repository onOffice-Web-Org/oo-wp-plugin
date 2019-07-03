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

use onOffice\WPlugin\Controller\EstateUnitsConfigurationDefault;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\EstateList;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Template;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassEstateUnitsConfigurationDefault
	extends WP_UnitTestCase
{
	/** @var EstateUnitsConfigurationDefault */
	private $_pSubject = null;


	/**
	 *
	 */

	public function testGetEstateList()
	{
		$this->assertInstanceOf(EstateList::class, $this->_pSubject->getEstateList());
	}


	/**
	 *
	 */

	public function testGetSDKWrapper()
	{
		$this->assertInstanceOf(SDKWrapper::class, $this->_pSubject->getSDKWrapper());
	}


	/**
	 *
	 */

	public function testGetTemplate()
	{
		$this->assertInstanceOf(Template::class, $this->_pSubject->getTemplate('testtemplate'));
	}


	/**
	 *
	 * @before
	 *
	 */

	public function prepareSubject()
	{
		$pDataView = new DataListView(1, 'test');
		$this->_pSubject = new EstateUnitsConfigurationDefault($pDataView);
	}
}
