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

namespace onOffice\WPlugin\WP;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class WPScriptStyleDefault
	implements WPScriptStyleBase
{
	/**
	 *
	 * @param string $handle
	 * @param string $src
	 * @param array $deps
	 * @param string $ver
	 * @param bool $inFooter
	 *
	 */

	public function enqueueScript(string $handle, string $src = '', array $deps = [],
		$ver = false, bool $inFooter = false)
	{
		wp_enqueue_script($handle, $src, $deps, $ver, $inFooter);
	}


	/**
	 *
	 * @param string $handle
	 * @param string $src
	 * @param array $deps
	 * @param string $ver
	 * @param string $media
	 *
	 */

	public function enqueueStyle(string $handle, string $src = '', array $deps = [],
		$ver = false, string $media = 'all')
	{
		wp_enqueue_style($handle, $src, $deps, $ver, $media);
	}


	/**
	 *
	 * @param string $handle
	 * @param string $src
	 * @param array $deps
	 * @param string $ver
	 * @param bool $inFooter
	 * @return bool
	 *
	 */

	public function registerScript(string $handle, string $src, array $deps = [], $ver = false,
		bool $inFooter = false): bool
	{
		return wp_register_script($handle, $src, $deps, $ver, $inFooter);
	}


	/**
	 *
	 * @param string $handle
	 * @param string $src
	 * @param array $deps
	 * @param string $ver
	 * @param string $media
	 * @return bool
	 *
	 */

	public function registerStyle(string $handle, string $src, array $deps = [], $ver = false,
		string $media = 'all'): bool
	{
		return wp_register_style($handle, $src, $deps, $ver, $media);
	}


	/**
	 *
	 * @param string $handle
	 * @param string $name
	 * @param array $data
	 *
	 */

	public function localizeScript(string $handle, string $name, array $data){
		wp_localize_script($handle, $name, $data);
	}
}
