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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataView\DataListView;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 * @covers \onOffice\WPlugin\DataView\DataListView
 *
 */

class TestClassDataListView
	extends WP_UnitTestCase
{
	/** @var DataListView */
	private $_pSubject = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pSubject = new DataListView(123, 'testDataListView');
	}


	/**
	 *
	 */

	public function testAddressFields()
	{
		$this->assertEquals([], $this->_pSubject->getAddressFields());
		$this->_pSubject->setAddressFields(['field1', 'field2']);
		$this->assertEquals(['field1', 'field2'], $this->_pSubject->getAddressFields());
	}


	/**
	 *
	 */

	public function testAvailableOptions()
	{
		$this->assertEquals([], $this->_pSubject->getAvailableOptions());
		$this->_pSubject->setAvailableOptions(['testField1', 'testField2']);
		$this->assertEquals(['testField1', 'testField2'], $this->_pSubject->getAvailableOptions());
	}


	/**
	 *
	 */

	public function testExpose()
	{
		$this->assertEquals('', $this->_pSubject->getExpose());
		$this->_pSubject->setExpose('testExpose');
		$this->assertEquals('testExpose', $this->_pSubject->getExpose());
	}


	/**
	 *
	 */

	public function testFields()
	{
		$this->assertEquals([], $this->_pSubject->getFields());
		$this->_pSubject->setFields(['testField3', 'testField4']);
		$this->assertEquals(['testField3', 'testField4'], $this->_pSubject->getFields());
	}


	/**
	 *
	 */

	public function testFilterId()
	{
		$this->assertEquals(0, $this->_pSubject->getFilterId());
		$this->_pSubject->setFilterId(12);
		$this->assertEquals(12, $this->_pSubject->getFilterId());
	}


	/**
	 *
	 */

	public function testFilterableFields()
	{
		$this->assertEquals([], $this->_pSubject->getFilterableFields());
		$this->_pSubject->setFilterableFields(['testField5', 'testField6']);
		$this->assertEquals(['testField5', 'testField6'], $this->_pSubject->getFilterableFields());
	}


	/**
	 *
	 */

	public function testGeoFields()
	{
		$this->assertEquals([], $this->_pSubject->getGeoFields());
		$this->_pSubject->setGeoFields(['country', 'city']);
		$this->assertEquals(['country', 'city'], $this->_pSubject->getGeoFields());
	}


	/**
	 *
	 */

	public function testHiddenFields()
	{
		$this->assertEquals([], $this->_pSubject->getHiddenFields());
		$this->_pSubject->setHiddenFields(['hidden1', 'hidden2']);
		$this->assertEquals(['hidden1', 'hidden2'], $this->_pSubject->getHiddenFields());
	}


	/**
	 *
	 */

	public function testGetId()
	{
		$this->assertEquals(123, $this->_pSubject->getId());
	}


	/**
	 *
	 */

	public function testListType()
	{
		$this->assertEquals('', $this->_pSubject->getListType());
		$this->_pSubject->setListType('testListType');
		$this->assertEquals('testListType', $this->_pSubject->getListType());
	}


	/**
	 *
	 */

	public function testGetModule()
	{
		$this->assertEquals(onOfficeSDK::MODULE_ESTATE, $this->_pSubject->getModule());
	}


	/**
	 *
	 */

	public function testGetName()
	{
		$this->assertEquals('testDataListView', $this->_pSubject->getName());
	}


	/**
	 *
	 */

	public function testPictureTypes()
	{
		$this->assertEquals([], $this->_pSubject->getPictureTypes());
		$this->_pSubject->setPictureTypes(['Titelbild', 'Lageplan']);
		$this->assertEquals(['Titelbild', 'Lageplan'], $this->_pSubject->getPictureTypes());
	}


	/**
	 *
	 */

	public function testSortByUserValues()
	{
		$this->assertEquals([], $this->_pSubject->getSortByUserValues());
		$this->_pSubject->setSortByUserValues(['kaufpreis', 'wohnflaeche']);
		$this->assertEquals(['kaufpreis', 'wohnflaeche'], $this->_pSubject->getSortByUserValues());
	}


	/**
	 *
	 */

	public function testRandom()
	{
		$this->assertFalse($this->_pSubject->getRandom());
		$this->_pSubject->setRandom(true);
		$this->assertTrue($this->_pSubject->getRandom());
	}


	/**
	 *
	 */

	public function testRecordsPerPage()
	{
		$this->assertEquals(5, $this->_pSubject->getRecordsPerPage());
		$this->_pSubject->setRecordsPerPage(33);
		$this->assertEquals(33, $this->_pSubject->getRecordsPerPage());
	}


	/**
	 *
	 */

	public function testShowStatus()
	{
		$this->assertFalse($this->_pSubject->getShowStatus());
		$this->_pSubject->setShowStatus(true);
		$this->assertTrue($this->_pSubject->getShowStatus());
	}


	/**
	 *
	 */

	public function testSortBy()
	{
		$this->assertEquals('', $this->_pSubject->getSortby());
		$this->_pSubject->setSortby('testField');
		$this->assertEquals('testField', $this->_pSubject->getSortby());
	}


	/**
	 *
	 */

	public function testSortOrder()
	{
		$this->assertEquals('', $this->_pSubject->getSortorder());
		$this->_pSubject->setSortorder('ASC');
		$this->assertEquals('ASC', $this->_pSubject->getSortorder());
	}


	/**
	 *
	 */

	public function testTemplate()
	{
		$this->assertEquals('', $this->_pSubject->getTemplate());
		$this->_pSubject->setTemplate('/path/to/template');
		$this->assertEquals('/path/to/template', $this->_pSubject->getTemplate());
	}

	/**
	 * @covers \onOffice\WPlugin\DataView\DataListView::setAdjustableSorting
	 * @covers \onOffice\WPlugin\DataView\DataListView::isAdjustableSorting
	 */
	public function testAdjustableSorting()
	{
		$this->_pSubject->setAdjustableSorting(true);
		$this->assertTrue($this->_pSubject->isAdjustableSorting());
	}


	/**
	 *
	 */

	public function testShowReferenceStatus()
	{
		$this->assertFalse($this->_pSubject->getShowReferenceStatus());
		$this->_pSubject->setShowReferenceStatus(true);
		$this->assertTrue($this->_pSubject->getShowReferenceStatus());
	}
}
