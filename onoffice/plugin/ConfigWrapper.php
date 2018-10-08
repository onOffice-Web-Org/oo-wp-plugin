<?php

/**
 *
 *    Copyright (C) 2016 onOffice Software AG
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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

use const ABSPATH;


/**
 *
 */

class ConfigWrapper {
	/** @var ConfigWrapper */
	private static $_pInstance = null;

	/** @var array */
	private $_config = null;


	/**
	 *
	 * @return ConfigWrapper
	 *
	 */

	public static function getInstance()
	{
		if (null === self::$_pInstance) {
			self::$_pInstance = new static;
		}

		return self::$_pInstance;
	}


	/**
	 *
	 */

	private function __construct()
	{
		$this->_config = $this->readConfig();
	}


	/**
	 *
	 * @return string
	 *
	 */

	public static function getSubPluginPath()
	{
		return ABSPATH.'wp-content/plugins/onoffice-personalized';
	}


	/**
	 *
	 * @return string
	 *
	 */

	public static function getTemplateBasePath()
	{
		return ABSPATH.'wp-content/plugins';
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function readConfig()
	{
		$config = [];
		$configFile = dirname(__FILE__).'/../config.php';
		$configFilePersonalized = self::getSubPluginPath().'/config.php';

		if (is_file($configFilePersonalized)) {
			include $configFilePersonalized;
		} else {
			include $configFile;
		}

		return $config;
	}


	/**
	 *
	 */

	private function __clone() {}


	/**
	 *
	 * @param string $key
	 * @return mixed
	 *
	 */

	public function getConfigByKey($key)
	{
		if (array_key_exists($key, $this->_config)) {
			return $this->_config[$key];
		}
		return null;
	}


	/**
	 *
	 * @return mixed
	 *
	 */

	public function getConfig()
	{
		return $this->_config;
	}
}
