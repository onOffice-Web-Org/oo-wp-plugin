<?php

/**
 *
 *    Copyright (C) 2018-2019 onOffice GmbH
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

class ScriptLoaderMapOsm
	implements ScriptLoader
{
	/** @var WPScriptStyleBase */
	private $_pWPScriptStyle = null;

	/**
	 *
	 * @param WPScriptStyleBase $pWPScriptStyle
	 *
	 */

	public function __construct(WPScriptStyleBase $pWPScriptStyle)
	{
		$this->_pWPScriptStyle = $pWPScriptStyle;
	}


	/**
	 *
	 */

	public function enqueue()
	{
		$this->_pWPScriptStyle->enqueueScript('leaflet-script');
		$this->_pWPScriptStyle->enqueueStyle('leaflet-style');
	}


	/**
	 *
	 */

	public function register()
	{
		$this->_pWPScriptStyle->registerStyle('leaflet-style',
			plugins_url('/third_party/leaflet/leaflet.css', ONOFFICE_PLUGIN_DIR.'/index.php'));

		$this->_pWPScriptStyle->registerScript('leaflet-script',
			plugins_url('/third_party/leaflet/leaflet.js', ONOFFICE_PLUGIN_DIR.'/index.php'));
	}
}
