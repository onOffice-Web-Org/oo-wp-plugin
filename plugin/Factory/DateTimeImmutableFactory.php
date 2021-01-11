<?php
/**
 *
 *    Copyright (C) 2020 onOffice GmbH
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

declare (strict_types=1);

namespace onOffice\WPlugin\Factory;

use DateTimeImmutable;
use DateTimeZone;

class DateTimeImmutableFactory
{
	/**
	 * @param string $time
	 * @param DateTimeZone|null $pDateTimeZone
	 * @return DateTimeImmutable
	 * @throws \Exception
	 */
	public function create(string $time = '', DateTimeZone $pDateTimeZone = null): DateTimeImmutable
	{
		$pDateTimeImmutable = new DateTimeImmutable();

		if (version_compare(PHP_VERSION, '7.1') >= 0) {
			$pDateTimeImmutable = new DateTimeImmutable($time, $pDateTimeZone);
		}

		return $pDateTimeImmutable;
	}


}