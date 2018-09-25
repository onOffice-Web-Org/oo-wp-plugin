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

use onOffice\WPlugin\DataView\DataViewSimilarEstates;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassDataViewSimilarEstates
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testDefaultValues()
	{
		$pDataViewSimilarEstates = new DataViewSimilarEstates();
		$this->assertEquals(false, $pDataViewSimilarEstates->getSameEstateKind());
		$this->assertEquals(false, $pDataViewSimilarEstates->getSameMarketingMethod());
		$this->assertEquals(false, $pDataViewSimilarEstates->getSamePostalCode());
		$this->assertEquals(10, $pDataViewSimilarEstates->getRadius());
		$this->assertEquals(5, $pDataViewSimilarEstates->getAmount());
	}


	/**
	 *
	 */

	public function testGetterSetter()
	{
		$pDataViewSimilarEstates = new DataViewSimilarEstates();
		$pDataViewSimilarEstates->setAmount(17);
		$this->assertEquals(17, $pDataViewSimilarEstates->getAmount());
		$pDataViewSimilarEstates->setRadius(23);
		$this->assertEquals(23, $pDataViewSimilarEstates->getRadius());
		$pDataViewSimilarEstates->setSameEstateKind(true);
		$this->assertTrue($pDataViewSimilarEstates->getSameEstateKind());
		$pDataViewSimilarEstates->setSameMarketingMethod(true);
		$this->assertTrue($pDataViewSimilarEstates->getSameMarketingMethod());
		$pDataViewSimilarEstates->setSamePostalCode(true);
		$this->assertTrue($pDataViewSimilarEstates->getSamePostalCode());
	}
}
