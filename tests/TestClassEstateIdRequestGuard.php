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

use Generator;
use onOffice\WPlugin\ArrayContainerEscape;
use onOffice\WPlugin\Controller\EstateDetailUrl;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\EstateDetail;
use onOffice\WPlugin\Factory\EstateListFactory;
use onOffice\WPlugin\Record\EstateIdRequestGuard;
use WP_UnitTestCase;

/**
 *
 */

class TestClassEstateIdRequestGuard
	extends WP_UnitTestCase
{
	/**
	 *
	 * @dataProvider dataProvider
	 *
	 * @param int $estateId
	 * @param bool|ArrayContainerEscape $iterator
	 * @param bool $result
	 *
	 */

	public function testIsValid(int $estateId, $iterator, bool $result)
	{
		$pEstateDetailFactory = $this->getMockBuilder(EstateListFactory::class)
			->disableOriginalConstructor()
			->getMock();
		$pEstateDetail = $this->getMockBuilder(EstateDetail::class)
			->setConstructorArgs([new DataDetailView()])
			->setMethods(['loadEstates', 'estateIterator'])
			->getMock();
		$pEstateDetail
			->expects($this->once())->method('estateIterator')
			->will($this->returnValue($iterator));
		$pEstateDetailFactory->method('createEstateDetail')->will($this->returnValue($pEstateDetail));
		$pSubject = new EstateIdRequestGuard($pEstateDetailFactory);
		$this->assertEquals($result, $pSubject->isValid($estateId));
	}


	/**
	 *
	 * @dataProvider dataProvider
	 *
	 * @param int $estateId
	 * @param bool|ArrayContainerEscape $iterator
	 * @param bool $result
	 *
	 */
	public function testCreateEstateDetailLinkForSwitchLanguageWPML(int $estateId, $iterator, bool $result)
	{
		add_option('onoffice-detail-view-showTitleUrl', true);
		$url = 'https://www.onoffice.de/detail/';
		$title =  $iterator ? '-' . $iterator['objekttitel'] : '';
		$expectedUrl = 'https://www.onoffice.de/detail/'. $estateId . $title;

		$pEstateDetailFactory = $this->getMockBuilder(EstateListFactory::class)
			->disableOriginalConstructor()
			->getMock();
		$pEstateDetail = $this->getMockBuilder(EstateDetail::class)
			->setConstructorArgs([new DataDetailView()])
			->setMethods(['loadEstates', 'estateIterator'])
			->getMock();
		$pEstateDetail->expects($this->once())->method('estateIterator')
			->will($this->returnValue($iterator));

		$pEstateDetailFactory->method('createEstateDetail')->will($this->returnValue($pEstateDetail));
		$pSubject = new EstateIdRequestGuard($pEstateDetailFactory);
		$pEstateDetailUrl = new EstateDetailUrl();

		$result = $pSubject->createEstateDetailLinkForSwitchLanguageWPML($url, $estateId, $pEstateDetailUrl);
		$this->assertEquals($expectedUrl, $result);
	}

	/**
	 *
	 * @return Generator
	 *
	 */

	public function dataProvider(): Generator
	{
		yield from [
			[3, new ArrayContainerEscape(['Id' => 3, 'objekttitel' => 'title-3']), true],
			[5, false, false],
			[7, false, false],
			[9, new ArrayContainerEscape(['Id' => 9, 'objekttitel' => 'title-9']), true],
		];
	}
}