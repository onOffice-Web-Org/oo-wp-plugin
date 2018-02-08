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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

abstract class MovieLinkTypes
{
	/** */
	const MOVIE_LINKS_NONE = 0;

	/** */
	const MOVIE_LINKS_LINK = 1;

	/** */
	const MOVIE_LINKS_PLAYER = 2;


	/** */
	const FILE_TYPE_MOVIE_LINK = 'Film-Link';

	/** @var string[] */
	private static $_movieLinkTypes = array(
		self::FILE_TYPE_MOVIE_LINK,
	);


	/**
	 *
	 * @param string $type
	 * @return bool
	 *
	 */

	static public function isMovieLink($type)
	{
		return in_array($type, self::$_movieLinkTypes, true);
	}
}
