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

use onOffice\WPlugin\Controller\SortList\SortListDataModel;
use WP_UnitTestCase;

class TestClassSortListDataModel
	extends WP_UnitTestCase
{
	/** @var SortListDataModel */
	private $_pModel = null;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pModel = new SortListDataModel;
	}

	/**
	 * @covers onOffice\WPlugin\Controller\SortList\SortListDataModel::setAdjustableSorting
	 * @covers onOffice\WPlugin\Controller\SortList\SortListDataModel::isAdjustableSorting
	 */
	public function testAdjustable()
	{
		$this->_pModel->setAdjustableSorting(true);
		$this->assertTrue($this->_pModel->isAdjustableSorting());
	}

	/**
	 * @covers onOffice\WPlugin\Controller\SortList\SortListDataModel::setSelectedSortby
	 * @covers onOffice\WPlugin\Controller\SortList\SortListDataModel::getSelectedSortby
	 */
	public function testSelectedSortby()
	{
		$this->_pModel->setSelectedSortby('asd');
		$this->assertEquals('asd', $this->_pModel->getSelectedSortby());
	}

	/**
	 * @covers onOffice\WPlugin\Controller\SortList\SortListDataModel::setSelectedSortorder
	 * @covers onOffice\WPlugin\Controller\SortList\SortListDataModel::getSelectedSortorder
	 */
	public function testSelectedSortorder()
	{
		$this->_pModel->setSelectedSortorder('asd');
		$this->assertEquals('asd', $this->_pModel->getSelectedSortorder());
	}

	/**
	 * @covers onOffice\WPlugin\Controller\SortList\SortListDataModel::setSortByUserValues
	 * @covers onOffice\WPlugin\Controller\SortList\SortListDataModel::getSortByUserValues
	 * @covers onOffice\WPlugin\Controller\SortList\SortListDataModel::addSortByUserValues
	 */
	public function testSortByUserValues()
	{
		$this->_pModel->setSortByUserValues(['asd', 'qwer']);
		$this->assertEquals(['asd', 'qwer'], $this->_pModel->getSortByUserValues());

		$this->_pModel->addSortByUserValues('fff');
		$this->assertEquals(['asd', 'qwer', 'fff'], $this->_pModel->getSortByUserValues());
	}

	/**
	 * @covers onOffice\WPlugin\Controller\SortList\SortListDataModel::setSortbyUserDirection
	 * @covers onOffice\WPlugin\Controller\SortList\SortListDataModel::getSortbyUserDirection
	 */
	public function testSortbyUserDirection()
	{
		$this->_pModel->setSortbyUserDirection(1);
		$this->assertEquals(1, $this->_pModel->getSortbyUserDirection());
	}

	/**
	 * @covers onOffice\WPlugin\Controller\SortList\SortListDataModel::setSortbyDefaultValue
	 * @covers onOffice\WPlugin\Controller\SortList\SortListDataModel::getSortbyDefaultValue
	 */
	public function testSortbyDefaultValue()
	{
		$this->_pModel->setSortbyDefaultValue('kaufpreis');
		$this->assertEquals('kaufpreis', $this->_pModel->getSortbyDefaultValue());
	}
}