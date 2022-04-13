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

use onOffice\WPlugin\DataView\DataViewSimilarEstates;
use onOffice\WPlugin\Filter\FilterConfigurationSimilarEstates;
use onOffice\WPlugin\Filter\GeoSearchBuilderSimilarEstates;
use onOffice\WPlugin\Types\GeoCoordinates;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassGeoSearchBuilderSimilarEstates
	extends WP_UnitTestCase
{
	/** @var FilterConfigurationSimilarEstates */
	private $_pFilterConfiguration = null;

	/** @var GeoSearchBuilderSimilarEstates */
	private $_pGeoSearchBuilderSimilarEstates = null;


	/**
	 *
	 */

	public function set_up()
	{
		parent::set_up();

		$pDataViewSimilarEstates = new DataViewSimilarEstates();
		$this->_pFilterConfiguration = new FilterConfigurationSimilarEstates
			($pDataViewSimilarEstates);
		$this->_pGeoSearchBuilderSimilarEstates = new GeoSearchBuilderSimilarEstates
			($this->_pFilterConfiguration);
	}


	/**
	 *
	 * An empty radius shoud output an empty configuration
	 * Empty address and geo-coordinates also result in empty configuration
	 *
	 */

	public function testEmpty()
	{
		$this->getDataViewSimilarEstates()->setRadius(0);
		$resultEmptyRadius = $this->_pGeoSearchBuilderSimilarEstates->buildParameters();
		$this->assertEquals([], $resultEmptyRadius);

		$this->getDataViewSimilarEstates()->setRadius(10);
		$resultEmptyGeoData = $this->_pGeoSearchBuilderSimilarEstates->buildParameters();
		$this->assertEquals([], $resultEmptyGeoData);
	}


	/**
	 *
	 */

	public function testByAddressWithoutCountry()
	{
		$this->setAddressDataWithoutCountry();

		$expectedResult = [
			'street' => 'Große Eschenheimer Straße',
			'zip' => '60313',
			'radius' => 10,
		];

		$result = $this->_pGeoSearchBuilderSimilarEstates->buildParameters();
		$this->assertEquals($expectedResult, $result);
	}


	/**
	 *
	 */

	public function testByAddressWithCountry()
	{
		$this->setAddressDataWithoutCountry();
		$this->_pFilterConfiguration->setCountry('DEU');

		$expectedResult = [
			'street' => 'Große Eschenheimer Straße',
			'zip' => '60313',
			'radius' => 10,
			'country' => 'DEU',
		];

		$result = $this->_pGeoSearchBuilderSimilarEstates->buildParameters();
		$this->assertEquals($expectedResult, $result);
	}


	/**
	 *
	 */

	public function testByGeoCoordinates()
	{
		$this->_pFilterConfiguration->setGeoCoordinates(new GeoCoordinates(50.774734, 6.0839185));
		$result = $this->_pGeoSearchBuilderSimilarEstates->buildParameters();
		$expected = [
			'latitude' => 50.774734,
			'longitude' => 6.0839185,
			'radius' => 10,
		];
		$this->assertEquals($expected, $result);
	}


	/**
	 *
	 */

	private function setAddressDataWithoutCountry()
	{
		$pFilterConfiguration = $this->_pFilterConfiguration;
		$pFilterConfiguration->setCity('Frankfurt');
		$pFilterConfiguration->setPostalCode('60313');
		$pFilterConfiguration->setStreet('Große Eschenheimer Straße');
	}


	/**
	 *
	 * @return DataViewSimilarEstates
	 *
	 */

	private function getDataViewSimilarEstates(): DataViewSimilarEstates
	{
		return $this->_pFilterConfiguration->getDataViewSimilarEstates();
	}
}
