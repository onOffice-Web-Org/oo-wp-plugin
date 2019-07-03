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

namespace onOffice\WPlugin\Types;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class ImageTypes
{

	/** */
	const TITLE = 'Titelbild';

	/** */
	const PHOTO = 'Foto';

	/** */
	const PHOTO_BIG = 'Foto_gross';

	/** */
	const GROUNDPLAN = 'Grundriss';

	/** */
	const PANORAMA = 'Panorama';

	/** */
	const LOCATION_MAP = 'Lageplan';

	/** */
	const ENERGY_PASS_RANGE = 'Epass_Skala';

	/** @var array */
	static private $_imageTypes = array
		(
			self::TITLE => 'Cover Photo',
			self::PHOTO => 'Photo',
			self::PHOTO_BIG => 'Photo (big)',
			self::PANORAMA => 'Panorama',
			self::GROUNDPLAN => 'Ground Plan',
			self::LOCATION_MAP => 'Location Map',
			self::ENERGY_PASS_RANGE => 'Energy-Pass Range',
		);


	/**
	 *
	 * @param string $type
	 * @return bool
	 *
	 */

	static public function isImageType(string $type): bool
	{
		return isset(self::$_imageTypes[$type]);
	}

	/** @return array */
	static public function getAllImageTypes(): array
		{ return self::$_imageTypes; }
}