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

use onOffice\WPlugin\WP\WPOptionWrapperBase;
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
	/** @var WPScriptStyleBase */
	private $_pWPScriptStyle = null;

	/** @var WPOptionWrapperBase */
	private $_pWPOptionWrapper = null;


	/**
	 *
	 * @param WPScriptStyleBase $pWPScriptStyle
	 * @param WPOptionWrapperBase $pWPOptionWrapper
	 *
	 */

	public function __construct(
		WPScriptStyleBase $pWPScriptStyle,
		WPOptionWrapperBase $pWPOptionWrapper)
	{
		$this->_pWPOptionWrapper = $pWPOptionWrapper;
		$this->_pWPScriptStyle = $pWPScriptStyle;
	}


	/**
	 *
	 */

	public function enqueue()
	{
		$this->_pWPScriptStyle->enqueueScript('gmapsinit');
	}


	/**
	 *
	 */

	public function register()
	{
		$key = $this->_pWPOptionWrapper->getOption('onoffice-settings-googlemaps-key', null);
		$url = 'https://maps.googleapis.com/maps/api/js?'.http_build_query(['key' => $key]);
		$this->_pWPScriptStyle->registerScript('google-maps', $url);
		$this->_pWPScriptStyle->registerScript('gmapsinit',
			plugins_url('dist/gmapsinit.min.js', ONOFFICE_PLUGIN_DIR.'/index.php'), ['google-maps']);
	}
}
