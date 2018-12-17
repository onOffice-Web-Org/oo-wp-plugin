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

use Exception;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class WPScriptStyleTest
	implements WPScriptStyleBase
{
	/** @var array */
	private $_registeredScripts = [];

	/** @var array */
	private $_registeredStyles = [];

	/** @var array */
	private $_enqueuedScripts = [];

	/** @var array */
	private $_enqueuedStyles = [];

	/** @var array */
	private $_localizedScript = [];

	/**
	 *
	 * @param string $handle
	 * @param string $src
	 * @param array $deps
	 * @param string $ver
	 * @param bool $inFooter
	 * @throws Exception
	 *
	 */

	public function enqueueScript(string $handle, string $src = '', array $deps = [], $ver = false,
		bool $inFooter = false)
	{
		if (!isset($this->_registeredScripts[$handle])) {
			throw new Exception('Script '.$handle.' not registered');
		}

		$this->_enqueuedScripts []= $handle;
	}


	/**
	 *
	 * @param string $handle
	 * @param string $src
	 * @param array $deps
	 * @param string $ver
	 * @param string $media
	 * @throws Exception
	 *
	 */

	public function enqueueStyle(string $handle, string $src = '', array $deps = [], $ver = false,
		string $media = 'all')
	{
		if (!isset($this->_registeredStyles[$handle])) {
			throw new Exception('Style '.$handle.' not registered');
		}

		$this->_enqueuedStyles []= $handle;
	}


	/**
	 *
	 * @param string $handle
	 * @param string $src
	 * @param array $deps
	 * @param string $ver
	 * @param bool $inFooter
	 * @return bool
	 * @throws Exception
	 *
	 */

	public function registerScript(string $handle, string $src, array $deps = [], $ver = false,
		bool $inFooter = false): bool
	{
		if (isset($this->_registeredScripts[$handle])) {
			throw new Exception('Script '.$handle.' already registered');
		}
		$this->_registeredScripts[$handle] = $this->getNewScriptArray($src, $deps, $ver, $inFooter);
		return true;
	}


	/**
	 *
	 * @param string $handle
	 * @param string $src
	 * @param array $deps
	 * @param string $ver
	 * @param string $media
	 * @return bool
	 * @throws Exception
	 *
	 */

	public function registerStyle(string $handle, string $src, array $deps = [], $ver = false,
		string $media = 'all'): bool
	{
		if (isset($this->_registeredStyles[$handle])) {
			throw new Exception('Style '.$handle.' already registered');
		}
		$this->_registeredStyles[$handle] = $this->getNewStyleArray($src, $deps, $ver, $media);
		return true;
	}


	/**
	 *
	 * @param string $src
	 * @param array $deps
	 * @param string $ver
	 * @param bool $inFooter
	 * @return array
	 *
	 */

	private function getNewScriptArray(string $src, array $deps, $ver, bool $inFooter): array
	{
		return [
			'src' => $src,
			'deps' => $deps,
			'ver' => $ver,
			'inFooter' => $inFooter,
		];
	}


	/**
	 *
	 * @param string $src
	 * @param array $deps
	 * @param string $ver
	 * @param string $media
	 * @return array
	 *
	 */

	private function getNewStyleArray(string $src, array $deps, $ver, string $media): array
	{
		return [
			'src' => $src,
			'deps' => $deps,
			'ver' => $ver,
			'media' => $media,
		];
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getRegisteredScripts(): array
	{
		return $this->_registeredScripts;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getRegisteredStyles(): array
	{
		return $this->_registeredStyles;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getEnqueuedScripts(): array
	{
		return $this->_enqueuedScripts;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getEnqueuedStyles(): array
	{
		return $this->_enqueuedStyles;
	}


	/**
	 *
	 * @param string $handle
	 * @param string $name
	 * @param array $data
	 * @throws Exception
	 *
	 */

	public function localizeScript(string $handle, string $name, array $data) {
		if (!isset($this->_registeredScripts[$handle])) {
			throw new Exception('Script '.$handle.' not registered');
		}

		$this->_localizedScript []= $handle;
	}

}
