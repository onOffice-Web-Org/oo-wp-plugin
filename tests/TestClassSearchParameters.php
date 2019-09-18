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

use onOffice\WPlugin\SearchParameters;
use WP_UnitTestCase;


/**
 *
 * test class for SearchParameters.php
 *
 */

class TestClassSearchParameters
	extends WP_UnitTestCase
{

	/**
	 *
	 */

	public function testSetParameter()
	{
		$pInstance = new SearchParameters();
		$pInstance->setParameter('asd', 'valueAsd');
		$pInstance->enableFilter(false);

		$this->assertEquals(['asd' => 'valueAsd'], $pInstance->getParameters());
	}


	public function testAddAllowedGetParameter()
	{
		$pInstance = new SearchParameters();
		$pInstance->addAllowedGetParameter('asd');

		$this->assertEquals(['asd'], $pInstance->getAllowedGetParameters());
	}


	public function testSetAllowedGetParametes()
	{
		$pInstance = new SearchParameters();
		$pInstance->setAllowedGetParameters(['asd']);

		$this->assertEquals(['asd'], $pInstance->getAllowedGetParameters());
	}


	public function testEnableFilter()
	{
		$pInstance = new SearchParameters();
		$pInstance->enableFilter(true);

		$this->assertEquals(true, $pInstance->getFilter());
	}


	public function testPopulateDefaultLinkParams()
	{
		$pInstance = new SearchParameters();
		$this->assertEquals(['asd'], $pInstance->populateDefaultLinkParams(['asd']));
	}
}
