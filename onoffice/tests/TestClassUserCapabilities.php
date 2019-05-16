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
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassUserCapabilities
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGetCapabilityForRule()
	{
		$pUserCapabilities = new UserCapabilities();
		$this->assertEquals('edit_pages', $pUserCapabilities->getCapabilityForRule
			(UserCapabilities::RULE_DEBUG_OUTPUT));
		$this->assertEquals('edit_pages', $pUserCapabilities->getCapabilityForRule
			(UserCapabilities::RULE_EDIT_VIEW_ADDRESS));
		$this->assertEquals('edit_pages', $pUserCapabilities->getCapabilityForRule
			(UserCapabilities::RULE_EDIT_VIEW_ESTATE));
		$this->assertEquals('edit_pages', $pUserCapabilities->getCapabilityForRule
			(UserCapabilities::RULE_EDIT_VIEW_FORM));
	}


	/**
	 *
	 * @expectedException \UnexpectedValueException
	 *
	 */

	public function testGetCapabilityForRuleUnknown()
	{
		$pUserCapabilities = new UserCapabilities();
		$pUserCapabilities->getCapabilityForRule('unknown');
	}
}
