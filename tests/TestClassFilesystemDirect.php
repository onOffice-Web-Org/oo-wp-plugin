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

declare (strict_types=1);

namespace onOffice\tests;

use onOffice\WPlugin\Filesystem\FilesystemDirect;
use WP_UnitTestCase;


/**
 *
 */

class TestClassFilesystemDirect
	extends WP_UnitTestCase
{
	/**
	 *
	 * @covers onOffice\WPlugin\Filesystem\FilesystemDirect::getContents
	 *
	 */

	public function testGetContents()
	{
		$pFilesystemDirect = new FilesystemDirect();
		$result = $pFilesystemDirect->getContents(__DIR__.'/resources/Filesystem/FilesystemDirectTest.txt');
		$this->assertEquals('Hello, World!', $result);
	}


	/**
	 *
	 * @expectedException \onOffice\WPlugin\Filesystem\FilesystemException
	 * @expectedExceptionMessage File not found or not readable
	 *
	 */

	public function testGetContentsFail()
	{
		$pFilesystemDirect = new FilesystemDirect();
		$pFilesystemDirect->getContents(__DIR__.'/resources/Filesystem/doesNotExist.txt');
	}
}