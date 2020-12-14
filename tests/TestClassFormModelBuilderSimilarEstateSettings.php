<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

use DI\Container;
use DI\ContainerBuilder;
use onOffice\WPlugin\DataView\DataSimilarEstatesSettingsHandler;
use onOffice\WPlugin\DataView\DataSimilarView;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderSimilarEstateSettings;
use onOffice\WPlugin\Model\InputModel\InputModelOptionFactorySimilarView;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\WP\WPOptionWrapperTest;
use WP_UnitTestCase;

class TestClassFormModelBuilderSimilarEstateSettings
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

    /** @var InputModelOptionFactorySimilarView */
    private $_pInputModelSimilarViewFactory ;

    /** @var DataSimilarView */
    private $_pDataSimilarView;

    /**
     * @before
     */
    public function prepare()
    {
        $this->_pInputModelSimilarViewFactory  = new InputModelOptionFactorySimilarView('onoffice');
        $row = self::VALUES_BY_ROW;

        $pWPOptionsWrapper = new WPOptionWrapperTest();
        $pDataSimilarEstatesSettingsHandler = new DataSimilarEstatesSettingsHandler($pWPOptionsWrapper);
        $pDataSimilarEstatesSettingsHandler->createDataSimilarEstatesSettingsByValues($row);
        $this->_pDataSimilarView->setDataSimilarViewActive(true);
        $this->_pDataSimilarView = $pDataSimilarEstatesSettingsHandler->getDataSimilarEstatesSettings();
    }

    /**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderSimilarEstateSettings::GetCheckboxEnableSimilarEstates
	 */
	public function testGetCheckboxEnableSimilarEstates()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderSimilarEstateSettings::class)
			->disableOriginalConstructor()
            ->setMethods(['getValue'])
			->getMock();

        $pInstance->setInputModelSimilarViewFactory($this->_pInputModelSimilarViewFactory);
        $pInstance->method('getValue')->willReturn('1');

		$pInputModelDB = $pInstance->getCheckboxEnableSimilarEstates();
		$this->assertInstanceOf(InputModelDB::class,$pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'checkbox');
	}

}