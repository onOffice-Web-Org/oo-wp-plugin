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
use onOffice\WPlugin\DataView\DataAddressDetailViewHandler;
use onOffice\WPlugin\Types\ImageTypes;
use onOffice\WPlugin\WP\WPOptionWrapperTest;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2024, onOffice(R) GmbH
 *
 */

class TestClassDataAddressDetailViewHandler
	extends WP_UnitTestCase
{
	/** */
	const VALUES_BY_ROW = [
		'template' => '/test/template.php',
		'fields' => [
			'Objektnr_extern',
			'wohnflaeche',
			'kaufpreis',
		],
		'pictures' => [
			ImageTypes::PASSPORT_PHOTO,
		],
		'oo_plugin_fieldconfig_address_translated_labels' => ['field']
	];

	/**
	 *
	 */

	public function testCreateAddressDetailViewByValues()
	{
		$row = self::VALUES_BY_ROW;

		$pDataAddressDetailViewHandler = new DataAddressDetailViewHandler();
		$pDataAddressDetailView = $pDataAddressDetailViewHandler->createAddressDetailViewByValues($row);
		$this->assertEquals($row['template'], $pDataAddressDetailView->getTemplate());
		$this->assertEquals($row['fields'], $pDataAddressDetailView->getFields());
		$this->assertEquals($row['pictures'], $pDataAddressDetailView->getPictureTypes());
		$this->assertEquals($row['oo_plugin_fieldconfig_address_translated_labels'], $pDataAddressDetailView->getCustomLabels());
	}


	/**
	 *
	 */

	public function testCreateEmptyDetailViewByValues()
	{
		$pDataAddressDetailViewHandler = new DataAddressDetailViewHandler();
		$pDataAddressDetailView = $pDataAddressDetailViewHandler->createAddressDetailViewByValues([]);

		$this->assertEquals($pDataAddressDetailView->getTemplate(), '');
		$this->assertEquals($pDataAddressDetailView->getFields(), []);
		$this->assertEquals($pDataAddressDetailView->getPictureTypes(), []);
		$this->assertEquals($pDataAddressDetailView->getName(), 'detail');
		$this->assertEquals($pDataAddressDetailView->getPageId(), 0);
		$this->assertEquals($pDataAddressDetailView->getPageIdsHaveDetailShortCode(), []);
	}


	/**
	 *
	 */

	public function testGetAddressDetailView()
	{
		$pDataAddressDetailView = new DataAddressDetailView();
		$pDataAddressDetailView->setPageId(1337);

		$pWPOptionsWrapper = new WPOptionWrapperTest();
		$pWPOptionsWrapper->addOption(DataAddressDetailViewHandler::DEFAULT_ADDRESS_VIEW_OPTION_KEY, $pDataAddressDetailView);
		$pDataAddressDetailViewHandler = new DataAddressDetailViewHandler($pWPOptionsWrapper);

		$this->assertEquals($pDataAddressDetailView, $pDataAddressDetailViewHandler->getAddressDetailView());

		$pWPOptionsWrapper->deleteOption(DataAddressDetailViewHandler::DEFAULT_ADDRESS_VIEW_OPTION_KEY);

		// if not set, return a new instance
		$this->assertEquals(new DataAddressDetailView(), $pDataAddressDetailViewHandler->getAddressDetailView());
	}


	/**
	 *
	 */

	public function testSaveAddressDetailView()
	{
		$pDataAddressDetailView = new DataAddressDetailView();
		$pDataAddressDetailView->setPageId(1337);
		$optionsKey = DataAddressDetailViewHandler::DEFAULT_ADDRESS_VIEW_OPTION_KEY;
		$pWPOptionsWrapper = new WPOptionWrapperTest();

		$pDataAddressDetailViewHandler = new DataAddressDetailViewHandler($pWPOptionsWrapper);
		$pDataAddressDetailViewHandler->saveAddressDetailView(clone $pDataAddressDetailView);
		$this->assertEquals($pDataAddressDetailView, $pWPOptionsWrapper->getOption($optionsKey));

		// test if overwriting works
		$pDataAddressDetailView->setPageId(1339);
		$pDataAddressDetailViewHandler->saveAddressDetailView(clone $pDataAddressDetailView);
		$this->assertEquals($pDataAddressDetailView, $pWPOptionsWrapper->getOption($optionsKey));
	}
}
