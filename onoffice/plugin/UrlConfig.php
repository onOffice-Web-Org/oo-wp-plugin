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
 * @copyright 2003-2016, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

/**
 *
 */

class UrlConfig {

	/**
	 *
	 * You can either put the configuration for the view into the config of the list
	 * or put in a reference
	 *
	 * @param string $view
	 * @return int the page id
	 *
	 */

	public static function getViewPageIdByConfig( $viewConfig ) {
		$pageid = null;

		if ( is_string( $viewConfig ) ) {
			$substr = substr($viewConfig, 1);
			list($configName, $view) = explode( ':', $substr );
			$estateConfig = ConfigWrapper::getInstance()->getConfigByKey( 'estate' );
			$pageId = $estateConfig[$configName]['views'][$view]['pageid'];
		} elseif ( is_array( $viewConfig ) ) {
			$pageId = $viewConfig['pageid'];
		}

		return $pageId;
	}
}
