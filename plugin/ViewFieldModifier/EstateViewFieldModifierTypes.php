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

namespace onOffice\WPlugin\ViewFieldModifier;

use onOffice\WPlugin\GeoPosition;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class EstateViewFieldModifierTypes
	implements ViewFieldModifierTypes
{
	/** */
	const MODIFIER_TYPE_DEFAULT = 'modifierTypeDefault';

	/** */
	const MODIFIER_TYPE_MAP = 'modifierTypeMap';

	/** */
	const MODIFIER_TYPE_TITLE = 'modifierTypeTitle';

	/** */
	const MODIFIER_TYPE_DETAIL_SIMILAR_ESTATES = 'modifierTypeDetailSimilarEstates';


	/** @var array */
	private $_mapping = [
		self::MODIFIER_TYPE_DEFAULT => EstateViewFieldModifierTypeDefault::class,
		self::MODIFIER_TYPE_MAP => EstateViewFieldModifierTypeMap::class,
		self::MODIFIER_TYPE_TITLE => EstateViewFieldModifierTypeTitle::class,
		self::MODIFIER_TYPE_DETAIL_SIMILAR_ESTATES => EstateViewFieldModifierTypeDetailSimilarEstates::class,
	];


	/**
	 *
	 * @return array
	 *
	 */

	public function getMapping(): array
	{
		return $this->_mapping;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getForbiddenAPIFields(): array
	{
		return [GeoPosition::FIELD_GEO_POSITION];
	}
}
