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

namespace onOffice\WPlugin\Types;

use onOffice\WPlugin\WP\WPOptionWrapperBase;
use onOffice\WPlugin\WP\WPOptionWrapperDefault;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class MapProvider
{
	/** */
	const OPEN_STREET_MAPS = 'osm';

	/** */
	const GOOGLE_MAPS = 'google-maps';

	/** */
	const PROVIDER_DEFAULT = self::OPEN_STREET_MAPS;


	/** @var WPOptionWrapperBase */
	private $_pOptionsWrapper = null;


	/**
	 *
	 * @param WPOptionWrapperBase $pWPOptionsWrapper
	 *
	 */

	public function __construct(WPOptionWrapperBase $pWPOptionsWrapper = null)
	{
		$this->_pOptionsWrapper = $pWPOptionsWrapper ?? new WPOptionWrapperDefault();
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getActiveMapProvider(): string
	{
		return $this->_pOptionsWrapper->getOption
			('onoffice-maps-mapprovider', self::PROVIDER_DEFAULT);
	}


	/**
	 *
	 * @return string
	 *
	 */

	static public function getStaticActiveMapProvider(): string
	{
		return get_option('onoffice-maps-mapprovider', false);
	}
}
