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
use onOffice\WPlugin\Types\GeoCoordinates;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassFilterConfigurationSimilarEstates
	extends WP_UnitTestCase
{
	/** @var DataViewSimilarEstates */
	private $_pDataViewSimilarEstates = null;


	/**
	 *
	 */

	public function setUp()
	{
		parent::set_up();

		$this->_pDataViewSimilarEstates = new DataViewSimilarEstates();
		$this->_pDataViewSimilarEstates->setRecordsPerPage(13);
		$this->_pDataViewSimilarEstates->setRadius(80);
		$this->_pDataViewSimilarEstates->setSameEstateKind(true);
		$this->_pDataViewSimilarEstates->setSameMarketingMethod(true);
		$this->_pDataViewSimilarEstates->setSamePostalCode(true);
	}


	/**
	 *
	 */

	public function testGetterSetter()
	{
		$pFilterConfigurationSimilarEstates = new FilterConfigurationSimilarEstates
			($this->_pDataViewSimilarEstates);

		$pFilterConfigurationSimilarEstates->setCountry('USA');
		$pFilterConfigurationSimilarEstates->setEstateKind('haus');
		$pFilterConfigurationSimilarEstates->setGeoCoordinates
			(new GeoCoordinates(35.0576431, -85.3162938));
		$pFilterConfigurationSimilarEstates->setPostalCode('37402');
		$pFilterConfigurationSimilarEstates->setStreet('Riverfront Pkwy');
		$pFilterConfigurationSimilarEstates->setCity('Chattanooga');
		$pFilterConfigurationSimilarEstates->setMarketingMethod('miete');

		$this->assertEquals(13, $pFilterConfigurationSimilarEstates->getAmount());
		$this->assertEquals(80, $pFilterConfigurationSimilarEstates->getRadius());
		$this->assertEquals($this->_pDataViewSimilarEstates,
			$pFilterConfigurationSimilarEstates->getDataViewSimilarEstates());
		$this->assertEquals(new GeoCoordinates(35.0576431, -85.3162938),
			$pFilterConfigurationSimilarEstates->getGeoCoordinates());
		$this->assertEquals('USA', $pFilterConfigurationSimilarEstates->getCountry());
		$this->assertEquals('haus', $pFilterConfigurationSimilarEstates->getEstateKind());
		$this->assertEquals('37402', $pFilterConfigurationSimilarEstates->getPostalCode());
		$this->assertEquals('Riverfront Pkwy', $pFilterConfigurationSimilarEstates->getStreet());
		$this->assertEquals('Chattanooga', $pFilterConfigurationSimilarEstates->getCity());
		$this->assertEquals('miete', $pFilterConfigurationSimilarEstates->getMarketingMethod());
	}
}
