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

use onOffice\WPlugin\WP\WPScriptStyleBase;
use const ONOFFICE_PLUGIN_DIR;
use function plugins_url;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class ScriptLoaderMapGoogleMaps
	implements ScriptLoader
{
	/** @var string */
	private $_pluginPath = '';


	/**
	 *
	 * @param string $pluginUrl
	 *
	 */

	public function __construct(string $pluginUrl = null)
	{
		$this->_pluginPath = $pluginUrl ?? ONOFFICE_PLUGIN_DIR.'/index.php';
	}


	/**
	 *
	 * @param WPScriptStyleBase $pWPScriptStyle
	 *
	 */

	public function enqueue(WPScriptStyleBase $pWPScriptStyle)
	{
		$pWPScriptStyle->enqueueScript('gmapsinit');
	}


	/**
	 *
	 * @param WPScriptStyleBase $pWPScriptStyle
	 *
	 */

	public function register(WPScriptStyleBase $pWPScriptStyle)
	{
		$pWPScriptStyle->registerScript('google-maps', 'https://maps.googleapis.com/maps/api/js');
		$pWPScriptStyle->registerScript('gmapsinit',
			plugins_url('/js/gmapsinit.js', $this->_pluginPath), ['google-maps']);
	}
}
