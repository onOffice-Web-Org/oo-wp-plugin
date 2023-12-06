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

declare (strict_types=1);

namespace onOffice\tests;

use onOffice\WPlugin\Gui\DateTimeFormatter;

class TestClassDateTimeFormatter
	extends WP_UnitTest_Localized
{
	public function testShortDate()
	{
		$this->switchLocale('en_US');
		$timestamp = $this->getTimestamp();
		$pDateTimeFormatter = new DateTimeFormatter();

		$format = DateTimeFormatter::SHORT|DateTimeFormatter::ADD_DATE;
		$result = $pDateTimeFormatter->formatByTimestamp($format, $timestamp);
		$this->assertEquals('2018-06-21', $result);
	}

	public function testShortTime()
	{
		$this->switchLocale('en_US');
		$timestamp = $this->getTimestamp();
		$pDateTimeFormatter = new DateTimeFormatter();

		$format = DateTimeFormatter::SHORT|DateTimeFormatter::ADD_TIME;
		$result = $pDateTimeFormatter->formatByTimestamp($format, $timestamp);
		$this->assertEquals('2:25:37 pm', $result);
	}

	public function testShortDateTime()
	{
		$this->switchLocale('en_US');
		$timestamp = $this->getTimestamp();
		$pDateTimeFormatter = new DateTimeFormatter();

		$format = DateTimeFormatter::SHORT|DateTimeFormatter::ADD_TIME|DateTimeFormatter::ADD_DATE;
		$result = $pDateTimeFormatter->formatByTimestamp($format, $timestamp);
		$this->assertEquals('2018/06/21 2:25:37 pm', $result);
	}

	/**
	 * Change this, when implementing long date
	 */
	public function testException()
	{
		$this->expectException(\Exception::class);
		$timestamp = $this->getTimestamp();
		$pDateTimeFormatter = new DateTimeFormatter();

		$format = DateTimeFormatter::ADD_TIME|DateTimeFormatter::ADD_DATE;
		$pDateTimeFormatter->formatByTimestamp($format, $timestamp);
	}

	/**
	 * @return int
	 * @throws \Exception
	 */
	private function getTimestamp(): int
	{
		$pDateTime = new \DateTime('2018-06-21 14:25:37');
		return $pDateTime->getTimestamp();
	}
}
