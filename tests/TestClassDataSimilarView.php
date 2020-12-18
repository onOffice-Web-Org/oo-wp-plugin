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

use Closure;
use onOffice\WPlugin\DataView\DataSimilarView;
use onOffice\WPlugin\DataView\DataViewSimilarEstates;
use WP_UnitTestCase;

class TestClassDataSimilarView
	extends WP_UnitTestCase
{
	/** */
	const DEFAULT_FIELDS_ESTATE = [
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
		'nutzflaeche'
	];

	/**
	 *
	 */

	public function test__construct()
	{
		$pDataSimilarView = new DataSimilarView();
		$this->assertInstanceOf(DataViewSimilarEstates::class,
			$pDataSimilarView->getDataViewSimilarEstates());
	}

	/**
	 *
	 */
	public function testDefaultValues()
	{
		$pDataSimilarView = new DataSimilarView();
		
		$this->assertEquals(self::DEFAULT_FIELDS_ESTATE, $pDataSimilarView->getFields());
		$this->assertEquals(0, $pDataSimilarView->getPageId());
	}

	/**
	 *
	 */
	public function testEnableSimilarEstates()
	{
		$pDataSimilarView = new DataSimilarView();

		$this->assertFalse($pDataSimilarView->getDataSimilarViewActive());
		$pDataSimilarView->setDataSimilarViewActive(true);
		$this->assertTrue($pDataSimilarView->getDataSimilarViewActive());
	}


}
