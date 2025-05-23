<?php

/**
 *
 *    Copyright (C) 2025 onOffice GmbH
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

declare(strict_types=1);

namespace onOffice\WPlugin\Filter;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2025, onOffice(R) GmbH
 *
 */

class DefaultFilterBuilderDetailViewAddress
	implements DefaultFilterBuilder
{

	/** @var array */
	private $_defaultFilter = [
		'homepage_veroeffentlichen' => [
			['op' => '=', 'val' => 1],
		],
	];


	/**
	 *
	 * @return array
	 *
	 */

	public function buildFilter(): array
	{
		$filter = $this->_defaultFilter;
		return $filter;
	}


	/**
	 * @return array
	 */
	public function getDefaultFilter(): array
	{
		return $this->buildFilter();
	}
}
