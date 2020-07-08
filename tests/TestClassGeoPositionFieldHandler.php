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

namespace onOffice\tests;

use LogicException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\GeoPositionFieldHandler;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigGeoFields;
use onOffice\WPlugin\Record\RecordManagerFactory;
use onOffice\WPlugin\Record\RecordManagerRead;
use stdClass;
use WP_UnitTestCase;

/**
 *
 */
class TestClassGeoPositionFieldHandler
	extends WP_UnitTestCase
{
	/** @var stdClass[] */
	private $_record = null;

	/** @var RecordManagerFactory */
	private $_pRecordManagerFactory = null;

	/**
	 * @before
	 */
	public function prepare()
	{
		$pInputModelDBFactoryConfigGeoFields = new InputModelDBFactoryConfigGeoFields(onOfficeSDK::MODULE_ESTATE);
		$fields = $pInputModelDBFactoryConfigGeoFields->getBooleanFields();
		$values = array_fill(0, count($fields), '1');
		$valuesFull = array_combine($fields, $values);
		$pRecord = (object)$valuesFull;
		$this->_record = [$pRecord];
		$this->_pRecordManagerFactory = $this->getMockBuilder(RecordManagerFactory::class)
			->setMethods(['create'])
			->getMock();
		$this->_pRecordManagerFactory->method('create')
			->with(onOfficeSDK::MODULE_ESTATE, RecordManagerFactory::ACTION_READ, $this->anything())
			->willReturn($this->getRecordManagerMock());
	}

	public function testGetActiveFieldsRespectingOrder()
	{
		$this->_record[0]->geo_order = 'street,country,radius,zip,city';

		$pView = new DataListView(3, 'test');
		$pGeoPositionFieldHandler = new GeoPositionFieldHandler($this->_pRecordManagerFactory);
		$pGeoPositionFieldHandler->readValues($pView);
		$activeFields = $pGeoPositionFieldHandler->getActiveFields();
		$this->assertCount(5, $activeFields);
		// same order is important here
		$this->assertEquals(['street', 'country', 'radius', 'zip', 'city'], array_values($activeFields));
	}

	/**
	 * @return GeoPositionFieldHandler
	 */
	public function testGetActiveFieldsWithValue(): GeoPositionFieldHandler
	{
		$this->_record[0]->geo_order = 'street,country,radius,zip,city';

		$pView = new DataListView(3, 'test');
		$pGeoPositionFieldHandler = new GeoPositionFieldHandler($this->_pRecordManagerFactory);
		$pGeoPositionFieldHandler->readValues($pView);

		$this->assertCount(5, $pGeoPositionFieldHandler->getActiveFieldsWithValue());
		$this->assertEquals([
			'country' => null,
			'zip' => null,
			'street' => null,
			'radius' => 10,
			'city' => null,
		], $pGeoPositionFieldHandler->getActiveFieldsWithValue());

		return $pGeoPositionFieldHandler;
	}

	public function testGetRadiusValue()
	{
		$this->_record[0]->radius = 15;
		$pView = new DataListView(3, 'test');
		$pGeoPositionFieldHandler = new GeoPositionFieldHandler($this->_pRecordManagerFactory);
		$pGeoPositionFieldHandler->readValues($pView);
		$this->assertEquals(15, $pGeoPositionFieldHandler->getRadiusValue());
	}

	public function testGetRadiusValueIfNoneGivenInBackend()
	{
		$this->_record[0]->radius = 0;
		$pView = new DataListView(3, 'test');
		$pGeoPositionFieldHandler = new GeoPositionFieldHandler($this->_pRecordManagerFactory);
		$pGeoPositionFieldHandler->readValues($pView);
		$this->assertEquals(10, $pGeoPositionFieldHandler->getRadiusValue());
	}

	public function testGetGeoFieldsOrdered()
	{
		$this->_record[0]->geo_order = 'country,street,radius,zip,city';

		$pView = new DataListView(3, 'test');
		$pGeoPositionFieldHandler = new GeoPositionFieldHandler($this->_pRecordManagerFactory);
		$pGeoPositionFieldHandler->readValues($pView);

		$this->assertEquals(['country', 'street', 'radius', 'zip', 'city'],
			$pGeoPositionFieldHandler->getGeoFieldsOrdered());
	}

	public function testGetGeoFieldsOrderedEmptyOrder()
	{
		// extra case
		$this->_record[0]->geo_order = '';
		$pView = new DataListView(3, 'test');
		$pGeoPositionFieldHandler = new GeoPositionFieldHandler($this->_pRecordManagerFactory);
		$pGeoPositionFieldHandler->readValues($pView);
		$this->assertEquals([], $pGeoPositionFieldHandler->getGeoFieldsOrdered());
	}

	public function testGetGeoFieldsLogicException()
	{
		// radius missing
		$this->_record[0]->geo_order = 'country,street,zip,city';

		$pView = new DataListView(3, 'test');
		$pGeoPositionFieldHandler = new GeoPositionFieldHandler($this->_pRecordManagerFactory);
		$pGeoPositionFieldHandler->readValues($pView);
		$this->expectException(LogicException::class);
		$pGeoPositionFieldHandler->getGeoFieldsOrdered();
	}

	/**
	 * @depends testGetActiveFieldsWithValue
	 * @param GeoPositionFieldHandler $pGeoPositionFieldHandler
	 */
	public function testDefaultValues(GeoPositionFieldHandler $pGeoPositionFieldHandler)
	{
		$pView = new DataListView(0, 'test');
		$pGeoPositionFieldHandler->readValues($pView);
		$this->assertEquals([
			'radius_active' => 'radius',
			'zip_active' => 'zip',
		], $pGeoPositionFieldHandler->getActiveFields());
	}


	/**
	 * @return RecordManagerRead
	 */
	private function getRecordManagerMock(): RecordManagerRead
	{
		$pRecordManager = $this->getMockBuilder(RecordManagerRead::class)
			->setMethods(['getMainTable', 'getIdColumnMain', 'getRecords', 'getRowByName', 'addWhere'])
			->getMock();
		$pRecordManager->method('getMainTable')->willReturn('oo_plugin_listviews');
		$pRecordManager->method('getIdColumnMain')->willReturn('listview_id');
		$pRecordManager->method('getRecords')->will($this->onConsecutiveCalls($this->_record, []));

		return $pRecordManager;
	}
}
