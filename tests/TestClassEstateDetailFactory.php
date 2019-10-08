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

use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\Factory\EstateDetailFactory;
use onOffice\WPlugin\Filter\DefaultFilterBuilderDetailView;
use WP_UnitTestCase;


/**
 *
 */

class TestClassEstateDetailFactory
	extends WP_UnitTestCase
{
	/** @var EstateDetailFactory */
	private $_pSubject = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$pDataDetailViewHandler = $this->getMockBuilder(DataDetailViewHandler::class)
			->getMock();
		$pDataDetailView = new DataDetailView();
		$pDataDetailViewHandler->expects($this->once())->method('getDetailView')
			->will($this->returnValue($pDataDetailView));
		$this->_pSubject = new EstateDetailFactory($pDataDetailViewHandler);
	}


	/**
	 *
	 */

	public function testCreateEstateDetail()
	{
		$pDataDetail = $this->_pSubject->createEstateDetail(3);
		$this->assertEquals(3, $pDataDetail->getEstateId());
		$this->assertInstanceOf(DefaultFilterBuilderDetailView::class, $pDataDetail->getDefaultFilterBuilder());
		$this->assertInstanceOf(DataDetailView::class, $pDataDetail->getDataView());
		$this->assertEquals([
			'veroeffentlichen' => [
				['op' => '=', 'val' => 1],
			],
			'Id' => [
				['op' => '=', 'val' => 3],
			],
		], $pDataDetail->getDefaultFilterBuilder()->buildFilter());
	}
}
