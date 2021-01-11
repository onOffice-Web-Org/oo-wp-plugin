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
namespace onOffice\tests;

use onOffice\WPlugin\Factory\DateTimeImmutableFactory;
use DateTimeImmutable;
use WP_UnitTestCase;

class TestClassDateTimeImmutableFactory
	extends WP_UnitTestCase
{
	public function testCreate()
	{
		$pFactory = new DateTimeImmutableFactory();

		$this->assertInstanceOf(DateTimeImmutable::class, $pFactory->create());
	}
}