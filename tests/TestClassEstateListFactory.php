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

use DI\Container;
use DI\ContainerBuilder;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\EstateList;
use onOffice\WPlugin\Factory\EstateListFactory;
use onOffice\WPlugin\Filter\DefaultFilterBuilderDetailView;
use WP_UnitTestCase;


/**
 *
 */

class TestClassEstateListFactory
	extends WP_UnitTestCase
{
	/** @var Container */
	private $_pContainer;

	/**
	 * @before
	 */
	public function prepare()
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();
	}

	public function testCreateEstateDetail()
	{
		$pDataDetailViewHandler = $this->getMockBuilder(DataDetailViewHandler::class)
			->getMock();
		$pDataDetailView = new DataDetailView();
		$pDataDetailViewHandler->expects($this->once())->method('getDetailView')
			->will($this->returnValue($pDataDetailView));

		$this->_pContainer->set(DataDetailViewHandler::class, $pDataDetailViewHandler);
		$pSubject = $this->_pContainer->get(EstateListFactory::class);

		$pDataDetail = $pSubject->createEstateDetail(3);
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
			'referenz' => [
				['op' => '!=', 'val' => 1]
			],
		], $pDataDetail->getDefaultFilterBuilder()->buildFilter());
	}

	public function testCreateEstateList()
	{
		$pSubject = $this->_pContainer->get(EstateListFactory::class);
		$pDataListView = new DataListView(123, 'testEstateListView');
		$pResult = $pSubject->createEstateList($pDataListView);
		$this->assertInstanceOf(EstateList::class, $pResult);
		$this->assertSame($pDataListView, $pResult->getDataView());
	}
}
