<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

use DI\Container;
use onOffice\WPlugin\Types\MapProvider;


/**
 *
 */

class ScriptLoaderMapFactory
{
	/** */
	const TYPE_MAPPING = [
		MapProvider::GOOGLE_MAPS => ScriptLoaderMapGoogleMaps::class,
		MapProvider::OPEN_STREET_MAPS => ScriptLoaderMapOsm::class,
	];

	/** @var Container */
	private $_pContainer = null;



	/**
	 *
	 * @param Container $pContainer
	 *
	 */

	public function __construct(Container $pContainer)
	{
		$this->_pContainer = $pContainer;
	}


	/**
	 *
	 * @param MapProvider $pMapProvider
	 * @return ScriptLoader
	 *
	 */

	public function buildForMapProvider(MapProvider $pMapProvider): ScriptLoader
	{
		$type = $pMapProvider->getActiveMapProvider();
		$class = self::TYPE_MAPPING[$type] ?? self::TYPE_MAPPING[MapProvider::PROVIDER_DEFAULT];
		return $this->_pContainer->get($class);
	}
}
