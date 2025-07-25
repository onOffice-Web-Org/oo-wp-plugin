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

use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\DataView\DataViewSimilarEstates;
use onOffice\WPlugin\Installer\DatabaseChanges;
use onOffice\WPlugin\Types\ImageTypes;
use onOffice\WPlugin\Utility\__String;
use onOffice\WPlugin\WP\WPOptionWrapperTest;
use onOffice\WPlugin\DataView\DataSimilarView;
use WP_UnitTestCase;
use function add_filter;
use function get_option;
use function update_option;
use function remove_filter;
use wpdb;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 * @covers onOffice\WPlugin\Installer\DatabaseChanges
 *
 * Please note $wpdb won't create any tables in testing mode
 *
 */

class TestClassDatabaseChanges
	extends WP_UnitTestCase
{
	/** amount of tables created */
	const NUM_NEW_TABLES = 9;

	/** @var string[] */
	private $_createQueries = [];

	/** @var string[] */
	private $_dropQueries = [];

	/** @var WPOptionWrapperTest */
	private $_pWpOption;

	/** @var wpdb */
	private $_pWPDBMock = null;

	/** @var DatabaseChanges */
	private $_pDbChanges;

	/** @var string[] */
	private $_fields = [
		'Field 1',
		'Field 2',
		'Field 3'
	];

	/**
	 * @before
	 */
	public function prepare()
	{
		global $wpdb;
		$dataDetailView = new DataDetailView();
		$dataDetailView->setAddressFields(['Vorname', 'Name', 'defaultemail']);
		$this->_pWpOption = new WPOptionWrapperTest();
		$this->_pWpOption->addOption('onoffice-default-view', $dataDetailView);
		$this->_pDbChanges = new DatabaseChanges($this->_pWpOption, $wpdb);

		$dataSimilarViewOptions = new \onOffice\WPlugin\DataView\DataDetailView();
		$dataSimilarViewOptions->name = "onoffice-default-view";
		$dataSimilarViewOptions->setDataDetailViewActive(true);

		$dataViewSimilarEstates = $dataSimilarViewOptions->getDataViewSimilarEstates();
		$dataViewSimilarEstates->setFields($this->_fields);
		$dataViewSimilarEstates->setSameEstateKind(true);
		$dataViewSimilarEstates->setSameMarketingMethod(true);
		$dataViewSimilarEstates->setSamePostalCode(true);
		$dataViewSimilarEstates->setRadius(35);
		$dataViewSimilarEstates->setRecordsPerPage(13);
		$dataViewSimilarEstates->setTemplate('/test/similar/template.php');
		$dataSimilarViewOptions->setDataViewSimilarEstates($dataViewSimilarEstates);
		$dataSimilarViewOptions->addToPageIdsHaveDetailShortCode(8);
		add_option('onoffice-default-view', $dataSimilarViewOptions);
	}

	/**
	 * @covers \onOffice\WPlugin\Installer\DatabaseChanges::install
	 */

	public function testInstall(): array
	{
		add_filter('query', [$this, 'saveCreateQuery'], 1);
		$this->_pDbChanges->install();

		remove_filter('query', [$this, 'saveCreateQuery'], 1);
		$this->assertGreaterThanOrEqual(self::NUM_NEW_TABLES, count($this->_createQueries));

		$dbversion = $this->_pDbChanges->getDbVersion();
		$this->assertEquals(61, $dbversion);
		return $this->_createQueries;
	}

	public function testInstallMigrationsDataSimilarEstates(): array
	{
		$this->_pDbChanges->deinstall();
		add_option('oo_plugin_db_version', '16');
		add_filter('query', [$this, 'saveCreateQuery'], 1);
		$this->_pDbChanges->install();
		remove_filter('query', [$this, 'saveCreateQuery'], 1);

		$pSimilarViewOptions = $this->_pWpOption->getOption('onoffice-similar-estates-settings-view');
		$newEnableSimilarEstates = $pSimilarViewOptions->getDataSimilarViewActive();

		$pNewDataViewSimilarEstates = $pSimilarViewOptions->getDataViewSimilarEstates();
		$newFields = $pNewDataViewSimilarEstates->getFields();
		$newRadius = $pNewDataViewSimilarEstates->getRadius();
		$newSameKind = $pNewDataViewSimilarEstates->getSameEstateKind();
		$newSameMarketingMethod = $pNewDataViewSimilarEstates->getSameMarketingMethod();
		$newSamePostalCode = $pNewDataViewSimilarEstates->getSamePostalCode();
		$newAmount = $pNewDataViewSimilarEstates->getRecordsPerPage();
		$newSimilarEstatesTemplate = $pNewDataViewSimilarEstates->getTemplate();

		$this->assertTrue($newEnableSimilarEstates);
		$this->assertEquals('Field 1', $newFields[0]);
		$this->assertEquals('Field 2', $newFields[1]);
		$this->assertEquals('Field 3', $newFields[2]);
		$this->assertEquals(35, $newRadius);
		$this->assertTrue($newSameKind);
		$this->assertTrue($newSameMarketingMethod);
		$this->assertEquals(35, $newSamePostalCode);
		$this->assertEquals(13, $newAmount);
		$this->assertEquals('/test/similar/template.php', $newSimilarEstatesTemplate);

		return $this->_createQueries;
	}

	/**
	 * @covers \onOffice\WPlugin\Installer\DatabaseChanges::deleteCommentFieldApplicantSearchForm
	 */

	public function testDeleteCommentFieldApplicantSearchForm()
	{
		$this->_pWpOption->addOption('oo_plugin_db_version', '18');
		$formsOutput = [
			(object)[
				'form_id' => '3',
				'name' => 'Applicant Search Form',
				'form_type' => 'applicantsearch',
			]
		];
		$fieldConfigOutput = [
			(object)[
				'form_fieldconfig_id' => '1',
				'form_id' => '3',
				'fieldname' => 'krit_bemerkung_oeffentlich'
			],
			(object)[
				'form_fieldconfig_id' => '2',
				'form_id' => '3',
				'fieldname' => 'message'
			]
		];
		$listViewOutput = [
			[
				'listview_id' => '3',
				'name' => 'Estate List',
				'list_type' => 'default',
			]
		];
		$fieldListViewConfigOutput = [
			(object)[
				'listview_id' => '3',
				'name' => 'Estate List',
				'list_type' => 'preisAufAnfrage',
			]
		];
		$detailPageIds = [[ "ID" => 8 ]];

		$this->_pWPDBMock = $this->getMockBuilder(wpdb::class)
			->setConstructorArgs(['testUser', 'testPassword', 'testDB', 'testHost'])
			->getMock();

		$this->_pWPDBMock->expects($this->exactly(8))
			->method('get_results')
			->willReturnOnConsecutiveCalls($formsOutput, $fieldConfigOutput, $formsOutput, $fieldConfigOutput, $detailPageIds, $listViewOutput, $fieldListViewConfigOutput);

		$this->_pWPDBMock->expects($this->exactly(4))->method('delete')
			->will($this->returnValue(true));

		$this->_pDbChanges = new DatabaseChanges($this->_pWpOption, $this->_pWPDBMock);
		$this->_pDbChanges->install();
	}

	/**
	 * @covers \onOffice\WPlugin\Installer\DatabaseChanges::deleteMessageFieldApplicantSearchForm
	 */

	public function testDeleteMessageFieldApplicantSearchForm()
	{
		$this->_pWpOption->addOption('oo_plugin_db_version', '25');
		$formsOutput = [
			(object)[
				'form_id' => '3',
				'name' => 'Applicant Search Form',
				'form_type' => 'applicantsearch',
			]
		];
		$fieldConfigOutput = [
			(object)[
				'form_fieldconfig_id' => '2',
				'form_id' => '3',
				'fieldname' => 'message'
			]
		];
		$listViewOutput = [
			[
				'listview_id' => '3',
				'name' => 'Estate List',
				'list_type' => 'default',
			]
		];
		$fieldListViewConfigOutput = [
			(object)[
				'listview_id' => '3',
				'name' => 'Estate List',
				'list_type' => 'preisAufAnfrage',
			]
		];
		$detailPageIds = [[ "ID" => 8 ]];

		$this->_pWPDBMock = $this->getMockBuilder(wpdb::class)
			->setConstructorArgs(['testUser', 'testPassword', 'testDB', 'testHost'])
			->getMock();

		$this->_pWPDBMock->expects($this->exactly(6))
			->method('get_results')
			->willReturnOnConsecutiveCalls($formsOutput, $fieldConfigOutput, $detailPageIds, $listViewOutput, $fieldListViewConfigOutput);

		$this->_pWPDBMock->expects($this->once())->method('delete')
			->will($this->returnValue(true));

		$this->_pDbChanges = new DatabaseChanges($this->_pWpOption, $this->_pWPDBMock);
		$this->_pDbChanges->install();
	}


	/**
	 *
	 */
	public function testMaxVersion()
	{
		$this->assertEquals(61, DatabaseChanges::MAX_VERSION);
	}


	/**
	 *
	 * @depends testInstall
	 * @param array $createQueries from testInstall test.
	 *
	 */

	public function testUninstall(array $createQueries)
	{
		add_filter('query', [$this, 'saveDropQuery'], 1);
		$this->_pDbChanges->deinstall();
		remove_filter('query', [$this, 'saveDropQuery'], 1);

		$this->assertGreaterThanOrEqual(self::NUM_NEW_TABLES, count($this->_dropQueries));

		// assert that as many tables have been removed as have been created
		$uniqueCreateQueries = array_unique($createQueries);
		$uniqueDropQueries = array_unique($this->_dropQueries);
		$this->assertEquals(count($uniqueCreateQueries), count($uniqueDropQueries));
		$dbversion = $this->_pWpOption->getOption('oo_plugin_db_version', null);
		$this->assertNull($dbversion);
		$this->assertNull($this->_pDbChanges->getDbVersion());
	}


	/**
	 *
	 * @param string $query
	 * @return string
	 *
	 */

	public function saveCreateQuery(string $query): string
	{
		if (__String::getNew($query)->startsWith('CREATE TABLE')) {
			$this->_createQueries []= $query;
		}

		return $query;
	}


	/**
	 *
	 * @param string $query
	 * @return string
	 *
	 */

	public function saveDropQuery(string $query): string
	{
		if (__String::getNew($query)->startsWith('DROP TABLE')) {
			$this->_dropQueries []= $query;
		}

		return $query;
	}

	/**
	 * @covers \onOffice\WPlugin\Installer\DatabaseChanges::updateShowPriceOnRequestOptionForSimilarView
	 */

	public function testUpdateShowPriceOnRequestOptionForSimilarView(): array
	{
		global $wpdb;
		$dataDetailView = new DataSimilarView();
		$data = $dataDetailView->getFields();
		$data[] = 'preisAufAnfrage';
		$this->_pDbChanges = new DatabaseChanges($this->_pWpOption, $wpdb);

		$dataSimilarViewOptions = new \onOffice\WPlugin\DataView\DataSimilarView();
		$dataSimilarViewOptions->name = "onoffice-default-view";
		$dataSimilarViewOptions->setFields($data);
		add_option('onoffice-similar-estates-settings-view', $dataSimilarViewOptions);

		$this->_pDbChanges->deinstall();
		add_option('oo_plugin_db_version', '38');
		add_filter('query', [$this, 'saveCreateQuery'], 1);
		$this->_pDbChanges->install();
		remove_filter('query', [$this, 'saveCreateQuery'], 1);

		$pSimilarViewOptions = $this->_pWpOption->getOption('onoffice-similar-estates-settings-view');
		$pDataViewSimilarEstates = $pSimilarViewOptions->getDataViewSimilarEstates();

		$this->assertNotContains('preisAufAnfrage', $pDataViewSimilarEstates->getFields());

		return $this->_createQueries;
	}

	/**
	 * @covers \onOffice\WPlugin\Installer\DatabaseChanges::updateShowPriceOnRequestOptionForDetailView
	 */

	public function testUpdateShowPriceOnRequestOptionForDetailView(): array
	{
		global $wpdb;
		$dataDetailView = new DataDetailView();
		$data = $dataDetailView->getFields();
		$data[] = 'preisAufAnfrage';
		$this->_pDbChanges = new DatabaseChanges($this->_pWpOption, $wpdb);

		$dataDetailViewOptions = new \onOffice\WPlugin\DataView\DataDetailView();
		$dataDetailViewOptions->name = "onoffice-default-view";
		$dataDetailViewOptions->setFields($data);
		update_option('onoffice-default-view', $dataDetailViewOptions);

		$this->_pDbChanges->deinstall();
		add_option('oo_plugin_db_version', '38');
		add_filter('query', [$this, 'saveCreateQuery'], 1);
		$this->_pDbChanges->install();
		remove_filter('query', [$this, 'saveCreateQuery'], 1);

		$pDetailViewOptions = $this->_pWpOption->getOption('onoffice-default-view');

		$this->assertNotContains('preisAufAnfrage', $pDetailViewOptions->getFields());
		$this->assertEquals(true, $pDetailViewOptions->getShowPriceOnRequest());

		return $this->_createQueries;
	}

	/**
	 * @covers \onOffice\WPlugin\Installer\DatabaseChanges::updateDefaultPictureTypesForSimilarEstate
	 */

	public function testUpdateDefaultPictureTypesForSimilarEstate(): array
	{
		global $wpdb;
		$this->_pDbChanges = new DatabaseChanges($this->_pWpOption, $wpdb);

		$dataSimilarViewOptions = new \onOffice\WPlugin\DataView\DataSimilarView();
		$dataSimilarViewOptions->name = "onoffice-default-view";
		add_option('onoffice-similar-estates-settings-view', $dataSimilarViewOptions);

		$this->_pDbChanges->deinstall();
		add_option('oo_plugin_db_version', '40');
		add_filter('query', [$this, 'saveCreateQuery'], 1);
		$this->_pDbChanges->install();
		remove_filter('query', [$this, 'saveCreateQuery'], 1);

		$pSimilarViewOptions = $this->_pWpOption->getOption('onoffice-similar-estates-settings-view');
		$pDataViewSimilarEstates = $pSimilarViewOptions->getDataViewSimilarEstates();

		$this->assertEquals([ImageTypes::TITLE], $pDataViewSimilarEstates->getPictureTypes());

		return $this->_createQueries;
	}

	/**
	 * @covers \onOffice\WPlugin\Installer\DatabaseChanges::updatePriceFieldsOptionForSimilarEstate
	 */
	public function testUpdatePriceFieldsOptionForSimilarEstate()
	{
		global $wpdb;
		$dataViewSimilarEstates = new DataViewSimilarEstates();
		$listFieldsShowPriceOnRequest = $dataViewSimilarEstates->getListFieldsShowPriceOnRequest();

		$this->_pDbChanges = new DatabaseChanges($this->_pWpOption, $wpdb);

		$dataSimilarViewOptions = new \onOffice\WPlugin\DataView\DataSimilarView();
		$dataSimilarViewOptions->name = "onoffice-similar-estates-settings-view";
		$dataSimilarViewOptions->getDataViewSimilarEstates()->setListFieldsShowPriceOnRequest($listFieldsShowPriceOnRequest);
		update_option('onoffice-similar-estates-settings-view', $dataSimilarViewOptions);

		$this->_pDbChanges->deinstall();
		add_option('oo_plugin_db_version', '44');
		add_filter('query', [$this, 'saveCreateQuery'], 1);
		$this->_pDbChanges->install();
		remove_filter('query', [$this, 'saveCreateQuery'], 1);

		$pSimilarEstatesOptions = get_option('onoffice-similar-estates-settings-view');
		$pDataViewSimilarEstates = $pSimilarEstatesOptions->getDataViewSimilarEstates();

		$this->assertEquals($listFieldsShowPriceOnRequest, $pDataViewSimilarEstates->getListFieldsShowPriceOnRequest());

		return $this->_createQueries;
	}

	/**
	 * @covers \onOffice\WPlugin\Installer\DatabaseChanges::updatePriceFieldsOptionDetailView
	 */
	public function testUpdatePriceFieldsOptionForDetailView()
	{
		global $wpdb;
		$dataDetailView = new DataDetailView();
		$listFieldsShowPriceOnRequest = $dataDetailView->getListFieldsShowPriceOnRequest();

		$this->_pDbChanges = new DatabaseChanges($this->_pWpOption, $wpdb);

		$dataDetailViewOptions = new \onOffice\WPlugin\DataView\DataDetailView();
		$dataDetailViewOptions->name = "onoffice-default-view";
		$dataDetailViewOptions->setListFieldsShowPriceOnRequest($listFieldsShowPriceOnRequest);
		update_option('onoffice-default-view', $dataDetailViewOptions);

		$this->_pDbChanges->deinstall();
		add_option('oo_plugin_db_version', '44');
		add_filter('query', [$this, 'saveCreateQuery'], 1);
		$this->_pDbChanges->install();
		remove_filter('query', [$this, 'saveCreateQuery'], 1);

		$pDetailViewOptions = get_option('onoffice-default-view');

		$this->assertEquals($listFieldsShowPriceOnRequest, $pDetailViewOptions->getListFieldsShowPriceOnRequest());

		return $this->_createQueries;
	}
}
