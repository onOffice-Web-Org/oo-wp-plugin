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
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$dbversion = get_option('oo_plugin_db_version', null);

		if ($dbversion === null)
		{
			dbDelta( self::getCreateQueryCache() );
			add_option( 'oo_plugin_db_version', 1.0 );
		}

		if ($dbversion == 1.0)
		{
			dbDelta( self::getCreateQueryListviews() );
			dbDelta( self::getCreateQueryFieldConfig() );
			dbDelta( self::getCreateQueryPictureTypes() );
			dbDelta( self::getCreateQueryListViewContactPerson() );
			dbDelta( self::getCreateQueryForms() );
			dbDelta( self::getCreateQueryFormFieldConfig() );
		}

		update_option( 'oo_plugin_db_version', 2.0 );

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
			`form_type` enum('owner', 'interest', 'contact', 'free') NOT NULL DEFAULT 'free',
			`template` tinytext NOT NULL,
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
			PRIMARY KEY (`form_fieldconfig_id`)
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
		self::flushRules();
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
		);

		foreach ($tables as $table)
		{
			$wpdb->query("DROP TABLE IF EXISTS $table");
		}

		delete_option('oo_plugin_db_version');

		self::flushRules();
	}
}
