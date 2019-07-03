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

namespace onOffice\WPlugin\WP;

use function add_option;
use function delete_option;
use function get_option;
use function update_option;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class WPOptionWrapperDefault
	implements WPOptionWrapperBase
{
	/**
	 *
	 * @param string $option
	 * @param mixed $value
	 * @param bool $autoload
	 * @return bool
	 *
	 */

	public function addOption(string $option, $value = '', bool $autoload = true): bool
	{
		return add_option($option, $value, '', $autoload);
	}


	/**
	 *
	 * @param string $option
	 * @param mixed $default
	 * @return mixed
	 *
	 */

	public function getOption(string $option, $default = false)
	{
		return get_option($option, $default);
	}


	/**
	 *
	 * @param string $option
	 * @param mixed $value
	 * @param bool $autoload
	 * @return bool
	 *
	 */

	public function updateOption(string $option, $value, bool $autoload = null): bool
	{
		return update_option($option, $value, $autoload);
	}


	/**
	 *
	 * @param string $option
	 * @return bool
	 *
	 */

	public function deleteOption(string $option): bool
	{
		return delete_option($option);
	}
}
