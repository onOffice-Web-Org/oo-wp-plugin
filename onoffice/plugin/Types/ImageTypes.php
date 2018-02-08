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

namespace onOffice\WPlugin\Types;

/**
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
	const LOCATION_MAP = 'Lageplan';

	/** */
	const CITY_MAP = 'Stadtplan';

	/** */
	const ENERGY_PASS_RANGE = 'Epass_Skala';

	/** */
	const FINANCE_EXAMPLE = 'Finanzierungsbeispiel';

	/** @var array */
	static private $_imageTypes = array
		(
			self::TITLE => 'Cover Photo',
			self::PHOTO => 'Photo',
			self::PHOTO_BIG => 'Photo (big)',
			self::GROUNDPLAN => 'Ground Plan',
			self::LOCATION_MAP => 'Location Map',
			self::CITY_MAP => 'City Map',
			self::ENERGY_PASS_RANGE => 'Energy-Pass Range',
			self::FINANCE_EXAMPLE => 'Finance Example',
		);


	/**
	 *
	 * @param string $type
	 * @return bool
	 *
	 */

	static public function isImageType($type)
	{
		return isset(self::$_imageTypes[$type]);
	}

	/** @return array */
	static public function getAllImageTypes()
		{ return self::$_imageTypes; }
}