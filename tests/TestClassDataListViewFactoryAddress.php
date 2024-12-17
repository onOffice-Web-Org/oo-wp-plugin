<?php


/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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

use onOffice\WPlugin\DataView\DataListViewFactoryAddress;
use onOffice\WPlugin\Record\RecordManagerReadListViewAddress;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 * @covers \onOffice\WPlugin\DataView\DataListViewFactoryAddress
 *
 */

class TestClassDataListViewFactoryAddress
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testRecordManagerInstance()
	{
		$pFactory = new DataListViewFactoryAddress();
		$pRecordManagerRead = $pFactory->getRecordManagerRead();
		$recordManagerType = RecordManagerReadListViewAddress::class;
		$this->assertInstanceOf($recordManagerType, $pRecordManagerRead);
	}


	/**
	 *
	 */

	public function testCreateListViewByRow()
	{
		$pFactory = new DataListViewFactoryAddress();
		$row = $this->getTestRow();

		$pDataListViewAddress = $pFactory->createListViewByRow($row);
		$this->assertEquals($row['listview_address_id'], $pDataListViewAddress->getId());
		$this->assertEquals($row['name'], $pDataListViewAddress->getName());
		$this->assertEquals($row['fields'], $pDataListViewAddress->getFields());
		$this->assertEquals($row['filterId'], $pDataListViewAddress->getFilterId());
		$this->assertEquals($row['recordsPerPage'], $pDataListViewAddress->getRecordsPerPage());
		$this->assertEquals($row['showPhoto'], $pDataListViewAddress->getShowPhoto());
		$this->assertEquals($row['sortby'], $pDataListViewAddress->getSortby());
		$this->assertEquals($row['sortorder'], $pDataListViewAddress->getSortorder());
		$this->assertEquals($row['template'], $pDataListViewAddress->getTemplate());
		$this->assertEquals($row['filterable'], $pDataListViewAddress->getFilterableFields());
		$this->assertEquals($row['hidden'], $pDataListViewAddress->getHiddenFields());
		$this->assertEquals($row['convertInputTextToSelectForField'], $pDataListViewAddress->getConvertInputTextToSelectForField());
		$this->assertEquals($row['bildWebseite'], $pDataListViewAddress->getBildWebseite());
		$this->assertEquals($row['show_map'], $pDataListViewAddress->getShowMap());
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getTestRow(): array
	{
		$row = [
			'listview_address_id' => 1234,
			'name' => 'testViewAsdfg23',
			'fields' => ['field1', 'field2', 'field25'],
			'filterable' => ['field2', 'field25'],
			'hidden' => ['field25'],
			'filterId' => 99,
			'recordsPerPage' => 37,
			'showPhoto' => true,
			'sortby' => 'field25',
			'sortorder' => 'DESC',
			'template' => 'testtemplate',
			'convertInputTextToSelectForField' => ['Ort'],
			'bildWebseite' => true,
			'show_map' => '1',
		];

		return $row;
	}
}
