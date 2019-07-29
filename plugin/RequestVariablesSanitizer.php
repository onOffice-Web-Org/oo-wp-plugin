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

namespace onOffice\WPlugin;


/**
 *
 */

class RequestVariablesSanitizer
{
	/**
	 *
	 * @param string $name
	 * @param int $filter
	 * @param int $option
	 * @return mixed
	 *
	 */

	public function getFilteredGet(string $name, int $filter = FILTER_DEFAULT, int $option = null)
	{
		return $this->getFiltered($_GET, $name, $filter, $option);
	}


	/**
	 *
	 * @param string $name
	 * @param int $filter
	 * @param int $option
	 * @return mixed
	 *
	 */

	public function getFilteredPost(string $name, int $filter = FILTER_DEFAULT, int $option = null)
	{
		return $this->getFiltered($_POST, $name, $filter, $option);
	}


	/**
	 *
	 * @param array $inputVariable
	 * @param string $name
	 * @param int $filter
	 * @param int $option
	 * @return mixed
	 *
	 */

	private function getFiltered(array $inputVariable, string $name, int $filter, int $option = null)
	{
		return filter_var($inputVariable[$name] ?? null, $filter, $option);
	}
}