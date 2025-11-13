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

declare(strict_types=1);

namespace onOffice\WPlugin\Installer;

use DI\Container;
use DI\ContainerBuilder;
use onOffice\WPlugin\AddressList;
use Exception;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\DataView\DataViewSimilarEstates;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\Template\TemplateCall;
use onOffice\WPlugin\Types\ImageTypes;
use onOffice\WPlugin\DataView\DataSimilarView;
use onOffice\WPlugin\WP\WPOptionWrapperBase;
use onOffice\WPlugin\WP\WPPluginChecker;
use wpdb;
use function dbDelta;
use function esc_sql;
use const ABSPATH;
use onOffice\WPlugin\Record\RecordManagerReadForm;

class DatabaseChanges implements DatabaseChangesInterface
{
	/** @var int */
	const MAX_VERSION = 62;

	/** @var WPOptionWrapperBase */
	private $_pWpOption;

	/** @var wpdb */
	private $_pWPDB;

	/** @var Container */
	private $_pContainer;

	/**
	 * @param WPOptionWrapperBase $pWpOption
	 * @param wpdb $pWPDB
	 *
	 * @throws Exception
	 */
	public function __construct(WPOptionWrapperBase $pWpOption, wpdb $pWPDB)
	{
		$this->_pWpOption = $pWpOption;
		$this->_pWPDB = $pWPDB;
		$pDIContainerBuilder = new ContainerBuilder;
		$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pDIContainerBuilder->build();
	}

	/**
	 *
	 */
	public function install()
	{
		if (get_site_option('oo_plugin_db_version') == DatabaseChanges::MAX_VERSION) {
			return;
		}
		// If you are modifying this, please also make sure to edit the test
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$isNewInstall = false;
		$dbversion = $this->_pWpOption->getOption('oo_plugin_db_version', null) ?? 0;
		if ($dbversion == 0) {
			$isNewInstall = true;
			dbDelta( $this->getCreateQueryCache() );
			$this->setDetailTemplate();

			$this->_pWpOption->addOption( 'oo_plugin_db_version', $dbversion, true );
		}

		dbDelta( $this->getCreateQueryAddressFieldConfig() );
		dbDelta( $this->getCreateQueryContactTypes() );
		dbDelta( $this->getCreateQueryFieldConfig() );
		dbDelta( $this->getCreateQueryFieldConfigAddressCustomsLabels() );
		dbDelta( $this->getCreateQueryFieldConfigAddressTranslatedLabels() );
		dbDelta( $this->getCreateQueryFieldConfigCustomsLabels() );
		dbDelta( $this->getCreateQueryFieldConfigDefaults() );
		dbDelta( $this->getCreateQueryFieldConfigDefaultsValues() );
		dbDelta( $this->getCreateQueryFieldConfigEstateCustomsLabels() );
		dbDelta( $this->getCreateQueryFieldConfigEstateTranslatedLabels() );
		dbDelta( $this->getCreateQueryFieldConfigTranslatedLabels() );
		dbDelta( $this->getCreateQueryForms() );
		dbDelta( $this->getCreateQueryFormActivityConfig() );
		dbDelta( $this->getCreateQueryFormFieldConfig() );
		dbDelta( $this->getCreateQueryFormTaskConfig() );
		dbDelta( $this->getCreateQueryListviews() );
		dbDelta( $this->getCreateQueryListViewContactPerson() );
		dbDelta( $this->getCreateQueryListViewsAddress() );
		dbDelta( $this->getCreateQueryPictureTypes() );
		dbDelta( $this->getCreateQuerySortByUserValues() );
		dbDelta( $this->addColumnsForHighlights() );
		dbDelta( $this->getCreateQueryFormMultiPageTitle() );

		// DELIBERATE FALLTHROUGH
		switch (true) {
			case $dbversion <= 14:
				$this->updateSortByUserDefinedDefault();
			case $dbversion <= 16:
				$this->migrationsDataSimilarEstates();
			case $dbversion <= 18:
				$this->deleteCommentFieldApplicantSearchForm();
			case $dbversion <= 20:
				$this->updateCreateAddressFieldOfIntersetAndOwnerForm();
			case $dbversion <= 21:
				$this->updateShowReferenceEstateOfList();
			case $dbversion <= 23:
				if ($isNewInstall) {
					$this->deactivateCheckDuplicateOfForm();
					$this->_pWpOption->addOption('onoffice-duplicate-check-warning', 1);
				}
			case $dbversion <= 25:
				$this->setDataDetailViewAccessControlValue();
			case $dbversion <= 26:
				$this->deleteMessageFieldApplicantSearchForm();
			case $dbversion <= 27:
				$this->updateEstateListSortBySetting();
			case $dbversion <= 28:
				$this->checkContactFieldInDefaultDetail();
			case $dbversion <= 29:
				$this->checkAllPageIdsHaveDetailShortCode();
				$this->_pWpOption->addOption( 'add-detail-posts-to-rewrite-rules', false );
				$this->_pWpOption->updateOption( 'onoffice-detail-view-showTitleUrl', true );
			case $dbversion <= 31:
				$this->_pWpOption->addOption('onoffice-is-encryptcredent', false);
			case $dbversion <= 32:
				$this->updateShowReferenceEstate();
				$this->setDataDetailViewRestrictAccessControlValue();
			case $dbversion <= 33:
				$this->updateDefaultSettingsTitleAndDescription();
			case $dbversion <= 36:
				$this->_pWpOption->addOption( 'onoffice-settings-honeypot', true );
			case $dbversion <= 37:
				$this->_pWpOption->updateOption( 'onoffice-settings-honeypot', false );
			case $dbversion <= 38:
				$this->updateShowPriceOnRequestOptionForListView();
				$this->updateShowPriceOnRequestOptionForSimilarView();
				$this->updateShowPriceOnRequestOptionForDetailView();
			case $dbversion <= 40:
				$this->updateDefaultPictureTypesForSimilarEstate();
			case $dbversion <= 41:
				$this->updateValueGeoFieldsForEsateList();
			case $dbversion <= 42:
				$this->deleteExposeColumnFromListviews();
			case $dbversion <= 43:
				$this->_pWpOption->updateOption('onoffice-settings-duration-cache', 'hourly');
			case $dbversion <= 46:
				$this->migrateContactTypes();
			case $dbversion <= 52:
				$this->updateContactImageTypesForDetailPage();
			case $dbversion <= 53:
				$this->updatePriceFieldsOptionForSimilarEstate();
				$this->updatePriceFieldsOptionDetailView();
			case $dbversion <= 58:
				$this->migrationsDataShortCodeFormForDetailView();
			case $dbversion <= 59:
				$this->updateValueGeoFieldsForForms();
			case $dbversion <= 61:
				$this->migrateMarkedPropertiesSort();
			default:
				$dbversion = DatabaseChanges::MAX_VERSION;
		}

		$this->_pWpOption->updateOption( 'oo_plugin_db_version', $dbversion, true );
	}

	/**
	 * @return mixed
	 */
	public function getDbVersion()
	{
		return $this->_pWpOption->getOption('oo_plugin_db_version', null);
	}

	/**
	 *
	 * @return string
	 *
	 */

	private function getCreateQueryCache()
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix."oo_plugin_cache";
		$sql = "CREATE TABLE $tableName (
			cache_id bigint(20) NOT NULL AUTO_INCREMENT,
			cache_parameters text NOT NULL,
			cache_parameters_hashed varchar(32) NOT NULL,
			cache_response mediumtext NOT NULL,
			cache_created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (cache_id),
			UNIQUE KEY cache_parameters_hashed (cache_parameters_hashed),
			KEY cache_created (cache_created)
		) $charsetCollate;";

		return $sql;
	}


	private function deleteMessageFieldApplicantSearchForm()
	{
		$prefix = $this->getPrefix();
		$tableName = $prefix . "oo_plugin_forms";
		$tableFieldConfig = $prefix . "oo_plugin_form_fieldconfig";

		$rows = $this->_pWPDB->get_results("SELECT `form_id` FROM {$tableName} WHERE form_type = 'applicantsearch'");

		foreach ($rows as $applicantSearchForm) {
			$allFieldMessages = $this->_pWPDB->get_results("SELECT form_fieldconfig_id FROM " . $tableFieldConfig . " 
										WHERE `fieldname` = 'message' 
										AND `form_id` = " . esc_sql($applicantSearchForm->form_id) . " ");
			foreach ($allFieldMessages as $fieldMessage) {
				$this->_pWPDB->delete($tableFieldConfig,
					array('form_fieldconfig_id' => $fieldMessage->form_fieldconfig_id));
			}
		}
	}
	/**
	 *
	 * @return string
	 *
	 */

	private function getCreateQueryListviews()
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix."oo_plugin_listviews";
		$sql = "CREATE TABLE $tableName (
			`listview_id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(191) NOT NULL,
			`filterId` int(11),
			`sortby` tinytext NOT NULL,
			`sortorder` enum('ASC','DESC') NOT NULL DEFAULT 'ASC',
			`show_status` tinyint(1) NOT NULL DEFAULT '1',
			`list_type` ENUM('default', 'reference', 'favorites', 'units') NOT NULL DEFAULT 'default',
			`template` tinytext NOT NULL,
			`expose` tinytext,
			`recordsPerPage` INT( 10 ) NOT NULL DEFAULT '10',
			`random` tinyint(1) NOT NULL DEFAULT '0',
			`country_active` tinyint(1) NOT NULL DEFAULT '1',
			`zip_active` tinyint(1) NOT NULL DEFAULT '1',
			`city_active` tinyint(1) NOT NULL DEFAULT '0',
			`street_active` tinyint(1) NOT NULL DEFAULT '1',
			`radius_active` tinyint(1) NOT NULL DEFAULT '1',
			`radius` INT( 10 ) NULL DEFAULT NULL,
			`geo_order` VARCHAR( 255 ) NOT NULL DEFAULT 'street,zip,city,country,radius',
			`sortBySetting` ENUM('0','1','2') NOT NULL DEFAULT '0' COMMENT 'Sortierung nach Benutzerwahl: 0 means preselected, 1 means userDefined, 2 means marked properties, if random is active this is empty',
			`sortByUserDefinedDefault` VARCHAR(200) NOT NULL COMMENT 'Standardsortierung',
			`sortByUserDefinedDirection` ENUM('0','1') NOT NULL DEFAULT '0' COMMENT 'Formulierung der Sortierrichtung: 0 means highestFirst/lowestFirt, 1 means descending/ascending',
			`show_reference_estate` tinyint(1) NOT NULL DEFAULT '0',
			`page_shortcode` tinytext NOT NULL,
			`show_map` tinyint(1) NOT NULL DEFAULT '1',
			`show_price_on_request` tinyint(1) NOT NULL DEFAULT '0',
			`markedPropertiesSort` VARCHAR( 255 ) NOT NULL DEFAULT 'neu,top_angebot,no_marker,kauf,miete,reserviert,referenz,exclusive,preisreduktion,objekt_des_tages,objekt_der_woche,secret_sale,courtage_frei',
			`sortByTags` tinytext NOT NULL,
			`sortByTagsDirection` enum('ASC','DESC') NOT NULL DEFAULT 'ASC',
			PRIMARY KEY (`listview_id`),
			UNIQUE KEY `name` (`name`)
		) $charsetCollate;";

		return $sql;
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function getCreateQueryForms()
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix."oo_plugin_forms";
		$sql = "CREATE TABLE $tableName (
			`form_id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(191) NOT NULL,
			`form_type` enum('owner', 'interest', 'contact', 'applicantsearch') NOT NULL DEFAULT 'contact',
			`template` tinytext NOT NULL,
			`recipient` varchar(255) NULL,
			`subject` mediumtext NULL,
			`createaddress` tinyint(1) NOT NULL DEFAULT '0',
			`limitresults` int,
			`checkduplicates` tinyint(1) NOT NULL DEFAULT '0',
			`pages` int NOT NULL DEFAULT '0',
			`captcha` tinyint(1) NOT NULL DEFAULT '0',
			`newsletter` tinyint(1) NOT NULL DEFAULT '0',
			`country_active` tinyint(1) NOT NULL DEFAULT '1',
			`zip_active` tinyint(1) NOT NULL DEFAULT '1',
			`city_active` tinyint(1) NOT NULL DEFAULT '0',
			`street_active` tinyint(1) NOT NULL DEFAULT '1',
			`radius_active` tinyint(1) NOT NULL DEFAULT '1',
			`radius` INT( 10 ) NULL DEFAULT NULL,
			`geo_order` VARCHAR( 255 ) NOT NULL DEFAULT 'street,zip,city,country,radius',
			`show_estate_context` tinyint(1) NOT NULL DEFAULT '0',
			`default_recipient` tinyint(1) NOT NULL DEFAULT '0',
			`contact_type` varchar(255) NULL DEFAULT NULL,
			`page_shortcode` tinytext NOT NULL,
			`show_form_as_modal` tinyint(1) NOT NULL DEFAULT '1',
			PRIMARY KEY (`form_id`),
			UNIQUE KEY `name` (`name`)
		) $charsetCollate;";

		return $sql;
	}

	/**
	 *
	 * @return string
	 *
	 */

	private function getCreateQueryFieldConfig()
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix."oo_plugin_fieldconfig";
		$sql = "CREATE TABLE $tableName (
			`fieldconfig_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`listview_id` int(11) NOT NULL,
			`order` int(11) NOT NULL,
			`fieldname` tinytext NOT NULL,
			`filterable` tinyint(1) NOT NULL DEFAULT '0',
			`hidden` tinyint(1) NOT NULL DEFAULT '0',
			`availableOptions` tinyint(1) NOT NULL DEFAULT '0',
			`convertTextToSelectForCityField` tinyint(1) NOT NULL DEFAULT '0',
			PRIMARY KEY (`fieldconfig_id`)
		) $charsetCollate;";

		return $sql;
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function getCreateQueryFormFieldConfig()
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix."oo_plugin_form_fieldconfig";
		$sql = "CREATE TABLE $tableName (
			`form_fieldconfig_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`form_id` int(11) NOT NULL,
			`order` int(11) NOT NULL,
			`fieldname` tinytext NOT NULL,
			`fieldlabel` varchar(255) NULL,
			`module` tinytext NULL,
			`individual_fieldname` tinyint(1) NOT NULL DEFAULT '0',
			`required` tinyint(1) NOT NULL DEFAULT '0',
			`markdown` tinyint(1) NOT NULL DEFAULT '0',
			`hidden_field` tinyint(1) NOT NULL DEFAULT '0',
			`availableOptions` tinyint(1) NOT NULL DEFAULT '0',
			`page_per_form` tinyint(1) NOT NULL DEFAULT '0',
			PRIMARY KEY (`form_fieldconfig_id`)
		) $charsetCollate;";

		return $sql;
	}

	/**
	 *
	 * @return string
	 *
	 */

	private function getCreateQueryAddressFieldConfig()
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix."oo_plugin_address_fieldconfig";
		$sql = "CREATE TABLE $tableName (
			`address_fieldconfig_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`listview_address_id` int(11) NOT NULL,
			`order` int(11) NOT NULL,
			`fieldname` tinytext NOT NULL,
			`filterable` tinyint(1) NOT NULL DEFAULT '0',
			`hidden` tinyint(1) NOT NULL DEFAULT '0',
			`convertInputTextToSelectForField` tinyint(1) NOT NULL DEFAULT '0',
			PRIMARY KEY (`address_fieldconfig_id`)
		) $charsetCollate;";

		return $sql;
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function getCreateQueryListViewContactPerson()
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix."oo_plugin_listview_contactperson";
		$sql = "CREATE TABLE $tableName (
				`contactperson_id` int(11) NOT NULL AUTO_INCREMENT,
				`listview_id` int(11) NOT NULL,
				`order` int(11) NOT NULL,
				`fieldname` tinytext NOT NULL,
				PRIMARY KEY (`contactperson_id`)
		) $charsetCollate;";

		return $sql;
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function getCreateQueryPictureTypes()
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix."oo_plugin_picturetypes";
		$sql = "CREATE TABLE $tableName (
			`picturetype_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`listview_id` int(11) NOT NULL,
			`picturetype` tinytext NOT NULL,
			PRIMARY KEY (`picturetype_id`)
		) $charsetCollate;";

		return $sql;
	}

	/**
	 *
	 * @return string
	 *
	 */

	private function getCreateQuerySortByUserValues()
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix."oo_plugin_sortbyuservalues";
		$sql = "CREATE TABLE $tableName (
			`sortbyvalue_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`listview_id` int(11) NOT NULL,
			`sortbyuservalue` varchar(100) NOT NULL,
			PRIMARY KEY (`sortbyvalue_id`)
		) $charsetCollate;";

		return $sql;
	}

	/**
	 * @return string
	 */
	private function getCreateQueryContactTypes()
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix."oo_plugin_contacttypes";
		$sql =  "CREATE TABLE $tableName (
			`contacttype_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`form_id` int(11) NOT NULL,
			`contact_type` varchar(100) NOT NULL,
			PRIMARY KEY (`contacttype_id`)
		) $charsetCollate;";

		return $sql;
	}

	/**
	 * @return void
	 */
	private function migrateContactTypes()
	{
		$prefix = $this->getPrefix();
		$tableForm = $prefix."oo_plugin_forms";
		$tableContactTypes = $prefix."oo_plugin_contacttypes";
		$contactTypes = $this->_pWPDB->get_results("SELECT form_id, contact_type 
			FROM $tableForm
			WHERE contact_type IS NOT NULL 
			AND contact_type != '' ", ARRAY_A);

		if (!empty($contactTypes) && is_array($contactTypes)) {
			foreach ($contactTypes as $contactType) {
				$formId = esc_sql((int) $contactType['form_id']);
				$value = esc_sql($contactType['contact_type']);
				$this->_pWPDB->insert($tableContactTypes, ['form_id' => $formId, 'contact_type' => $value]);
			}
		}
	}

	/**
	 *
	 * @return string
	 *
	 */

	private function getCreateQueryListViewsAddress()
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix."oo_plugin_listviews_address";
		$sql = "CREATE TABLE $tableName (
			`listview_address_id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(191) NOT NULL,
			`filterId` int(11) DEFAULT NULL,
			`sortby` tinytext NOT NULL,
			`sortorder` enum('ASC','DESC') NOT NULL DEFAULT 'ASC',
			`template` tinytext NOT NULL,
			`recordsPerPage` int(10) NOT NULL DEFAULT '10',
			`showPhoto` tinyint(1) NOT NULL DEFAULT '0',
			`bildWebseite` tinyint(1) NOT NULL DEFAULT '0',
			`page_shortcode` tinytext NOT NULL,
			`show_map` tinyint(1) NOT NULL DEFAULT '0',
			PRIMARY KEY (`listview_address_id`),
			UNIQUE KEY `name` (`name`)
		) $charsetCollate;";

		return $sql;
	}

	/**
	 * @return string
	 */
	private function getCreateQueryFieldConfigDefaults(): string
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix."oo_plugin_fieldconfig_form_defaults";
		$sql = "CREATE TABLE $tableName (
			`defaults_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`form_id` bigint(20) NOT NULL,
			`fieldname` tinytext NOT NULL,
			PRIMARY KEY (`defaults_id`)
		) $charsetCollate;";

		return $sql;
	}

	/**
	 * @return string
	 */
	private function getCreateQueryFieldConfigDefaultsValues(): string
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix."oo_plugin_fieldconfig_form_defaults_values";
		$sql = "CREATE TABLE $tableName (
			`defaults_values_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`defaults_id` bigint(20) NOT NULL,
			`locale` tinytext NULL DEFAULT NULL,
			`value` text,
			PRIMARY KEY (`defaults_values_id`)
		) $charsetCollate;";

		return $sql;
	}

	/**
	 * @return string
	 */
	private function getCreateQueryFieldConfigCustomsLabels(): string
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix . "oo_plugin_fieldconfig_form_customs_labels";
		$sql = "CREATE TABLE $tableName (
			`customs_labels_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`form_id` bigint(20) NOT NULL,
			`fieldname` tinytext NOT NULL,
			PRIMARY KEY (`customs_labels_id`)
		) $charsetCollate;";

		return $sql;
	}

	/**
	 * @return string
	 */
	private function getCreateQueryFieldConfigTranslatedLabels(): string
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix . "oo_plugin_fieldconfig_form_translated_labels";
		$sql = "CREATE TABLE $tableName (
			`translated_label_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`input_id` bigint(20) NOT NULL,
			`locale` tinytext NULL DEFAULT NULL,
			`value` text,
			PRIMARY KEY (`translated_label_id`)
		) $charsetCollate;";

		return $sql;
	}


	/**
	 * @return string
	 */
	private function getCreateQueryFieldConfigEstateCustomsLabels(): string
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix . "oo_plugin_fieldconfig_estate_customs_labels";
		$sql = "CREATE TABLE $tableName (
			`customs_labels_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`form_id` bigint(20) NOT NULL,
			`fieldname` tinytext NOT NULL,
			PRIMARY KEY (`customs_labels_id`)
		) $charsetCollate;";

		return $sql;
	}


	/**
	 * @return string
	 */
	private function getCreateQueryFieldConfigEstateTranslatedLabels(): string
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix . "oo_plugin_fieldconfig_estate_translated_labels";
		$sql = "CREATE TABLE $tableName (
			`customs_labels_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`input_id` bigint(20) NOT NULL,
			`locale` tinytext NULL DEFAULT NULL,
			`value` text,
			PRIMARY KEY (`customs_labels_id`)
		) $charsetCollate;";

		return $sql;
	}
	/**
	 *
	 */

	private function updateSortByUserDefinedDefault()
	{
		$prefix = $this->getPrefix();
		$tableName = $prefix."oo_plugin_listviews";

		$this->_pWPDB->query("UPDATE $tableName 
				SET `sortByUserDefinedDefault` = CONCAT(`sortByUserDefinedDefault`, '#ASC') 
				WHERE `sortByUserDefinedDefault` != '' AND
				`sortByUserDefinedDefault`  NOT LIKE '%#ASC' AND 
				`sortByUserDefinedDefault` NOT LIKE '%#DESC'");
	}

	/**
	 *
	 */

	public function migrationsDataSimilarEstates()
	{
		$pDataDetailViewOptions = get_option('onoffice-default-view');
		if (!empty($pDataDetailViewOptions)) {
			$dataDetailViewActive = $pDataDetailViewOptions->getDataDetailViewActive();

			$dataDetailViewSimilarEstates = $pDataDetailViewOptions->getDataViewSimilarEstates();
			$pDataSimilarViewOptions = new DataSimilarView();
			$pDataSimilarViewOptions->setDataSimilarViewActive($dataDetailViewActive);

			$pDataViewSimilarEstatesNew = $pDataSimilarViewOptions->getDataViewSimilarEstates();
			$pDataViewSimilarEstatesNew->setFields($dataDetailViewSimilarEstates->getFields());
			$pDataViewSimilarEstatesNew->setSameEstateKind($dataDetailViewSimilarEstates->getSameEstateKind());
			$pDataViewSimilarEstatesNew->setSameMarketingMethod($dataDetailViewSimilarEstates->getSameMarketingMethod());
			$pDataViewSimilarEstatesNew->setSamePostalCode($dataDetailViewSimilarEstates->getSamePostalCode());
			$pDataViewSimilarEstatesNew->setRadius($dataDetailViewSimilarEstates->getRadius());
			$pDataViewSimilarEstatesNew->setRecordsPerPage($dataDetailViewSimilarEstates->getRecordsPerPage());
			$pDataViewSimilarEstatesNew->setTemplate($dataDetailViewSimilarEstates->getTemplate());
			$pDataViewSimilarEstatesNew->setCustomLabels($dataDetailViewSimilarEstates->getCustomLabels());
			$pDataSimilarViewOptions->setDataViewSimilarEstates($pDataViewSimilarEstatesNew);
			$this->_pWpOption->addOption('onoffice-similar-estates-settings-view', $pDataSimilarViewOptions);
		}
	}

	/**
	 *
	 */

	private function deleteCommentFieldApplicantSearchForm()
	{
		$prefix = $this->getPrefix();
		$tableName = $prefix . "oo_plugin_forms";
		$tableFieldConfig = $prefix . "oo_plugin_form_fieldconfig";

		$rows = $this->_pWPDB->get_results("SELECT `form_id` FROM {$tableName} WHERE form_type = 'applicantsearch'");

		foreach ($rows as $applicantSearchForm) {
			$allFieldComments = $this->_pWPDB->get_results("SELECT form_fieldconfig_id FROM $tableFieldConfig
										WHERE `fieldname` = 'krit_bemerkung_oeffentlich'
										AND `form_id` = ".esc_sql($applicantSearchForm->form_id)." ");
			foreach ($allFieldComments as $fieldComment) {
				$this->_pWPDB->delete($tableFieldConfig,
					array('form_fieldconfig_id' => $fieldComment->form_fieldconfig_id));
			}
		}
	}

	/**
	 *
	 */
	private function deleteExposeColumnFromListviews()
	{
		$prefix = $this->getPrefix();
		$tableName = $prefix . "oo_plugin_listviews";
		$sql = "ALTER TABLE $tableName DROP COLUMN expose";
		$this->_pWPDB->query($sql);
	}

	/**
	 *
	 */

	private function updateCreateAddressFieldOfIntersetAndOwnerForm()
	{
		$prefix = $this->getPrefix();
		$sql = "UPDATE {$prefix}oo_plugin_forms
				SET `createaddress` = 1
				WHERE `form_type` = 'interest' OR `form_type` = 'owner'";

		$this->_pWPDB->query($sql);
	}


	/**
	 *
	 */

	private function deactivateCheckDuplicateOfForm()
	{
		$prefix = $this->getPrefix();
		$sql = "UPDATE {$prefix}oo_plugin_forms
				SET `checkduplicates` = 0";

		$this->_pWPDB->query($sql);
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function getPrefix()
	{
		return $this->_pWPDB->prefix;
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function getCharsetCollate()
	{
		return $this->_pWPDB->get_charset_collate();
	}


	/**
	 *
	 * Callback for plugin uninstall hook
	 *
	 * @global wpdb $wpdb
	 *
	 */

	public function deinstall()
	{
		$prefix = $this->getPrefix();
		$tables = array(
			$prefix."oo_plugin_cache",
			$prefix."oo_plugin_listviews",
			$prefix."oo_plugin_fieldconfig",
			$prefix."oo_plugin_picturetypes",
			$prefix."oo_plugin_forms",
			$prefix."oo_plugin_form_fieldconfig",
			$prefix."oo_plugin_listview_contactperson",
			$prefix."oo_plugin_listviews_address",
			$prefix."oo_plugin_address_fieldconfig",
			$prefix."oo_plugin_sortbyuservalues",
			$prefix."oo_plugin_fieldconfig_form_defaults",
			$prefix."oo_plugin_fieldconfig_form_defaults_values",
			$prefix."oo_plugin_fieldconfig_form_customs_labels",
			$prefix."oo_plugin_fieldconfig_form_translated_labels",
			$prefix."oo_plugin_fieldconfig_estate_customs_labels",
			$prefix."oo_plugin_fieldconfig_estate_translated_labels",
			$prefix."oo_plugin_contacttypes",
			$prefix."oo_plugin_fieldconfig_address_customs_labels",
			$prefix."oo_plugin_fieldconfig_address_translated_labels",
			$prefix."oo_plugin_form_activityconfig",
			$prefix."oo_plugin_form_taskconfig",
			$prefix."oo_plugin_form_multipage_title",
		);

		foreach ($tables as $table)	{
			$this->_pWPDB->query("DROP TABLE IF EXISTS ".esc_sql($table));
		}

		$this->_pWpOption->deleteOption('oo_plugin_db_version');
	}


	/**
	 * @return void
	 */

	public function setDataDetailViewAccessControlValue()
	{
		$pDataDetailViewHandler = new DataDetailViewHandler();
		$pDetailView = $pDataDetailViewHandler->getDetailView();
		$pDetailView->setHasDetailView(true);
		$pDataDetailViewHandler->saveDetailView($pDetailView);
	}

	/**
	 * @return void
	 */

	public function setDataDetailViewRestrictAccessControlValue()
	{
		$pDataDetailViewHandler = new DataDetailViewHandler();
		$pDetailView            = $pDataDetailViewHandler->getDetailView();
		$pDetailView->setHasDetailViewRestrict( !$pDetailView->hasDetailView() );
		$pDataDetailViewHandler->saveDetailView( $pDetailView );
	}


	/**
	 * @return void
	 */

	public function setDetailTemplate()
	{
		$detailTemplatesList[ TemplateCall::TEMPLATE_FOLDER_INCLUDED ] = glob( plugin_dir_path( ONOFFICE_PLUGIN_DIR
		                                    . '/index.php' ) . 'templates.dist/' . 'estate' . '/' . 'default_detail' . '.php' );
		$detailTemplatesList[ TemplateCall::TEMPLATE_FOLDER_PLUGIN ]   = glob( plugin_dir_path( ONOFFICE_PLUGIN_DIR )
		                                    . 'onoffice-personalized/templates/' . 'estate' . '/' . 'default_detail' . '.php' );
		$detailTemplatesList[ TemplateCall::TEMPLATE_FOLDER_THEME ]    = glob( get_stylesheet_directory()
		                                    . '/onoffice-theme/templates/' . 'estate' . '/' . 'default_detail' . '.php' );

		$detailTemplatesList = ( new TemplateCall() )->formatTemplatesData( array_filter( $detailTemplatesList ), 'estate' );
		$firstTemplatePath   = reset( $detailTemplatesList )['path'];

		$pDataDetailViewHandler = $this->_pContainer->get( DataDetailViewHandler::class );
		$pDetailView            = $pDataDetailViewHandler->getDetailView();
		$pDetailView->setTemplate( key( $firstTemplatePath ) );
		$pDataDetailViewHandler->saveDetailView( $pDetailView );
	}


	/**
	 * @return void
	 */

	public function updateShowReferenceEstateOfList()
	{
		$prefix = $this->getPrefix();
		$sql    = "UPDATE {$prefix}oo_plugin_listviews
				SET `show_reference_estate` = 1";

		$this->_pWPDB->query( $sql );
	}


	/**
	 * @return void
	 */

	public function updateShowReferenceEstate()
	{
		$prefix = $this->getPrefix();
		$sql    = "UPDATE {$prefix}oo_plugin_listviews
				SET `show_reference_estate` = '2'
				WHERE `list_type`='reference'";

		$this->_pWPDB->query( $sql );
	}


	/**
	 * @return void
	 */

	public function updateEstateListSortBySetting()
	{
		$prefix = $this->getPrefix();
		$sql    = "UPDATE {$prefix}oo_plugin_listviews
				SET `sortBySetting` = '0' 
				WHERE (`sortBySetting` IS NULL OR `sortBySetting` = '') 
				AND `random` = 0";

		$this->_pWPDB->query( $sql );
	}


	/**
	 * @return void
	 */

	public function checkContactFieldInDefaultDetail() {
		$pDataDetailViewHandler = $this->_pContainer->get( DataDetailViewHandler::class );
		$pDetailView = $pDataDetailViewHandler->getDetailView();
		$addressFields = $pDetailView->getAddressFields();

		foreach (AddressList::DEFAULT_FIELDS_REPLACE as $defaultField => $newField) {
			if (in_array($defaultField, $addressFields)) {
				$key = array_search($defaultField, $addressFields);
				unset($addressFields[$key]);

				if (!in_array($newField, $addressFields)) {
					$addressFields[$key] = $newField;
				}
			}
		}
		ksort($addressFields);
		$pDetailView->setAddressFields($addressFields);
		$pDataDetailViewHandler->saveDetailView( $pDetailView );
	}


	/**
	 * @return void
	 */

	public function updateDefaultSettingsTitleAndDescription() {
        $WPPluginChecker = new WPPluginChecker;

		if ( get_option('onoffice-settings-title-and-description') === false ) {
			if ($WPPluginChecker->isSEOPluginActive()) {
				update_option('onoffice-settings-title-and-description', 1);
			} else {
				update_option('onoffice-settings-title-and-description', 0);
			}
		};
	}


	/**
	 *
	 * @return void
	 *
	 */

	public function checkAllPageIdsHaveDetailShortCode()
	{
		$pDataDetailViewHandler = $this->_pContainer->get( DataDetailViewHandler::class );
		$pDetailView            = $pDataDetailViewHandler->getDetailView();
		$detailViewName         = 'view="' . $pDetailView->getName() . '"';
		$tableName              = $this->getPrefix() . "posts";

		$listDetailPosts = $this->_pWPDB->get_results( "SELECT `ID` FROM {$tableName}
														WHERE post_status = 'publish'
														AND post_content LIKE '%[oo_estate%" . $detailViewName . "%]%'", ARRAY_A );
		foreach ( $listDetailPosts as $post ) {
			$pDetailView->addToPageIdsHaveDetailShortCode( (int) $post['ID'] );
		}
		$pDataDetailViewHandler->saveDetailView( $pDetailView );
	}


	/**
	 *
	 */
	private function updateDefaultPictureTypesForSimilarEstate()
	{
		$pDataSimilarViewOptions = get_option('onoffice-similar-estates-settings-view');
		if(!empty($pDataSimilarViewOptions) && empty($pDataSimilarViewOptions->getDataViewSimilarEstates()->getPictureTypes())){
			$pDataSimilarViewOptions->getDataViewSimilarEstates()->setPictureTypes([ImageTypes::TITLE]);
			$this->_pWpOption->updateOption('onoffice-similar-estates-settings-view', $pDataSimilarViewOptions);
		}
	}

	/**
	 * @return void
	 */

	private function updateShowPriceOnRequestOptionForListView()
	{
		$prefix = $this->getPrefix();
		$tableNameFieldConfig = $prefix . "oo_plugin_fieldconfig";
		$tableNameListViews = $prefix . "oo_plugin_listviews";

		$listViewsPosts = $this->_pWPDB->get_results( "SELECT `listview_id` FROM {$tableNameFieldConfig}
							WHERE fieldname = 'preisAufAnfrage'", ARRAY_A );
		if(!empty($listViewsPosts)){
			$this->_pWPDB->get_results("DELETE FROM {$tableNameFieldConfig} " ."WHERE fieldname = 'preisAufAnfrage'");
			foreach ( $listViewsPosts as $post ) {
				$id = esc_sql((int) $post['listview_id']);
				$this->_pWPDB->query("UPDATE $tableNameListViews 
					SET `show_price_on_request` = '1'
					WHERE `listview_id` = $id");
			}
		}
	}


	/**
	 * @return void
	 */

	private function updateShowPriceOnRequestOptionForSimilarView()
	{
		$pDataSimilarViewOptions = get_option('onoffice-similar-estates-settings-view');
		if(!empty($pDataSimilarViewOptions) && in_array('preisAufAnfrage', $pDataSimilarViewOptions->getFields())){
			$oldData = $pDataSimilarViewOptions->getDataViewSimilarEstates()->getFields();
			$fields = array_flip($oldData);
			unset($fields['preisAufAnfrage']);
			$newData = array_flip($fields);
			$pDataSimilarViewOptions->getDataViewSimilarEstates()->setFields($newData);
			$pDataSimilarViewOptions->setFields($newData);
			$pDataSimilarViewOptions->getDataViewSimilarEstates()->setShowPriceOnRequest(true);
			$this->_pWpOption->updateOption('onoffice-similar-estates-settings-view', $pDataSimilarViewOptions);
		}
	}


	/**
	 * @return void
	 */

	private function updateShowPriceOnRequestOptionForDetailView()
	{
		$pDataDetailViewOptions = get_option('onoffice-default-view');
		if(!empty($pDataDetailViewOptions) && in_array('preisAufAnfrage', $pDataDetailViewOptions->getFields())){
			$oldData = $pDataDetailViewOptions->getFields();
			$fields = array_flip($oldData);
			unset($fields['preisAufAnfrage']);
			$newData = array_flip($fields);
			$pDataDetailViewOptions->setFields($newData);
			$pDataDetailViewOptions->setShowPriceOnRequest(true);
			$this->_pWpOption->updateOption('onoffice-default-view', $pDataDetailViewOptions);
		}
	}

	/**
	 * @return void
	 */
	public function updateValueGeoFieldsForEsateList()
	{
		$prefix = $this->getPrefix();
		$sql = "UPDATE {$prefix}oo_plugin_listviews
			SET country_active = 1, radius_active = 1";

		$this->_pWPDB->query($sql);
	}

	/**
	 * @return void
	 */
	private function migrationsDataShortCodeFormForDetailView()
	{
		$pDataDetailViewOptions = get_option('onoffice-default-view');
		if(!empty($pDataDetailViewOptions) && !empty($pDataDetailViewOptions->getShortCodeForm())){
			$recordManagerReadForm = $this->_pContainer->get(RecordManagerReadForm::class);
			$allRecordsForm = $recordManagerReadForm->getRowByName($pDataDetailViewOptions->getShortCodeForm());
			if ($allRecordsForm['form_type'] !== Form::TYPE_CONTACT) {
				$this->_pWpOption->updateOption('onoffice-default-view', null);
			}
		}
	}

	/**
	 * @return void
	 */
	private function addColumnsForHighlights(): string
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix."oo_plugin_fieldconfig";
		$sql = "CREATE TABLE $tableName (
			`highlighted` tinyint(1) NOT NULL DEFAULT '0',
		) $charsetCollate;";

		return $sql;
	}

	/**
	 * @return string
	 */
	private function getCreateQueryFieldConfigAddressCustomsLabels(): string
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix . "oo_plugin_fieldconfig_address_customs_labels";
		$sql = "CREATE TABLE $tableName (
			`customs_labels_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`form_id` bigint(20) NOT NULL,
			`fieldname` tinytext NOT NULL,
			PRIMARY KEY (`customs_labels_id`)
		) $charsetCollate;";

		return $sql;
	}

	/**
	 * @return string
	 */
	private function getCreateQueryFieldConfigAddressTranslatedLabels(): string
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix . "oo_plugin_fieldconfig_address_translated_labels";
		$sql = "CREATE TABLE $tableName (
			`translated_label_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`input_id` bigint(20) NOT NULL,
			`locale` tinytext NULL DEFAULT NULL,
			`value` text,
			PRIMARY KEY (`translated_label_id`)
		) $charsetCollate;";

		return $sql;
	}

	/**
	 * @return string
	 */
	private function getCreateQueryFormActivityConfig(): string
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix."oo_plugin_form_activityconfig";
		$sql = "CREATE TABLE $tableName (
			`form_activityconfig_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`form_id` int(11) NOT NULL,
			`write_activity` tinyint(1) NOT NULL DEFAULT '0',
			`action_kind` tinytext NOT NULL,
			`action_type` tinytext NOT NULL,
			`origin_contact` tinytext NOT NULL,
			`advisory_level` tinytext NOT NULL,
			`characteristic` VARCHAR(255) NOT NULL,
			`remark` text NOT NULL,
			PRIMARY KEY (`form_activityconfig_id`)
		) $charsetCollate;";

		return $sql;
	}

	/**
	 * @return string
	 */
	private function getCreateQueryFormTaskConfig(): string
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix."oo_plugin_form_taskconfig";
		$sql = "CREATE TABLE $tableName (
			`form_taskconfig_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`form_id` int(11) NOT NULL,
			`enable_create_task` tinyint(1) NOT NULL DEFAULT '0',
			`responsibility` VARCHAR(255) NOT NULL,
			`processor` VARCHAR(255) NOT NULL,
			`type` int(11) NOT NULL DEFAULT '0',
			`priority` tinyint(1) NOT NULL DEFAULT '0',
			`subject` VARCHAR(255) NOT NULL,
			`description` text NOT NULL,
			`status` tinyint(1) NOT NULL DEFAULT '0',
			PRIMARY KEY (`form_taskconfig_id`)
		) $charsetCollate;";

		return $sql;
	}

	/**
	 * @return void
	 */

	private function updateContactImageTypesForDetailPage()
	{
		$pDataDetailViewOptions = $this->_pWpOption->getOption('onoffice-default-view');
		if(!empty($pDataDetailViewOptions) && in_array('imageUrl', $pDataDetailViewOptions->getAddressFields())){
			$pDataDetailViewOptions->setContactImageTypes([ImageTypes::PASSPORTPHOTO]);
			$this->_pWpOption->updateOption('onoffice-default-view', $pDataDetailViewOptions);
		}
	}

	/**
	 * @return void
	 */

	public function updateValueGeoFieldsForForms()
	{
		$prefix = $this->getPrefix();

		$sql = "UPDATE {$prefix}oo_plugin_forms
			SET country_active = 1, radius_active = CASE 
			WHEN form_type = '" . Form::TYPE_INTEREST . "' THEN 1
			ELSE radius_active END
		WHERE form_type IN ('" . Form::TYPE_INTEREST . "', '" . Form::TYPE_APPLICANT_SEARCH . "')";

		$this->_pWPDB->query($sql);
	}

	/**
	 *
	 */
	public function updatePriceFieldsOptionForSimilarEstate()
	{
		$pDataSimilarViewOptions = $this->_pWpOption->getOption('onoffice-similar-estates-settings-view');
		if (!empty($pDataSimilarViewOptions)) {
			$pDataViewSimilarEstates = new DataViewSimilarEstates();
			$pDataSimilarViewOptions->getDataViewSimilarEstates()->setListFieldsShowPriceOnRequest($pDataViewSimilarEstates->getListFieldsShowPriceOnRequest());
			$this->_pWpOption->updateOption('onoffice-similar-estates-settings-view', $pDataSimilarViewOptions);
		}
	}

	/**
	 *
	 */
	public function updatePriceFieldsOptionDetailView()
	{
		$pDataDetailViewOptions = $this->_pWpOption->getOption('onoffice-default-view');
		if (!empty($pDataDetailViewOptions)) {
			$pDataDataDetailView = new DataDetailView();
			$pDataDetailViewOptions->setListFieldsShowPriceOnRequest($pDataDataDetailView->getListFieldsShowPriceOnRequest());
			$this->_pWpOption->updateOption('onoffice-default-view', $pDataDetailViewOptions);
		}
	}

	/**
	 * @return string
	 */
	private function getCreateQueryFormMultiPageTitle(): string
	{
		$prefix = $this->getPrefix();
		$charsetCollate = $this->getCharsetCollate();
		$tableName = $prefix . "oo_plugin_form_multipage_title";
		$sql = "CREATE TABLE $tableName (
			`form_multipage_title_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`form_id` bigint(20) NOT NULL,
			`locale` tinytext NULL DEFAULT NULL,
			`value` text,
			`page` tinyint(1) NOT NULL DEFAULT '0',
			PRIMARY KEY (`form_multipage_title_id`)
		) $charsetCollate;";

		return $sql;
	}

	/**
	* @return void
	*/
	private function migrateMarkedPropertiesSort(): void
	{
		$requiredTerms = [
			'exclusive',
			'preisreduktion',
			'objekt_des_tages',
			'objekt_der_woche',
			'secret_sale',
			'courtage_frei'
		];
	
		$requiredTermsLower = array_map('strtolower', $requiredTerms);
		$tableName = $this->getPrefix() . "oo_plugin_listviews";
	
		$rows = $this->_pWPDB->get_results(
			"SELECT `listview_id`, `markedPropertiesSort` FROM {$tableName}"
		);
	
		foreach ($rows as $row) {
			$currentTerms = array_filter(array_map('trim', explode(',', $row->markedPropertiesSort)));
	
			$remainingTerms = [];
			foreach ($currentTerms as $term) {
				if (!in_array(strtolower($term), $requiredTermsLower, true)) {
					$remainingTerms[] = $term;
				}
			}
	
			$mergedTerms = array_merge($remainingTerms, $requiredTerms);
			$updatedTermsCsv = implode(',', $mergedTerms);
	
			if (strcasecmp($updatedTermsCsv, $row->markedPropertiesSort) !== 0) {
				$updateResult = $this->_pWPDB->update(
					$tableName,
					['markedPropertiesSort' => $updatedTermsCsv],
					['listview_id' => $row->listview_id],
					['%s'],
					['%d']
				);
	
				if ($updateResult === false) {
					error_log("Failed to update listview_id {$row->listview_id} in migrateMarkedPropertiesSort()"); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Needed for debugging database migration issues.
				}
			}
		}
	}
}
