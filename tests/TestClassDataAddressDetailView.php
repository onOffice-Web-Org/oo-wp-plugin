<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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

use onOffice\WPlugin\DataView\DataAddressDetailView;
use WP_UnitTestCase;

class TestClassDataAddressDetailView
	extends WP_UnitTestCase
{
	/** */
	const DEFAULT_FIELDS = [
		'Anrede',
		'Vorname',
		'Name',
		'Zusatz1',
		'Email',
		'Telefon1',
		'Telefax1',
	];

	/**
	 *
	 */
	public function testDefaultValues()
	{
		$pDataAddressDetailView = new DataAddressDetailView();

		$this->assertEquals(self::DEFAULT_FIELDS, $pDataAddressDetailView->getFields());
		$this->assertEquals('detail', $pDataAddressDetailView->getName());
		$this->assertEquals(0, $pDataAddressDetailView->getPageId());
		$this->assertEquals([], $pDataAddressDetailView->getPictureTypes());
		$this->assertEquals('', $pDataAddressDetailView->getTemplate());
		$this->assertEquals([], $pDataAddressDetailView->getPageIdsHaveDetailShortCode());
		$this->assertEquals([], $pDataAddressDetailView->getCustomLabels());
	}

	/**
	 *
	 */
	public function testGetterSetter()
	{
		$pDataAddressDetailView = new DataAddressDetailView();

		$pDataAddressDetailView->setFields(['testaddressfield1', 'testaddressfield2']);
		$this->assertEquals(['testaddressfield1', 'testaddressfield2'],
			$pDataAddressDetailView->getFields());
		$pDataAddressDetailView->setFields(['testfield1', 'testfield2']);
		$this->assertEquals(['testfield1', 'testfield2'], $pDataAddressDetailView->getFields());
		$pDataAddressDetailView->setPageId(12);
		$this->assertEquals(12, $pDataAddressDetailView->getPageId());
		$pDataAddressDetailView->setPictureTypes(['testpicturetype1', 'testpicturetype2']);
		$this->assertEquals(['testpicturetype1', 'testpicturetype2'],
			$pDataAddressDetailView->getPictureTypes());
		$pDataAddressDetailView->setTemplate('/test/template1.test');
		$this->assertEquals('/test/template1.test', $pDataAddressDetailView->getTemplate());

		$pDataAddressDetailView->addToPageIdsHaveDetailShortCode(14);
		$this->assertEquals([14 => 14], $pDataAddressDetailView->getPageIdsHaveDetailShortCode());
		$pDataAddressDetailView->removeFromPageIdsHaveDetailShortCode(14);
		$this->assertEquals([], $pDataAddressDetailView->getPageIdsHaveDetailShortCode());
		$pDataAddressDetailView->setCustomLabels(['field1']);
		$this->assertEquals(['field1'], $pDataAddressDetailView->getCustomLabels());
		$pDataAddressDetailView->setShowPriceOnRequest(1);
		$this->assertEquals(1, $pDataAddressDetailView->getShowPriceOnRequest());
		$pDataAddressDetailView->setShowEstateMap(1);
		$this->assertEquals(1, $pDataAddressDetailView->getShowEstateMap());
		$pDataAddressDetailView->setShowReferenceEstates('');
	}
}
