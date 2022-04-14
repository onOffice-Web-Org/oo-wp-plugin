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

use onOffice\WPlugin\ArrayContainerEscape;
use onOffice\WPlugin\Escape;
use WP_UnitTestCase;
use function esc_js;
use function esc_url;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassArrayContainerEscape
	extends WP_UnitTestCase
{
	/**
	 *
	 *
	 */

	public function testEmpty()
	{
		$pArrayContainerEscape = new ArrayContainerEscape([]);
		$this->assertEmpty($pArrayContainerEscape->current());
		$this->assertEmpty($pArrayContainerEscape->getValue(8));
		$this->assertEmpty($pArrayContainerEscape->getValue('bla'));
		$this->assertEmpty($pArrayContainerEscape->getValue(0, Escape::URL));
		$this->assertNull($pArrayContainerEscape->key());
		$this->assertFalse($pArrayContainerEscape->offsetExists(0));
		$this->assertFalse($pArrayContainerEscape->offsetExists(1));
		$this->assertFalse($pArrayContainerEscape->offsetExists('asdf'));
		$this->assertEmpty($pArrayContainerEscape->offsetGet('hello'));
		$this->assertFalse($pArrayContainerEscape->valid());
		$pArrayContainerEscape->next();
		$this->assertFalse($pArrayContainerEscape->valid());
	}


	/**
	 *
	 */

	public function testData()
	{
		$testUrl = 'http://hello.de/world ![[ test.asdf';
		$pArrayContainerEscape = new ArrayContainerEscape([
			'Bob' => 'Alice',
			'html' => '<html>"',
			3 => '42',
			'sphere' => array(
				'cube<',
				'cylinder',
			),
			'js' => '"\'"',
			'url' => $testUrl,
		]);

		$this->assertEquals('Alice', $pArrayContainerEscape->current());
		$this->assertEquals('Alice', $pArrayContainerEscape->getValue('Bob', Escape::HTML));
		$this->assertEquals('42', $pArrayContainerEscape->getValue(3, Escape::HTML));
		$this->assertTrue($pArrayContainerEscape->valid());
		$pArrayContainerEscape->next();
		$this->assertTrue($pArrayContainerEscape->valid());

		$this->assertEquals('&lt;html&gt;&quot;', $pArrayContainerEscape->current());

		// actual escaping
		$this->assertEquals('&lt;html&gt;&quot;', $pArrayContainerEscape->getValue('html'));
		$this->assertEquals('&lt;html&gt;&quot;', $pArrayContainerEscape->getValue('html', Escape::ATTR));
		$this->assertEquals('&lt;html&gt;&quot;', $pArrayContainerEscape->getValue('html', Escape::TEXTAREA));
		$this->assertEquals(esc_js('"\'"'), $pArrayContainerEscape->getValue('js', Escape::JS));
		$this->assertEquals(esc_url($testUrl), $pArrayContainerEscape->getValue('url', Escape::URL));
		$this->assertEquals('&lt;html&gt;&quot;', $pArrayContainerEscape->offsetGet('html'));
		$this->assertEquals(['cube<', 'cylinder'], $pArrayContainerEscape->getValueRaw('sphere'));
		$this->assertEquals(['cube&lt;', 'cylinder'], $pArrayContainerEscape['sphere']);
		$this->assertEquals(['cube&lt;', 'cylinder'], $pArrayContainerEscape->getValue('sphere'));

		$pArrayContainerEscape->offsetSet('foo', 'bar');
		$this->assertEquals('bar', $pArrayContainerEscape->offsetGet('foo'));
		$pArrayContainerEscape->offsetSet(null, 'baz');
		$this->assertEquals('baz', $pArrayContainerEscape->offsetGet(4));
		$pArrayContainerEscape->rewind();
		$this->assertEquals('Alice', $pArrayContainerEscape->current());
		$this->assertEquals('Bob', $pArrayContainerEscape->key());
		$this->assertEquals('<html>"', $pArrayContainerEscape->getValueRaw('html'));

	}


	/**
	 *
	 */

	public function testIterator()
	{
		$pArrayContainerEscape = new ArrayContainerEscape([
			'html' => '<script>"',
			'test' => 'other',
			'anArray' => [
				'value1<',
				'value2',
			],
		]);

		$expected = [
			'html' => '&lt;script&gt;&quot;',
			'test' => 'other',
			'anArray' => [
				'value1&lt;',
				'value2',
			]
		];

		foreach ($pArrayContainerEscape as $key => $value) {
			$this->assertNotEmpty($key);
			$this->assertEquals($expected[$key], $value);
		}
	}


	public function testException()
	{
		$this->expectException(\Exception::class);
		$pArrayContainerEscape = new ArrayContainerEscape([
			'asd' => 'hello',
		]);

		// must throw an \Exception
		$pArrayContainerEscape->getValue('asd', 'NoSuchEscaping');
	}
}
