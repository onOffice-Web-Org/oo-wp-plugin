<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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

namespace onOffice\WPlugin\Filter;

use Exception;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class DefaultFilterBuilderDetailView
	implements DefaultFilterBuilder
{
	/** @var int */
	private $_estateId = null;


	/**
	 *
	 * @return array
	 *
	 */

	public function buildFilter(): array
	{
		if ($this->_estateId === null) {
			throw new Exception('EstateId must not be null');
		}

		return [
			'veroeffentlichen' => [
				['op' => '=', 'val' => 1],
			],
			'Id' => [
				['op' => '=', 'val' => $this->_estateId],
			],
		];
	}

	/** @param int $estateId */
	public function setEstateId(int $estateId)
		{ $this->_estateId = $estateId; }
}
