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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\LocalFieldsCollectionFactory;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassLocalFieldsCollectionFactory
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testProduceForEstate()
	{
		$pFactory = new LocalFieldsCollectionFactory();
		$pCollection = $pFactory->produceCollection(onOfficeSDK::MODULE_ESTATE);

		$this->assertInstanceOf(FieldsCollection::class, $pCollection);
		$this->assertGreaterThan(0, count($pCollection->getAllFields()));
	}


	/**
	 *
	 */

	public function testProduceForUnknownModule()
	{
		$pFactory = new LocalFieldsCollectionFactory();
		$pCollection = $pFactory->produceCollection('UnknownModule');
		$this->assertInstanceOf(FieldsCollection::class, $pCollection);
		$this->assertEquals([], $pCollection->getAllFields());
	}
}
