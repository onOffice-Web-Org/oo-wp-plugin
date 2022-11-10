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

namespace onOffice\WPlugin\API;
use onOffice\WPlugin\Controller\Exception\ExceptionPrettyPrintable;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class APIEmptyResultException
	extends ApiClientException
	implements ExceptionPrettyPrintable
{
	/**
	 *
	 * @return string
	 *
	 */

	public function printFormatted(): string
	{
		return __('The onOffice plugin has an unexpected problem when trying to reach the onOffice API.', 'onoffice-for-wp-websites');
	}
}
