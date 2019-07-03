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

namespace onOffice\WPlugin\Controller;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 * Interface of classes that hold the dependencies of EstateListInputVariableReader
 *
 */

interface InputVariableReaderConfig
{
	/**
	 *
	 * @param string $field
	 * @param string $module
	 * @return string
	 *
	 */

	public function getFieldType(string $field, string $module): string;


	/**
	 *
	 * @return string such as Europe/Berlin
	 *
	 */

	public function getTimezoneString(): string;


	/**
	 *
	 * @param int $var
	 * @param string $name
	 * @param int $sanitizer
	 * @return array
	 *
	 */

	public function getFilterVariable(int $var, string $name, int $sanitizer);


	/**
	 *
	 * @param string $name
	 * @return bool
	 *
	 */

	public function getIsRequestVarArray(string $name): bool;
}
