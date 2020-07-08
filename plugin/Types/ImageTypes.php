<?php

/**
 *
 *    Copyright (C) 2020 onOffice GmbH
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

class ImageTypes
{
	const TITLE = 'Titelbild';
	const PHOTO = 'Foto';
	const PHOTO_BIG = 'Foto_gross';
	const GROUNDPLAN = 'Grundriss';
	const PANORAMA = 'Panorama';
	const LOCATION_MAP = 'Lageplan';
	const ENERGY_PASS_RANGE = 'Epass_Skala';

	const IMAGE_TYPES = [
		self::TITLE,
		self::PHOTO,
		self::PHOTO_BIG,
		self::PANORAMA,
		self::GROUNDPLAN,
		self::LOCATION_MAP,
		self::ENERGY_PASS_RANGE,
	];

	/**
	 * @param string $type
	 * @return bool
	 */
	public static function isImageType(string $type): bool
	{
		return in_array($type, self::IMAGE_TYPES, true);
	}

	/**
	 * @return array
	 */
	public static function getAllImageTypesTranslated(): array
	{
		return [
			self::TITLE => __('Cover Photo', 'onoffice'),
			self::PHOTO => __('Photo', 'onoffice'),
			self::PHOTO_BIG => __('Photo (big)', 'onoffice'),
			self::PANORAMA => __('Panorama', 'onoffice'),
			self::GROUNDPLAN => __('Ground Plan', 'onoffice'),
			self::LOCATION_MAP => __('Location Map', 'onoffice'),
			self::ENERGY_PASS_RANGE => __('Energy-Pass Range', 'onoffice'),
		];
	}
}