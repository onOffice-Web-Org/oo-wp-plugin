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

use onOffice\WPlugin\Utility\__String;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassString
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGetNew()
	{
		$pString = __String::getNew('Hello World');
		$this->assertInstanceOf(__String::class, $pString);
		$this->assertEquals('Hello World', $pString->__toString());
	}


	/**
	 *
	 */

	public function testGetReplace()
	{
		$pString = __String::getNew('Hello World! World is great.');
		$this->assertEquals('Hello People. World is great.', $pString->replace('World!', 'People.'));
	}


	/**
	 *
	 */

	public function testRemove()
	{
		$pString = __String::getNew('Hello World! World is great.');
		$this->assertEquals('Hll Wrld! Wrld s grt.', $pString->remove(['a', 'e', 'i', 'o']));
	}


	/**
	 *
	 */

	public function testKeep()
	{
		$pString = __String::getNew('Hello World! World is great.');
		$this->assertEquals('eo o o i ea', $pString->keep('aeio '));
	}


	/**
	 *
	 */

	public function testSplit()
	{
		$pString = __String::getNew('Schön.');
		$this->assertEquals(['S', 'c', 'h', chr(0xC3), chr(0xB6), 'n', '.'], $pString->split());
	}


	/**
	 *
	 */

	public function testLength()
	{
		$pString = __String::getNew('Schön.');
		$this->assertEquals(6, $pString->length()); // 6 because of unicode

		$pStringZh = __String::getNew('你好');
		$this->assertEquals(2, $pStringZh->length());
	}


	/**
	 *
	 */

	public function testIsEmpty()
	{
		$this->assertTrue(__String::getNew('')->isEmpty());
		$this->assertFalse(__String::getNew('Hi')->isEmpty());
	}


	/**
	 *
	 */

	public function testEndsWith()
	{
		$pString = __String::getNew('Knusper knupser Knäuschen, wer knuspert an meinem Häuschen?');
		$this->assertTrue($pString->endsWith('Häuschen?'));
		$this->assertTrue($pString->endsWith('?'));
		$this->assertFalse($pString->endsWith('Häuschen'));
	}


	/**
	 *
	 */

	public function testStartsWith()
	{
		$pString = __String::getNew('Knusper knupser Knäuschen, wer knuspert an meinem Häuschen?');
		$this->assertTrue($pString->startsWith('Knusper'));
		$this->assertFalse($pString->startsWith('knusper'));
		$this->assertFalse($pString->startsWith(''));
	}


	/**
	 *
	 */

	public function testSub()
	{
		$pString = __String::getNew('Knusper knupser Knäuschen, wer knuspert an meinem Häuschen?');
		$this->assertEquals('wer knuspert an meinem Häuschen?', $pString->sub(27));
		$this->assertEquals('wer', $pString->sub(27, 3));
	}


	/**
	 *
	 */

	public function testContains()
	{
		$pString = __String::getNew('Knusper knupser Knäuschen, wer knuspert an meinem Häuschen?');
		$this->assertTrue($pString->contains('Knäuschen'));
		$this->assertFalse($pString->contains('Knäuschen', 27));
		$this->assertTrue($pString->contains('Häuschen', 27));
	}


	/**
	 *
	 */

	public function testBinLength()
	{
		$pString = __String::getNew('Schön.');
		$this->assertEquals(7, $pString->binLength());

		$pStringZh = __String::getNew('你好');
		$this->assertEquals(6, $pStringZh->binLength());
	}


	/**
	 *
	 */

	public function testToString()
	{
		$string = 'asdfghjkl;öäüßÖÄÜẞ';
		$this->assertEquals($string, (string)__String::getNew($string));
	}
}
