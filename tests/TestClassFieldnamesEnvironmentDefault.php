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

use onOffice\WPlugin\Field\FieldnamesEnvironmentDefault;
use onOffice\WPlugin\SDKWrapper;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassFieldnamesEnvironmentDefault
	extends WP_UnitTest_Localized
{
	/**
	 *
	 */

	public function testGetSDKWrapper()
	{
		$pEnvironment = new FieldnamesEnvironmentDefault();
		$this->assertInstanceOf(SDKWrapper::class, $pEnvironment->getSDKWrapper());
	}


	/**
	 *
	 */

	public function testGetLanguage()
	{
		$pEnvironment = new FieldnamesEnvironmentDefault();
		$this->assertEquals('DEU', $pEnvironment->getLanguage());

		$this->switchLocale('en_US');
		$this->assertEquals('ENG', $pEnvironment->getLanguage());

		$this->switchLocale('de_DE');
	}
}
