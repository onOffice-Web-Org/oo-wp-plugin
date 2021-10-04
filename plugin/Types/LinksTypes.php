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

declare (strict_types=1);

namespace onOffice\WPlugin\Types;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

abstract class LinksTypes
{
	/** */
	const LINKS_DEACTIVATED = 'deactivated';

	/** */
	const LINKS_LINK = 'link';

	/** */
	const LINKS_EMBEDDED = 'embedded';


	/** */
	const FILE_TYPE_OGULO_LINK = 'Ogulo-Link';
	const FILE_TYPE_OBJECT_LINK = 'Objekt-Link';
	const FILE_TYPE_LINK = 'Link';

	/** @var string[] */
	private static $_linkTypes = [
		self::FILE_TYPE_OGULO_LINK,
		self::FILE_TYPE_OBJECT_LINK,
		self::FILE_TYPE_LINK,
	];


	/**
	 *
	 * @param string $type
	 * @return bool
	 *
	 */

	static public function isOguloLink(string $type): bool
	{
		return $type === self::FILE_TYPE_OGULO_LINK;
	}

	/**
	 *
	 * @param string $type
	 * @return bool
	 *
	 */
	static public function isObjectLink(string $type): bool
	{
		return $type === self::FILE_TYPE_OBJECT_LINK;
	}

	/**
	 *
	 * @param string $type
	 * @return bool
	 *
	 */
	static public function isLink(string $type): bool
	{
		return $type === self::FILE_TYPE_LINK;
	}
}
