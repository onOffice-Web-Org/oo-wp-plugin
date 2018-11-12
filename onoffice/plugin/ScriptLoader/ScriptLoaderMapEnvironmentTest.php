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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class ScriptLoaderMapEnvironmentTest
	implements ScriptLoaderMapEnvironment
{
	/** @var string */
	private $_mapProvider = MapProvider::PROVIDER_DEFAULT;


	/**
	 *
	 * @return string
	 *
	 */

	public function getMapProvider(): string
	{
		return $this->_mapProvider;
	}


	/**
	 *
	 * @param string $mapProvider
	 *
	 */

	public function setMapProvider(string $mapProvider)
	{
		$this->_mapProvider = $mapProvider;
	}
}
