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

use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\Types\ImageTypes;
use onOffice\WPlugin\Types\MovieLinkTypes;
use onOffice\WPlugin\WP\WPOptionWrapperTest;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassDataDetailViewHandler
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
			ImageTypes::TITLE,
			ImageTypes::PHOTO,
		],
		'expose' => 'testexposetype',
		'addressfields' => ['Vorname', 'Name'],
		'access-control' => true,
		'movielinks' => MovieLinkTypes::MOVIE_LINKS_PLAYER
	];


	/**
	 *
	 */

	public function testCreateDetailViewByValues()
	{
		$row = self::VALUES_BY_ROW;

		$pDataDetailViewHandler = new DataDetailViewHandler();
		$pDataDetailview = $pDataDetailViewHandler->createDetailViewByValues($row);
		$this->assertEquals($row['template'], $pDataDetailview->getTemplate());
		$this->assertEquals($row['fields'], $pDataDetailview->getFields());
		$this->assertEquals($row['pictures'], $pDataDetailview->getPictureTypes());
		$this->assertEquals($row['expose'], $pDataDetailview->getExpose());
		$this->assertEquals($row['addressfields'], $pDataDetailview->getAddressFields());
		$this->assertEquals($row['movielinks'], $pDataDetailview->getMovieLinks());
		$this->assertEquals($row['access-control'], $pDataDetailview->hasDetailView());
	}


	/**
	 *
	 */

	public function testCreateEmptyDetailViewByValues()
	{
		$pDataDetailViewHandler = new DataDetailViewHandler();
		$pDataDetailview = $pDataDetailViewHandler->createDetailViewByValues([]);

		$this->assertEquals($pDataDetailview->getTemplate(), '');
		$this->assertEquals($pDataDetailview->getFields(), []);
		$this->assertEquals($pDataDetailview->getPictureTypes(), []);
		$this->assertEquals($pDataDetailview->getExpose(), '');
		$this->assertEquals($pDataDetailview->getAddressFields(), []);
		$this->assertEquals($pDataDetailview->hasDetailView(), false);
		$this->assertEquals($pDataDetailview->getMovieLinks(), MovieLinkTypes::MOVIE_LINKS_NONE);
		$this->assertEquals($pDataDetailview->getName(), 'detail');
		$this->assertEquals($pDataDetailview->getPageId(), 0);
	}


	/**
	 *
	 */

	public function testGetDetailView()
	{
		$pDataDetailView = new DataDetailView();
		$pDataDetailView->setPageId(1337);

		$pWPOptionsWrapper = new WPOptionWrapperTest();
		$pWPOptionsWrapper->addOption(DataDetailViewHandler::DEFAULT_VIEW_OPTION_KEY, $pDataDetailView);
		$pDataDetailViewHandler = new DataDetailViewHandler($pWPOptionsWrapper);

		$this->assertEquals($pDataDetailView, $pDataDetailViewHandler->getDetailView());

		$pWPOptionsWrapper->deleteOption(DataDetailViewHandler::DEFAULT_VIEW_OPTION_KEY);

		// if not set, return a new instance
		$this->assertEquals(new DataDetailView(), $pDataDetailViewHandler->getDetailView());
	}


	/**
	 *
	 */

	public function testSaveDetailView()
	{
		$pDataDetailView = new DataDetailView();
		$pDataDetailView->setPageId(1337);
		$optionsKey = DataDetailViewHandler::DEFAULT_VIEW_OPTION_KEY;
		$pWPOptionsWrapper = new WPOptionWrapperTest();

		$pDataDetailViewHandler = new DataDetailViewHandler($pWPOptionsWrapper);
		$pDataDetailViewHandler->saveDetailView(clone $pDataDetailView);
		$this->assertEquals($pDataDetailView, $pWPOptionsWrapper->getOption($optionsKey));

		// test if overwriting works
		$pDataDetailView->setPageId(1339);
		$pDataDetailViewHandler->saveDetailView(clone $pDataDetailView);
		$this->assertEquals($pDataDetailView, $pWPOptionsWrapper->getOption($optionsKey));
	}
}
