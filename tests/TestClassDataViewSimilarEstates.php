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
use onOffice\WPlugin\Types\ImageTypes;
use WP_UnitTestCase;

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
		$this->assertEquals(true, $pDataViewSimilarEstates->getSameEstateKind());
		$this->assertEquals(true, $pDataViewSimilarEstates->getSameMarketingMethod());
		$this->assertEquals(false, $pDataViewSimilarEstates->getSamePostalCode());
        $this->assertEquals(false, $pDataViewSimilarEstates->getDontShowArchived());
        $this->assertEquals(false, $pDataViewSimilarEstates->getDontShowReference());
		$this->assertEquals(10, $pDataViewSimilarEstates->getRadius());
		$this->assertEquals(5, $pDataViewSimilarEstates->getRecordsPerPage());
	}


	/**
	 *
	 */

	public function testGetterSetter()
	{
		$pDataViewSimilarEstates = new DataViewSimilarEstates();
		$pDataViewSimilarEstates->setRecordsPerPage(17);
		$this->assertEquals(17, $pDataViewSimilarEstates->getRecordsPerPage());
		$pDataViewSimilarEstates->setRadius(23);
		$this->assertEquals(23, $pDataViewSimilarEstates->getRadius());
		$pDataViewSimilarEstates->setSameEstateKind(true);
		$this->assertTrue($pDataViewSimilarEstates->getSameEstateKind());
		$pDataViewSimilarEstates->setSameMarketingMethod(true);
		$this->assertTrue($pDataViewSimilarEstates->getSameMarketingMethod());
		$pDataViewSimilarEstates->setSamePostalCode(true);
		$this->assertTrue($pDataViewSimilarEstates->getSamePostalCode());
        $pDataViewSimilarEstates->setDontShowArchived(true);
        $this->assertTrue($pDataViewSimilarEstates->getDontShowArchived());
        $pDataViewSimilarEstates->setDontShowReference(true);
        $this->assertTrue($pDataViewSimilarEstates->getDontShowReference());
		$this->assertEquals(['Id' => 'ASC'], $pDataViewSimilarEstates->getSortBy());
		$this->assertNull($pDataViewSimilarEstates->getSortOrder());
		$this->assertNull($pDataViewSimilarEstates->getFilterId());
	}


	/**
	 *
	 */

	public function testOverriddenFields()
	{
		$pDataViewSimilarEstates = new DataViewSimilarEstates();
		$this->assertEquals([], $pDataViewSimilarEstates->getAddressFields());
		$this->assertEquals('', $pDataViewSimilarEstates->getExpose());
		$expectedFields = [
			'Id',
			'objekttitel',
			'objektnr_extern',
			'regionaler_zusatz',
			'kaufpreis',
			'wohnflaeche',
			'anzahl_zimmer',
			'kaltmiete',
			'ort',
			'plz',
            'status2',
			'grundstuecksflaeche',
			'nutzflaeche',
		];
		$this->assertEquals($expectedFields, $pDataViewSimilarEstates->getFields());
		$this->assertEquals('SimilarEstates', $pDataViewSimilarEstates->getName());
		$this->assertEquals([ImageTypes::TITLE], $pDataViewSimilarEstates->getPictureTypes());
		$this->assertEquals('', $pDataViewSimilarEstates->getTemplate());
	}


	/**
	 *
	 */

	public function testRandom()
	{
		$pDataViewSimilarEstates = new DataViewSimilarEstates();
		$this->assertEquals(false,$pDataViewSimilarEstates->getRandom());
	}
}
