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

declare(strict_types=1);

namespace onOffice\WPlugin\DataView;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 * Interface for list views with filterable fields
 *
 */

interface DataViewFilterableFields
{
	/**
	 *
	 * @return int The ID of the listview
	 *
	 */

	public function getId(): int;


	/**
	 *
	 * @return string Module name
	 *
	 */

	public function getModule(): string;


	/**
	 *
	 * @return array An array fields as string
	 *
	 */

	public function getFilterableFields(): array;


	/**
	 *
	 * @return array An array fields as string
	 *
	 */

	public function getHiddenFields(): array;
}
