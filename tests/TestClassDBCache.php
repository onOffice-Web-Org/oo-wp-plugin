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

use onOffice\WPlugin\Cache\DBCache;
use ReflectionMethod;
use WP_UnitTestCase;


/**
 *
 * @covers onOffice\WPlugin\Cache\DBCache
 *
 */

class TestClassDBCache
	extends WP_UnitTestCase
{
	/**
	 * Different filters should produce different cache keys.
	 * Before the fix, only the Id filter was included in the hash.
	 */
	public function testGetParametersHashed_differentFiltersDifferentKeys()
	{
		$pDBCache = new DBCache(['ttl' => 3600]);
		$method = new ReflectionMethod(DBCache::class, 'getParametersHashed');
		$method->setAccessible(true);

		$paramsA = [
			'parameters' => [
				'listname' => 'test',
				'formatoutput' => true,
				'outputlanguage' => 'ENG',
				'filter' => [
					'kaufpreis' => [['op' => '>=', 'val' => 100000]],
				],
			],
		];

		$paramsB = [
			'parameters' => [
				'listname' => 'test',
				'formatoutput' => true,
				'outputlanguage' => 'ENG',
				'filter' => [
					'kaufpreis' => [['op' => '>=', 'val' => 200000]],
				],
			],
		];

		$keyA = $method->invoke($pDBCache, $paramsA);
		$keyB = $method->invoke($pDBCache, $paramsB);

		$this->assertNotEquals($keyA, $keyB);
	}


	/**
	 * Same listname, formatoutput, language, and filter should produce the same cache key.
	 */
	public function testGetParametersHashed_sameParamsSameKey()
	{
		$pDBCache = new DBCache(['ttl' => 3600]);
		$method = new ReflectionMethod(DBCache::class, 'getParametersHashed');
		$method->setAccessible(true);

		$params = [
			'parameters' => [
				'listname' => 'test',
				'formatoutput' => true,
				'outputlanguage' => 'ENG',
				'filter' => [
					'kaufpreis' => [['op' => '>=', 'val' => 100000]],
				],
			],
		];

		$key1 = $method->invoke($pDBCache, $params);
		$key2 = $method->invoke($pDBCache, $params);

		$this->assertEquals($key1, $key2);
	}


	/**
	 * Adding a geo filter should change the cache key.
	 */
	public function testGetParametersHashed_geoFilterChangesKey()
	{
		$pDBCache = new DBCache(['ttl' => 3600]);
		$method = new ReflectionMethod(DBCache::class, 'getParametersHashed');
		$method->setAccessible(true);

		$paramsWithoutGeo = [
			'parameters' => [
				'listname' => 'test',
				'formatoutput' => true,
				'outputlanguage' => 'ENG',
				'filter' => [
					'kaufpreis' => [['op' => '>=', 'val' => 100000]],
				],
			],
		];

		$paramsWithGeo = [
			'parameters' => [
				'listname' => 'test',
				'formatoutput' => true,
				'outputlanguage' => 'ENG',
				'filter' => [
					'kaufpreis' => [['op' => '>=', 'val' => 100000]],
					'geo' => [['op' => 'geo', 'val' => 10, 'loc' => '7.0,51.0']],
				],
			],
		];

		$keyWithoutGeo = $method->invoke($pDBCache, $paramsWithoutGeo);
		$keyWithGeo = $method->invoke($pDBCache, $paramsWithGeo);

		$this->assertNotEquals($keyWithoutGeo, $keyWithGeo);
	}


	/**
	 * Empty filter array should produce a different key than a populated filter.
	 */
	public function testGetParametersHashed_emptyFilterDifferentFromPopulatedFilter()
	{
		$pDBCache = new DBCache(['ttl' => 3600]);
		$method = new ReflectionMethod(DBCache::class, 'getParametersHashed');
		$method->setAccessible(true);

		$paramsEmptyFilter = [
			'parameters' => [
				'listname' => 'test',
				'formatoutput' => true,
				'outputlanguage' => 'ENG',
				'filter' => [],
			],
		];

		$paramsPopulatedFilter = [
			'parameters' => [
				'listname' => 'test',
				'formatoutput' => true,
				'outputlanguage' => 'ENG',
				'filter' => [
					'kaufpreis' => [['op' => '>=', 'val' => 100000]],
				],
			],
		];

		$keyEmpty = $method->invoke($pDBCache, $paramsEmptyFilter);
		$keyPopulated = $method->invoke($pDBCache, $paramsPopulatedFilter);

		$this->assertNotEquals($keyEmpty, $keyPopulated);
	}


	/**
	 * Without listname, falls back to full serialization.
	 */
	public function testGetParametersHashed_withoutListnameUsesFullSerialization()
	{
		$pDBCache = new DBCache(['ttl' => 3600]);
		$method = new ReflectionMethod(DBCache::class, 'getParametersHashed');
		$method->setAccessible(true);

		$params = [
			'parameters' => [
				'formatoutput' => true,
				'outputlanguage' => 'ENG',
			],
		];

		$key = $method->invoke($pDBCache, $params);

		$this->assertNotEmpty($key);
		$this->assertIsString($key);
		$this->assertEquals(32, strlen($key));
	}
}