<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataDetailViewCheckAccessControl;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\EstateDetail;
use onOffice\WPlugin\Factory\EstateListFactory;
use WP_UnitTestCase;

class TestClassDataDetailViewCheckAccessControl
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

	public function testCheckAccessControl(int $estateId, $iterator, bool $result)
	{
		$pDataDetailViewHandler = new DataDetailViewHandler();
		$pDataDetailView = $pDataDetailViewHandler->getDetailView();
		$pDataDetailView->setHasDetailView(false);
		$pDataDetailViewHandler->saveDetailView($pDataDetailView);

		$pEstateDetailFactory = $this->getMockBuilder(EstateListFactory::class)
			->disableOriginalConstructor()
			->getMock();
		$pEstateDetail = $this->getMockBuilder(EstateDetail::class)
			->setConstructorArgs([new DataDetailView()])
			->setMethods(['loadEstates', 'estateIterator', 'getRawValues'])
			->getMock();
		$pEstateDetail
			->expects($this->once())->method('estateIterator')
			->will($this->returnValue($iterator));
		$pEstateDetail
			->expects($this->once())->method('getRawValues')
			->will($this->returnValue($iterator));
		$pEstateDetailFactory->method('createEstateDetail')->will($this->returnValue($pEstateDetail));

		$pDataDetailViewCheckAccessControl = new DataDetailViewCheckAccessControl($pDataDetailViewHandler,
			$pEstateDetailFactory);
		$accessControlChecker = $pDataDetailViewCheckAccessControl->checkAccessControl($estateId);

		$this->assertEquals($pDataDetailView->hasDetailView(), false);
		$this->assertEquals($result, $accessControlChecker);
	}


	/**
	 *
	 * @return Generator
	 *
	 */

	public function dataProvider(): Generator
	{
		yield from [
			[3, new ArrayContainerEscape([3 => ['elements' => ['referenz' => "1"]]]), false],
			[4, new ArrayContainerEscape([4 => ['elements' => ['referenz' => "0"]]]), true],
		];
	}
}
