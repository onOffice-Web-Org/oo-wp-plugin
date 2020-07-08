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
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\SortList\SortListBuilder;
use onOffice\WPlugin\Controller\SortList\SortListDropDownGenerator;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataListViewFactory;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Renderer\SortListRenderer;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use WP_UnitTestCase;

class TestClassSortListDropDownGenerator
	extends WP_UnitTestCase
{
	/** @var DataListViewFactory */
	private $_pDataListViewFactory;

	/** @var SortListBuilder */
	private $_pSortListBuilder;

	/** @var SortListRenderer */
	private $_pSortListRenderer;

	/** @var DataListView */
	private $_pListView;

	/** @var FieldsCollectionBuilderShort */
	private $_pBuilder;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pSortListRenderer = new SortListRenderer;

		$this->_pListView = new DataListView(1, 'asd');
		$this->_pListView->setSortByUserValues(['kaltmiete', 'kaufpreis']);
		$this->_pListView->setSortby('kaufpreis');
		$this->_pListView->setSortorder('DESC');
		$this->_pListView->setSortBySetting(1);
		$this->_pListView->setSortByUserDefinedDefault('kaltmiete');
		$this->_pListView->setSortByUserDefinedDirection(1);

		$this->_pDataListViewFactory = $this->getMockBuilder(DataListViewFactory::class)
			->setMethods(['getListViewByName'])
			->disableOriginalConstructor()
			->getMock();

		$this->_pDataListViewFactory->method('getListViewByName')
			->with($this->anything())
			->willReturn($this->_pListView);

		$this->_pBuilder = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
			->setMethods(['addFieldsAddressEstate'])
			->setConstructorArgs([new Container])
			->getMock();

		$this->_pBuilder->method('addFieldsAddressEstate')
			->with($this->anything())
			->willReturnCallback(function (FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
				$pKaltmiete = new Field('kaltmiete', onOfficeSDK::MODULE_ESTATE);
				$pKaltmiete->setLabel('Kaltmiete');
				$pFieldsCollection->addField($pKaltmiete);

				$pKaufpreis = new Field('kaufpreis', onOfficeSDK::MODULE_ESTATE);
				$pKaufpreis->setLabel('Kaufpreis');
				$pFieldsCollection->addField($pKaufpreis);

				return $this->_pBuilder;
			});

		$this->_pSortListBuilder = new SortListBuilder($this->_pBuilder);
	}

	/**
	 */
	public function test()
	{
		$expected = '<select name="userDefinedSelection" id="onofficeSortListSelector"><option value="kaltmiete#ASC"  selected>Kaltmiete (ascending)</option><option value="kaltmiete#DESC" >Kaltmiete (descending)</option><option value="kaufpreis#ASC" >Kaufpreis (ascending)</option><option value="kaufpreis#DESC" >Kaufpreis (descending)</option></select>';
		$pInstance = new SortListDropDownGenerator($this->_pSortListBuilder, $this->_pSortListRenderer, $this->_pDataListViewFactory);
		$this->assertInstanceOf(SortListDropDownGenerator::class, $pInstance);

		$this->assertEquals($expected, $pInstance->generate('asd'));
	}
}