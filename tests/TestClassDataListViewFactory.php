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

use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataListViewFactory;
use onOffice\WPlugin\DataView\DataViewFilterableFields;
use WP_UnitTestCase;

/**
 *
 */

class TestClassDataListViewFactory
	extends WP_UnitTestCase
{
	/** @var DataListViewFactory */
	private $_pSubject = null;

	/** @var array */
	private $_baseRow = [
		'listview_id' => 13,
		'name' => 'testView',
		'expose' => 'testExpose',
		'fields' => ['field1', 'field2'],
		'filterId' => 133,
		'list_type' => 'testtype',
		'pictures' => ['Photo', 'Test'],
		'show_status' => 1,
		'sortby' => 'field2',
		'sortorder' => 'DESC',
		'recordsPerPage' => 100,
		'template' => 'testtemplate',
		'random' => 1,
		'filterable' => ['field1', 'field3'],
		'hidden' => ['field4', 'field3'],
		'highlighted' => ['field5', 'field6'],
		'availableOptions' => ['field2'],
		'sortBySetting' => 1,
		'sortByUserDefinedDefault' => 'kaufpreis',
		'sortByUserDefinedDirection' => 1,
		'sortbyuservalues' => ['kaufpreis,anzahl_zimmer'],
		'radius' => '200',
		'show_reference_estate' => '1',
		'show_price_on_request' => true,
		'show_map' => '1',
		'convertTextToSelectForCityField' => ['ort'],
		'markedPropertiesSort' => 'neu,top_angebot,no_marker,kauf,miete,reserviert,referenz',
		'sortByTags' => 'kaufpreis',
		'sortByTagsDirection' => 'ASC'
	];

	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pSubject = new DataListViewFactory();
	}


	/**
	 *
	 */

	public function testCreateListViewByRow()
	{
		$pDataListView = $this->_pSubject->createListViewByRow($this->_baseRow);
		$this->assertInstanceOf(DataViewFilterableFields::class, $pDataListView);
		$this->assertInstanceOf(DataListView::class, $pDataListView);
		$this->assertEquals($this->_baseRow['listview_id'], $pDataListView->getId());
		$this->assertEquals($this->_baseRow['name'], $pDataListView->getName());
		$this->assertEquals($this->_baseRow['expose'], $pDataListView->getExpose());
		$this->assertEquals($this->_baseRow['fields'], $pDataListView->getFields());
		$this->assertEquals($this->_baseRow['filterId'], $pDataListView->getFilterId());
		$this->assertEquals($this->_baseRow['list_type'], $pDataListView->getListType());
		$this->assertEquals($this->_baseRow['pictures'], $pDataListView->getPictureTypes());
		$this->assertEquals($this->_baseRow['show_status'], $pDataListView->getShowStatus());
		$this->assertEquals($this->_baseRow['sortby'], $pDataListView->getSortBy());
		$this->assertEquals($this->_baseRow['sortorder'], $pDataListView->getSortOrder());
		$this->assertEquals($this->_baseRow['recordsPerPage'], $pDataListView->getRecordsPerPage());
		$this->assertEquals($this->_baseRow['template'], $pDataListView->getTemplate());
		$this->assertEquals($this->_baseRow['random'], $pDataListView->getRandom());
		$this->assertEquals($this->_baseRow['filterable'], $pDataListView->getFilterableFields());
		$this->assertEquals($this->_baseRow['hidden'], $pDataListView->getHiddenFields());
		$this->assertEquals($this->_baseRow['highlighted'], $pDataListView->getHighlightedFields());
		$this->assertEquals($this->_baseRow['availableOptions'], $pDataListView->getAvailableOptions());
		$this->assertEquals($this->_baseRow['sortBySetting'], $pDataListView->getSortBySetting());
		$this->assertEquals($this->_baseRow['sortByUserDefinedDefault'], $pDataListView->getSortByUserDefinedDefault());
		$this->assertEquals($this->_baseRow['sortByUserDefinedDirection'], $pDataListView->getSortByUserDefinedDirection());
		$this->assertEquals($this->_baseRow['sortbyuservalues'], $pDataListView->getSortByUserValues());
		$this->assertEquals($this->_baseRow['show_reference_estate'], $pDataListView->getShowReferenceEstate());
		$this->assertEquals($this->_baseRow['show_price_on_request'], $pDataListView->getShowPriceOnRequest());
		$this->assertEquals($this->_baseRow['show_map'], $pDataListView->getShowMap());
		$this->assertEquals($this->_baseRow['convertTextToSelectForCityField'], $pDataListView->getConvertTextToSelectForCityField());
		$this->assertEquals($this->_baseRow['markedPropertiesSort'], $pDataListView->getMarkedPropertiesSort());
		$this->assertEquals($this->_baseRow['sortByTags'], $pDataListView->getSortByTags());
		$this->assertEquals($this->_baseRow['sortByTagsDirection'], $pDataListView->getSortByTagsDirection());

		$pDataListView->getFilterableFields();
	}
}
