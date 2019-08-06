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

use onOffice\WPlugin\Controller\MainPageFileMapping;
use WP_UnitTestCase;


/**
 *
 */

class TestClassMainPageFileMapping
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGetMapping()
	{
		$pMainPageFileMapping = new MainPageFileMapping();
		$mapping = $pMainPageFileMapping->getMapping();
		$this->assertGreaterThanOrEqual(1, $mapping);
		$this->assertArrayHasKey('de_DE', $mapping);
	}
}
