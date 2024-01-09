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
	 * @param int|array $option
	 * @return mixed
	 *
	 */

	public function getFilteredGet(string $name, int $filter = FILTER_DEFAULT, $option = null)
	{
		return $this->getFiltered($_GET, $name, $filter, $option);
	}

	/**
	 * @return array
	 */

	public function getParameterPage()
	{
		$pagedData = [];
		foreach ($_GET as $key => $value) {
			if (strpos($key, 'page_of_id_') === 0) {
				$pagedData[] = $key;
			}
		}
		return $pagedData;
	}

	/**
	 *
	 * @param string $name
	 * @param int $filter
	 * @param int|array $option
	 * @return mixed
	 *
	 */

	public function getFilteredPost(string $name, int $filter = FILTER_DEFAULT, $option = null)
	{
		return $this->getFiltered($_POST, $name, $filter, $option);
	}


	/**
	 *
	 * @param array $inputVariable
	 * @param string $name
	 * @param int $filter
	 * @param int|array $option
	 * @return mixed
	 *
	 */

	private function getFiltered(array $inputVariable, string $name, int $filter, $option = null)
	{
		$variable = stripslashes_deep($inputVariable[$name] ?? null);
		return filter_var($variable, $filter, $option);
	}
}