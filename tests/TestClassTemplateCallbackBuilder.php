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
use Closure;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\Controller\SortList\SortListDropDownGenerator;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\EstateList;
use onOffice\WPlugin\Template\TemplateCallbackBuilder;
use WP_UnitTestCase;

class TestClassTemplateCallbackBuilder
	extends WP_UnitTestCase
{
	/**
	 * @return EstateList
	 */
	private function buildEstateList(): EstateList
	{
		$pListView = new DataListView(1, 'asd');
		$pListView->setSortByUserValues(['kaltmiete', 'kaufpreis']);
		$pListView->setSortby('kaufpreis');
		$pListView->setSortorder('DESC');
		$pListView->setSortBySetting(1);
		$pListView->setSortByUserDefinedDefault('kaltmiete');
		$pListView->setSortByUserDefinedDirection(1);

		$pEstateList = $this->getMockBuilder(EstateList::class)
			->setMethods(['getDataView'])
			->disableOriginalConstructor()
			->getMock();

		$pEstateList->method('getDataView')
			->willReturn($pListView);
		return $pEstateList;
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testBuildCallbackListSortDropDown()
	{
		$pEstateList = $this->buildEstateList();
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();

		$pSortListDropDownGenerator = $this->getMockBuilder(SortListDropDownGenerator::class)
			->disableOriginalConstructor()
			->getMock();
		$pSortListDropDownGenerator->expects($this->once())->method('generate')->with('asd')
			->willReturn('HTML List here');

		$pContainer->set(SortListDropDownGenerator::class, $pSortListDropDownGenerator);
		$pInstance = $pContainer->get(TemplateCallbackBuilder::class);
		$pClosureWithEstateList = $pInstance->buildCallbackListSortDropDown($pEstateList);
		$this->assertInstanceOf(Closure::class, $pClosureWithEstateList);
		$this->assertSame('HTML List here', $pClosureWithEstateList());

		$pClosureDummy = $pInstance->buildCallbackListSortDropDown(null);
		$this->assertInstanceOf(Closure::class,  $pClosureDummy);
		$this->assertEquals('', $pClosureDummy());
	}

	public function testBuildCallbackEstateListName()
	{
		$pEstateList = $this->buildEstateList();
		$pSubject = new TemplateCallbackBuilder(new Container());
		$pClosureWithEstateList = $pSubject->buildCallbackEstateListName($pEstateList);
		$this->assertInstanceOf(Closure::class, $pClosureWithEstateList);
		$this->assertSame('asd', $pClosureWithEstateList());
		$pClosureDummy = $pSubject->buildCallbackEstateListName(null);
		$this->assertSame('', $pClosureDummy());
	}
}