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

namespace onOffice\WPlugin\EstateViewFieldModifier;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class EstateViewFieldModifierTypes
{
	/** */
	const MODIFIER_TYPE_DEFAULT = 'fixedConfTypeDefault';

	/** */
	const MODIFIER_TYPE_MAP = 'fixedConfTypeMap';


	/** @var array */
	private static $_mapping = array(
		self::MODIFIER_TYPE_DEFAULT => '\onOffice\WPlugin\EstateViewFieldModifier\EstateViewFieldModifierTypeDefault',
		self::MODIFIER_TYPE_MAP => '\onOffice\WPlugin\EstateViewFieldModifier\EstateViewFieldModifierTypeMap',
	);


	/**
	 *
	 * @return array
	 *
	 */

	public static function getMapping()
	{
		return self::$_mapping;
	}
}
