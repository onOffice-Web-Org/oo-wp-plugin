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

use onOffice\WPlugin\Filter\DefaultFilterBuilderDetailView;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassDefaultFilterBuilderDetailView
	extends WP_UnitTestCase
{
	public function testBuildFilterNoEstateId()
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('EstateId must not be 0');
		$pInstance = new DefaultFilterBuilderDetailView();
		$pInstance->buildFilter();
	}


	/**
	 *
	 */

	public function testBuildFilter()
	{
		$pInstance = new DefaultFilterBuilderDetailView();
		$pInstance->setEstateId(13);
		$filterResult = $pInstance->buildFilter();

		$expectation = [
			'veroeffentlichen' => [
				[
					'op' => '=',
					'val' => '1',
				]
			],
			'Id' => [
				[
					'op' => '=',
					'val' => 13,
				]
			],
		];
		$this->assertEquals($expectation, $filterResult);
	}


	/**
	 *
	 */

	public function testGetterSetter()
	{
		$pInstance = new DefaultFilterBuilderDetailView();
		$this->assertEquals(0, $pInstance->getEstateId());
		$pInstance->setEstateId(13);
		$this->assertEquals(13, $pInstance->getEstateId());
	}
}
