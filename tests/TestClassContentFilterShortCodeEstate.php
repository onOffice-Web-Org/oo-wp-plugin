<?php

/**
 *
 *    Copyright (C) 2020 onOffice GmbH
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
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeEstate;
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeEstateDetail;
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeEstateList;

class TestClassContentFilterShortCodeEstate
	extends \WP_UnitTestCase
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
		$pContentFilterShortCodeEstateDetail = $this->getMockBuilder
			(ContentFilterShortCodeEstateDetail::class)
				->disableOriginalConstructor()
				->getMock();
		$pContentFilterShortCodeEstateDetail
			->method('getViewName')->will($this->returnValue('default_view'));
		$pContentFilterShortCodeEstateList = $this->getMockBuilder
			(ContentFilterShortCodeEstateList::class)
				->disableOriginalConstructor()
				->getMock();
		$this->_pContainer->set(ContentFilterShortCodeEstateDetail::class,
			$pContentFilterShortCodeEstateDetail);
		$this->_pContainer->set(ContentFilterShortCodeEstateList::class,
			$pContentFilterShortCodeEstateList);
	}

	public function testGetTag()
	{
		$result = $this->_pContainer->get(ContentFilterShortCodeEstate::class)->getTag();
		$this->assertSame('oo_estate', $result);
	}

	public function testReplaceShortCodesForDetail()
	{
		$input = [
			'view' => 'default_view',
			'units' => 'test_units',
		];
		$pContentFilterDetail = $this->_pContainer->get(ContentFilterShortCodeEstateDetail::class);
		$pContentFilterDetail->expects($this->once())
			->method('render')
			->with($input)
			->will($this->returnValue('rendered detail'));

		$pContentFilterList = $this->_pContainer->get(ContentFilterShortCodeEstateList::class);
		$pContentFilterList->expects($this->once())
			->method('render')
			->with(['view' => 'other', 'units' => null])
			->will($this->returnValue('rendered list'));

		$pSubject = $this->_pContainer->get(ContentFilterShortCodeEstate::class);
		$this->assertSame('rendered detail', $pSubject->replaceShortCodes($input));
		$this->assertSame('rendered list', $pSubject->replaceShortCodes(['view' => 'other']));
	}
}