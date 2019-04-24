<?php


/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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

namespace onOffice\WPlugin;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 * Creates tables and sets options
 * Also removes them
 *
 */

abstract class Installer
{
	/**
	 *
	 * Callback for plugin activation hook
	 *
	 */

	static public function install()
	{
		// If you are modifying this, please also make sure to edit the test
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$dbversion = get_option('oo_plugin_db_version', null);

		if ($dbversion === null) {
			dbDelta( self::getCreateQueryCache() );

			$dbversion = 1.0;
			add_option( 'oo_plugin_db_version', $dbversion, '', false );
		}

		if ($dbversion == 1.0) {
			dbDelta( self::getCreateQueryListviews() );
			dbDelta( self::getCreateQueryFieldConfig() );
			dbDelta( self::getCreateQueryPictureTypes() );
			dbDelta( self::getCreateQueryListViewContactPerson() );
			dbDelta( self::getCreateQueryForms() );
			dbDelta( self::getCreateQueryFormFieldConfig() );

			$dbversion = 2.0;
		}

		if ($dbversion == 2.0) {
			dbDelta( self::getCreateQueryListViewsAddress() );
			dbDelta( self::getCreateQueryAddressFieldConfig() );
			$dbversion = 3.0;
		}

		if ($dbversion == 3.0) {
			// new column: captcha
			dbDelta( self::getCreateQueryForms() );
			$dbversion = 4;
		}

		if ($dbversion == 4.0) {
			// new column: newsletter
			dbDelta( self::getCreateQueryForms() );
			$dbversion = 5;
		}

		if ($dbversion == 5.0) {
			// new columns: filterable, hidden
			dbDelta( self::getCreateQueryAddressFieldConfig() );
			$dbversion = 6;
		}

		if ($dbversion == 6.0) {
			// new columns: availableOptions
			dbDelta( self::getCreateQueryFieldConfig() );
			dbDelta( self::getCreateQueryFormFieldConfig() );
			$dbversion = 7;
		}

		if ($dbversion == 7.0) {
			// new columns: {country,zip,street,radius}_active
			dbDelta( self::getCreateQueryListviews() );
			dbDelta( self::getCreateQueryForms() );
			$dbversion = 8;
		}

		if ($dbversion == 8.0) {
			// new columns: radius
			dbDelta( self::getCreateQueryListviews() );
			dbDelta( self::getCreateQueryForms() );
			$dbversion = 9;
		}

		if ($dbversion == 9.0) {
			// new columns: city_active
			dbDelta( self::getCreateQueryListviews() );
			dbDelta( self::getCreateQueryForms() );
			$dbversion = 10;
		}

		update_option( 'oo_plugin_db_version', $dbversion, false);

		$pContentFilter = new ContentFilter();
		$pContentFilter->addCustomRewriteTags();
		$pContentFilter->addCustomRewriteRules();
		self::flushRules();
	}


	/**
	 *
	 * @return string
	 *
	 */

	static private function getCreateQueryCache()
	{
		$prefix = self::getPrefix();
		$charsetCollate = self::getCharsetCollate();
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


	/**
	 *
	 * @return string
	 *
	 */

	static private function getCreateQueryListviews()
	{
		$prefix = self::getPrefix();
		$charsetCollate = self::getCharsetCollate();
		$tableName = $prefix."oo_plugin_listviews";
		$sql = "CREATE TABLE $tableName (
			`listview_id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(191) NOT NULL,
			`filterId` int(11),
			`sortby` tinytext NOT NULL,
			`sortorder` enum('ASC','DESC') NOT NULL DEFAULT 'ASC',
			`show_status` tinyint(1) NOT NULL DEFAULT '0',
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

	static private function getCreateQueryForms()
	{
		$prefix = self::getPrefix();
		$charsetCollate = self::getCharsetCollate();
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

	static private function getCreateQueryFieldConfig()
	{
		$prefix = self::getPrefix();
		$charsetCollate = self::getCharsetCollate();
		$tableName = $prefix."oo_plugin_fieldconfig";
		$sql = "CREATE TABLE $tableName (
			`fieldconfig_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`listview_id` int(11) NOT NULL,
			`order` int(11) NOT NULL,
			`fieldname` tinytext NOT NULL,
			`filterable` tinyint(1) NOT NULL DEFAULT '0',
			`hidden` tinyint(1) NOT NULL DEFAULT '0',
			`availableOptions` tinyint(1) NOT NULL DEFAULT '0',
			PRIMARY KEY (`fieldconfig_id`)
		) $charsetCollate;";

		return $sql;
	}


	/**
	 *
	 * @return string
	 *
	 */

	static private function getCreateQueryFormFieldConfig()
	{
		$prefix = self::getPrefix();
		$charsetCollate = self::getCharsetCollate();
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
			`availableOptions` tinyint(1) NOT NULL DEFAULT '0',
			PRIMARY KEY (`form_fieldconfig_id`)
		) $charsetCollate;";

		return $sql;
	}


	/**
	 *
	 * @return string
	 *
	 */

	static private function getCreateQueryAddressFieldConfig()
	{
		$prefix = self::getPrefix();
		$charsetCollate = self::getCharsetCollate();
		$tableName = $prefix."oo_plugin_address_fieldconfig";
		$sql = "CREATE TABLE $tableName (
			`address_fieldconfig_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`listview_address_id` int(11) NOT NULL,
			`order` int(11) NOT NULL,
			`fieldname` tinytext NOT NULL,
			`filterable` tinyint(1) NOT NULL DEFAULT '0',
			`hidden` tinyint(1) NOT NULL DEFAULT '0',
			PRIMARY KEY (`address_fieldconfig_id`)
		) $charsetCollate;";

		return $sql;
	}


	/**
	 *
	 * @return string
	 *
	 */

	static private function getCreateQueryListViewContactPerson()
	{
		$prefix = self::getPrefix();
		$charsetCollate = self::getCharsetCollate();
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

	static private function getCreateQueryPictureTypes()
	{
		$prefix = self::getPrefix();
		$charsetCollate = self::getCharsetCollate();
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

	static private function getCreateQueryListViewsAddress()
	{
		$prefix = self::getPrefix();
		$charsetCollate = self::getCharsetCollate();
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
			PRIMARY KEY (`listview_address_id`),
			UNIQUE KEY `name` (`name`)
		) $charsetCollate;";

		return $sql;
	}


	/**
	 *
	 * @global WP_Rewrite $wp_rewrite
	 *
	 */

	static private function flushRules()
	{
		global $wp_rewrite;
		$wp_rewrite->flush_rules(false);
	}


	/**
	 *
	 * @global \wpdb $wpdb
	 * @return string
	 *
	 */

	static private function getPrefix()
	{
		global $wpdb;
		return $wpdb->prefix;
	}


	/**
	 *
	 * @global \wpdb $wpdb
	 * @return string
	 *
	 */

	static private function getCharsetCollate()
	{
		global $wpdb;
		return $wpdb->get_charset_collate();
	}


	/**
	 *
	 */

	static public function deactivate()
	{
		// @codeCoverageIgnoreStart
		self::flushRules();
		// @codeCoverageIgnoreEnd
	}


	/**
	 *
	 * Callback for plugin uninstall hook
	 *
	 * @global \wpdb $wpdb
	 *
	 */

	static public function deinstall()
	{
		global $wpdb;
		$prefix = self::getPrefix();

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
		);

		foreach ($tables as $table)
		{
			$wpdb->query("DROP TABLE IF EXISTS ".esc_sql($table));
		}

		delete_option('oo_plugin_db_version');
		delete_option('onoffice-default-view');
		delete_option('onoffice-favorization-enableFav');
		delete_option('onoffice-favorization-favButtonLabelFav');
		delete_option('onoffice-maps-mapprovider');
		delete_option('onoffice-settings-apisecret');
		delete_option('onoffice-settings-apikey');

		self::flushRules();
	}
}
