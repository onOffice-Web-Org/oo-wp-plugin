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
		$this->assertEquals(10, $pDataViewSimilarEstates->getRadius());
		$this->assertEquals(6, $pDataViewSimilarEstates->getRecordsPerPage());
		$this->assertEquals(false, $pDataViewSimilarEstates->getShowPriceOnRequest());
		$this->assertEquals('0', $pDataViewSimilarEstates->getShowReferenceEstate());
		$this->assertEquals(0, $pDataViewSimilarEstates->getFilterId());
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
		$pDataViewSimilarEstates->setShowPriceOnRequest(true);
		$this->assertTrue($pDataViewSimilarEstates->getShowPriceOnRequest());
		$this->assertEquals(['Id' => 'ASC'], $pDataViewSimilarEstates->getSortBy());
		$this->assertNull($pDataViewSimilarEstates->getSortOrder());
		$pDataViewSimilarEstates->setFilterId(10);
		$this->assertEquals($pDataViewSimilarEstates->getFilterId(), 10);
		$pDataViewSimilarEstates->setShowReferenceEstate('0');
		$this->assertEquals($pDataViewSimilarEstates->getShowReferenceEstate(), '0');
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
			'grundstuecksflaeche',
			'nutzflaeche',
		];
		$this->assertEquals($expectedFields, $pDataViewSimilarEstates->getFields());
		$this->assertEquals('SimilarEstates', $pDataViewSimilarEstates->getName());
		$this->assertEquals([], $pDataViewSimilarEstates->getPictureTypes());
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

	/**
	 *
	 */

	public function testGetListFieldsShowPriceOnRequest()
	{
		$pDataViewSimilarEstates = new DataViewSimilarEstates();
		$expectedPriceFields = [
			'kaufpreis',
			'erbpacht',
			'nettokaltmiete',
			'warmmiete',
			'pacht',
			'kaltmiete',
			'miete_pauschal',
			'saisonmiete',
			'wochmietbto',
			'kaufpreis_pro_qm',
			'mietpreis_pro_qm',
			'calculatedPrice',
		];
		$this->assertEquals($expectedPriceFields, $pDataViewSimilarEstates->getListFieldsShowPriceOnRequest());
	}
}
