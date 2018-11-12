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

namespace onOffice\WPlugin\ScriptLoader;

use onOffice\WPlugin\Types\MapProvider;
use onOffice\WPlugin\WP\WPScriptStyleBase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class ScriptLoaderMap
	implements ScriptLoader
{
	/** @var ScriptLoaderMapEnvironment */
	private $_pScriptLoaderMapEnvironment = null;

	/** @var ScriptLoader */
	private $_pSpecificMapLoader = null;


	/**
	 *
	 * @param WPScriptStyleBase $pScriptloaderMapEnvironment
	 *
	 */

	public function __construct(ScriptLoaderMapEnvironment $pScriptloaderMapEnvironment = null)
	{
		$this->_pScriptLoaderMapEnvironment = $pScriptloaderMapEnvironment ??
			new ScriptLoaderMapEnvironmentDefault();

		switch ($this->_pScriptLoaderMapEnvironment->getMapProvider()) {
			case MapProvider::GOOGLE_MAPS:
				$this->_pSpecificMapLoader = new ScriptLoaderMapGoogleMaps();
				break;
			case MapProvider::OPEN_STREET_MAPS:
			default:
				$this->_pSpecificMapLoader = new ScriptLoaderMapOsm();
				break;
		}
	}


	/**
	 *
	 * @param WPScriptStyleBase $pWPScriptStyle
	 *
	 */

	public function enqueue(WPScriptStyleBase $pWPScriptStyle)
	{
		$this->_pSpecificMapLoader->enqueue($pWPScriptStyle);
	}


	/**
	 *
	 * @param WPScriptStyleBase $pWPScriptStyle
	 *
	 */

	public function register(WPScriptStyleBase $pWPScriptStyle)
	{
		$this->_pSpecificMapLoader->register($pWPScriptStyle);
	}


	/**
	 *
	 * @return ScriptLoader
	 *
	 */

	public function getSpecificMapLoader(): ScriptLoader
	{
		return $this->_pSpecificMapLoader;
	}
}
