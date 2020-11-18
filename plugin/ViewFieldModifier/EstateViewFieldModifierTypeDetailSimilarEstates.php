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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class EstateViewFieldModifierTypeDetailSimilarEstates
	implements ViewFieldModifierTypeBase
{
	/** */
	const NEW_FIELDS = [
		'laengengrad',
		'breitengrad',
		'plz',
        'status2',
		'vermarktungsart',
		'objektart',
		'strasse',
		'land',
		'Id',
	];

	/** @var array */
	private $_viewFields = [];


	/**
	 *
	 * @param array $viewFields
	 *
	 */

	public function __construct(array $viewFields)
	{
		$this->_viewFields = $viewFields;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getAPIFields(): array
	{
		return array_unique(array_merge($this->_viewFields, self::NEW_FIELDS));
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getVisibleFields(): array
	{
		return $this->getAPIFields();
	}


	/**
	 *
	 * @param array $record
	 * @return array
	 *
	 */

	public function reduceRecord(array $record): array
	{
		return $record;
	}
}
