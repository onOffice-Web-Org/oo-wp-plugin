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

use onOffice\WPlugin\Types\MapProvider;


/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class ScriptLoaderMap
	implements ScriptLoader
{
	/** @var ScriptLoaderMapFactory */
	private $_pScriptLoaderMapFactory = null;

	/** @var MapProvider */
	private $_pMapProvider = null;


	/**
	 *
	 * @param MapProvider $pMapProvider
	 * @param ScriptLoaderMapFactory $pFactory
	 *
	 */

	public function __construct(
		MapProvider $pMapProvider,
		ScriptLoaderMapFactory $pFactory)
	{
		$this->_pMapProvider = $pMapProvider;
		$this->_pScriptLoaderMapFactory = $pFactory;
	}


	/**
	 *
	 */

	public function enqueue()
	{
		$this->getSpecificMapLoader()->enqueue();
	}


	/**
	 *
	 */

	public function register()
	{
		$this->getSpecificMapLoader()->register();
	}


	/**
	 *
	 * @return ScriptLoader
	 *
	 */

	private function getSpecificMapLoader(): ScriptLoader
	{
		return $this->_pScriptLoaderMapFactory->buildForMapProvider($this->_pMapProvider);
	}
}
