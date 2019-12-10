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
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\EstateList;
use onOffice\WPlugin\Template\TemplateCallbackBuilder;
use WP_UnitTestCase;

class TestClassTemplateCallbackBuilder
	extends WP_UnitTestCase
{
	/**
	 * @covers onOffice\WPlugin\Template\TemplateCallbackBuilder::buildCallbackListSortDropDown
	 */
	public function testBuildCallbackListSortDropDown()
	{
		$pListView = new DataListView(1, 'asd');
		$pListView->setSortByUserValues(['kaltmiete', 'kaufpreis']);
		$pListView->setSortby('kaufpreis');
		$pListView->setSortorder('DESC');
		$pListView->setSortBySetting(1);
		$pListView->setSortByUserDefinedDefault('kaltmiete');
		$pListView->setSortByUserDefinedDirection(1);

		$pInstance = new TemplateCallbackBuilder(new Container());
		$this->assertInstanceOf(TemplateCallbackBuilder::class, $pInstance);

		$pEstateList = $this->getMockbuilder(EstateList::class)
			->setMethods(['getDataView'])
			->disableOriginalConstructor()
			->getMock();

		$pEstateList->method('getDataView')
			->with($this->anything())
			->willReturn($pListView);

		$this->assertInstanceOf(Closure::class, $pInstance->buildCallbackListSortDropDown($pEstateList));
		$this->assertInstanceOf(Closure::class,  $pInstance->buildCallbackListSortDropDown(null));
	}
}