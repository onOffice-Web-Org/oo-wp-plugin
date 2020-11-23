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

use onOffice\WPlugin\DataView\DataSimilarView;
use onOffice\WPlugin\DataView\DataSimilarEstatesSettingsHandler;
use onOffice\WPlugin\WP\WPOptionWrapperTest;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassDataSimilarEstatesSettingsHandler
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
		'similar_estates_template' => '/test/similar/template.php',
		'same_kind' => true,
		'same_maketing_method' => true,
        'dont_show_archived' => true,
        'dont_show_reference' => true,
		'radius' => 35,
		'amount' => 13,
		'enablesimilarestates' => true,
	];

    /** */
    const VALUES_EMPTY_BY_ROW = [
        'fields' => [],
    ];


	/**
	 *
	 */

	public function testCreateDataSimilarEstatesSettingsByValues()
	{
		$row = self::VALUES_BY_ROW;

        $pWPOptionsWrapper = new WPOptionWrapperTest();
		$pDataSimilarEstatesSettingsHandler = new DataSimilarEstatesSettingsHandler($pWPOptionsWrapper);
		$pDataSimilarView = $pDataSimilarEstatesSettingsHandler->createDataSimilarEstatesSettingsByValues($row);
		$this->assertEquals($row['fields'], $pDataSimilarView->getFields());
        $this->assertEquals($row['enablesimilarestates'], $pDataSimilarView->getDataSimilarViewActive());
        $pDataSimilar = $pDataSimilarView->getDataViewSimilarEstates();

        $this->assertEquals($row['same_kind'], $pDataSimilar->getSameEstateKind());
        $this->assertEquals($row['same_maketing_method'], $pDataSimilar->getSameMarketingMethod());
        $this->assertFalse($pDataSimilar->getSamePostalCode()); // missing -> bool
        $this->assertEquals($row['radius'], $pDataSimilar->getRadius());
        $this->assertEquals($row['amount'], $pDataSimilar->getRecordsPerPage());
	}

    /**
     *
     */

    public function testCreateEmptyDataSimilarEstatesSettingsByValues()
    {
        $row = self::VALUES_EMPTY_BY_ROW;
        $pWPOptionsWrapper = new WPOptionWrapperTest();
        $pDataDetailViewHandler = new DataSimilarEstatesSettingsHandler($pWPOptionsWrapper);
        $pDataSimilarView = $pDataDetailViewHandler->createDataSimilarEstatesSettingsByValues($row);

        $this->assertEquals($pDataSimilarView->getFields(), []);
        $this->assertEquals($pDataSimilarView->getPageId(), 0);
    }

	/**
	 *
	 */

	public function testGetDataSimilarEstatesSettings()
	{
		$pDataSimilarView = new DataSimilarView();
		$pDataSimilarView->setPageId(1337);

		$pWPOptionsWrapper = new WPOptionWrapperTest();
		$pWPOptionsWrapper->addOption(DataSimilarEstatesSettingsHandler::DEFAULT_VIEW_OPTION_KEY, $pDataSimilarView);
		$pDataSimilarEstatesSettingsHandler = new DataSimilarEstatesSettingsHandler($pWPOptionsWrapper);

		$this->assertEquals($pDataSimilarView, $pDataSimilarEstatesSettingsHandler->getDataSimilarEstatesSettings());

		$pWPOptionsWrapper->deleteOption(DataSimilarEstatesSettingsHandler::DEFAULT_VIEW_OPTION_KEY);

		// if not set, return a new instance
		$this->assertEquals(new DataSimilarView(), $pDataSimilarEstatesSettingsHandler->getDataSimilarEstatesSettings());
	}


	/**
	 *
	 */

	public function testSaveDataSimilarEstatesSettings()
	{
		$pDataSimilarView = new DataSimilarView();
		$pDataSimilarView->setPageId(1337);
		$optionsKey = DataSimilarEstatesSettingsHandler::DEFAULT_VIEW_OPTION_KEY;
		$pWPOptionsWrapper = new WPOptionWrapperTest();

		$pDataSimilarEstatesSettingsHandler = new DataSimilarEstatesSettingsHandler($pWPOptionsWrapper);
		$pDataSimilarEstatesSettingsHandler->saveDataSimilarEstatesSettings(clone $pDataSimilarView);
		$this->assertEquals($pDataSimilarView, $pWPOptionsWrapper->getOption($optionsKey));

		// test if overwriting works
		$pDataSimilarView->setPageId(1339);
		$pDataSimilarEstatesSettingsHandler->saveDataSimilarEstatesSettings(clone $pDataSimilarView);
		$this->assertEquals($pDataSimilarView, $pWPOptionsWrapper->getOption($optionsKey));
	}
}
