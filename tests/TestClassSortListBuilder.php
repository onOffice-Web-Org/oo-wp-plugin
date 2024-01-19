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
use onOffice\WPlugin\Controller\SortList\SortListDataModel;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use WP_UnitTestCase;

class TestClassSortListBuilder
	extends WP_UnitTestCase
{
	/** @var DataListView */
	private $_pListView = null;

	/** @var FieldsCollectionBuilderShort */
	private $_pBuilder = null;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pListView = new DataListView(1, 'asd');
		$this->_pListView->setSortByUserValues(['kaltmiete', 'kaufpreis']);
		$this->_pListView->setSortby('kaufpreis');
		$this->_pListView->setSortorder('DESC');
		$this->_pListView->setSortBySetting(1);
		$this->_pListView->setSortByUserDefinedDefault('kaltmiete#ASC');
		$this->_pListView->setSortByUserDefinedDirection(1);

		$this->_pBuilder = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
			->setMethods(['addFieldsAddressEstate','addCustomLabelFieldsEstateFrontend'])
			->setConstructorArgs([new Container])
			->getMock();

		$this->_pBuilder->method('addFieldsAddressEstate')
			->with($this->anything())
			->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
				$pKaltmiete = new Field('kaltmiete', onOfficeSDK::MODULE_ESTATE);
				$pKaltmiete->setLabel('Kaltmiete');
				$pFieldsCollection->addField($pKaltmiete);

				$pKaufpreis = new Field('kaufpreis', onOfficeSDK::MODULE_ESTATE);
				$pKaufpreis->setLabel('Kaufpreis');
				$pFieldsCollection->addField($pKaufpreis);

				return $this->_pBuilder;
			}));
	}

	/**
	 * @covers onOffice\WPlugin\Controller\SortList\SortListBuilder::build
	 * @covers onOffice\WPlugin\Controller\SortList\SortListBuilder::estimateAdjustable
	 * @covers onOffice\WPlugin\Controller\SortList\SortListBuilder::estimateSortbyDefault
	 * @covers onOffice\WPlugin\Controller\SortList\SortListBuilder::estimateSortByValues
	 * @covers onOffice\WPlugin\Controller\SortList\SortListBuilder::estimateAdjustableSelectedSortby
	 * @covers onOffice\WPlugin\Controller\SortList\SortListBuilder::estimateAdjustableSelectedSortorder
	 */
	public function testBuild()
	{
		$pBuilder = new SortListBuilder($this->_pBuilder);
		$pModelAdj = $pBuilder->build($this->_pListView);
		$this->assertInstanceOf(SortListDataModel::class, $pModelAdj);
		$this->assertEquals(['kaltmiete' => 'Kaltmiete', 'kaufpreis' => 'Kaufpreis'], $pModelAdj->getSortByUserValues());
		$this->assertEquals('kaltmiete', $pModelAdj->getSelectedSortby());
		$this->assertEquals('ASC', $pModelAdj->getSelectedSortorder());

		$this->_pListView->setSortBySetting(0);
		$pModelNotAdj = $pBuilder->build($this->_pListView, $this->_pBuilder);
		$this->assertInstanceOf(SortListDataModel::class, $pModelNotAdj);
	}

	/**
	 * @covers onOffice\WPlugin\Controller\SortList\SortListBuilder::build
	 * @covers onOffice\WPlugin\Controller\SortList\SortListBuilder::estimateAdjustable
	 * @covers onOffice\WPlugin\Controller\SortList\SortListBuilder::estimateSortbyDefault
	 * @covers onOffice\WPlugin\Controller\SortList\SortListBuilder::estimateSortByValues
	 * @covers onOffice\WPlugin\Controller\SortList\SortListBuilder::estimateAdjustableSelectedSortby
	 * @covers onOffice\WPlugin\Controller\SortList\SortListBuilder::estimateAdjustableSelectedSortorder
	 */
	public function testBuildWithRequestVars()
	{
		$_GET = ['sortby_id_1' => 'kaufpreis', 'sortorder_id_1' => 'DESC'];
		$pBuilder = new SortListBuilder($this->_pBuilder);
		$pModelAdj = $pBuilder->build($this->_pListView);
		$this->assertInstanceOf(SortListDataModel::class, $pModelAdj);
		$this->assertEquals('kaufpreis', $pModelAdj->getSelectedSortby());
		$this->assertEquals('DESC', $pModelAdj->getSelectedSortorder());
	}

	/**
	 * @covers onOffice\WPlugin\Controller\SortList\SortListBuilder::build
	 * @covers onOffice\WPlugin\Controller\SortList\SortListBuilder::estimateAdjustable
	 * @covers onOffice\WPlugin\Controller\SortList\SortListBuilder::estimateSortbyDefault
	 * @covers onOffice\WPlugin\Controller\SortList\SortListBuilder::estimateSortByValues
	 * @covers onOffice\WPlugin\Controller\SortList\SortListBuilder::estimateAdjustableSelectedSortby
	 * @covers onOffice\WPlugin\Controller\SortList\SortListBuilder::estimateAdjustableSelectedSortorder
	 */
	public function testBuildWithoutSortByDefault()
	{
		$pBuilder = new SortListBuilder($this->_pBuilder);
		$this->_pListView->setSortByUserDefinedDefault('');
		$pModelAdj = $pBuilder->build($this->_pListView);
		$this->assertInstanceOf(SortListDataModel::class, $pModelAdj);
		$this->assertEquals('kaltmiete', $pModelAdj->getSelectedSortby());
		$this->assertEquals('ASC', $pModelAdj->getSelectedSortorder());
	}
}