<?php
/**
 *
 *    Copyright (C) 2026 onOffice GmbH
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

use onOffice\WPlugin\Utility\UrlHelper;
use WP_UnitTestCase;

class TestClassUrlHelper
	extends WP_UnitTestCase
{
	/**
	 * @return array
	 */
	public function dataSlugs(): array
	{
		return [
			'square meters' => ['Wohnung 120 m²', 'wohnung-120-m2'],
			'cubic meters' => ['Halle 500 m³', 'halle-500-m3'],
			'fraction' => ['Haus ½ Etage', 'haus-1-2-etage'],
			'spaces' => ['  Haus   am  See  ', 'haus-am-see'],
			'degree sign' => ['Penthouse 360° Blick', 'penthouse-360-blick'],
			'section sign' => ['Objekt § 34c', 'objekt-34c'],
			'quotes and dashes' => ['„Villa“ – Toplage', 'villa-toplage'],
			'accents' => ['Café à côté', 'cafe-a-cote'],
			'micro sign' => ['µ-Apartment', 'u-apartment'],
			'numero sign' => ['№ 7 Loft', 'nr-7-loft'],
			'non latin only' => ['мой дом', ''],
			'mixed non latin' => ['Loft мой', 'loft'],
			'already encoded' => ['Wohnung m%C2%B2', 'wohnung-m'],
			'empty' => ['', ''],
		];
	}

	/**
	 * @dataProvider dataSlugs
	 * @covers \onOffice\WPlugin\Utility\UrlHelper::sanitizeTitleToSlug
	 *
	 * @param string $title
	 * @param string $expectedSlug
	 */
	public function testSanitizeTitleToSlug(string $title, string $expectedSlug)
	{
		$this->assertSame($expectedSlug, UrlHelper::sanitizeTitleToSlug($title));
	}

	/**
	 * @dataProvider dataSlugs
	 * @covers \onOffice\WPlugin\Utility\UrlHelper::sanitizeTitleToSlug
	 *
	 * @param string $title
	 */
	public function testSlugNeverContainsPercentSign(string $title)
	{
		$this->assertStringNotContainsString('%', UrlHelper::sanitizeTitleToSlug($title));
		$this->assertStringNotContainsString('%', UrlHelper::sanitizeTitleToSlug($title, 'de_DE'));
	}

	/**
	 * @covers \onOffice\WPlugin\Utility\UrlHelper::sanitizeTitleToSlug
	 */
	public function testSanitizeTitleToSlugUsesGermanUmlautRulesForGermanLocale()
	{
		$this->assertSame('schoene-wohnung', UrlHelper::sanitizeTitleToSlug('Schöne Wohnung', 'de_DE'));
		$this->assertSame('grosse-strasse', UrlHelper::sanitizeTitleToSlug('Große Straße', 'de_DE'));
		$this->assertSame('buero-120-m2', UrlHelper::sanitizeTitleToSlug('Büro 120 m²', 'de_DE'));
	}

	/**
	 * @covers \onOffice\WPlugin\Utility\UrlHelper::sanitizeTitleToSlug
	 */
	public function testSanitizeTitleToSlugIsStableWhenAppliedTwice()
	{
		$slug = UrlHelper::sanitizeTitleToSlug('Wohnung 120 m² mit „Südbalkon“');
		$this->assertSame($slug, UrlHelper::sanitizeTitleToSlug($slug));
	}

	/**
	 * @covers \onOffice\WPlugin\Utility\UrlHelper::removePercentEncodedOctets
	 */
	public function testRemovePercentEncodedOctets()
	{
		$this->assertSame('wohnung-120-m', UrlHelper::removePercentEncodedOctets('wohnung-120-m%c2%b2'));
		$this->assertSame('', UrlHelper::removePercentEncodedOctets('%d0%bc%d0%be%d0%b9-%d0%b4%d0%be%d0%bc'));
		$this->assertSame('plain-slug', UrlHelper::removePercentEncodedOctets('plain-slug'));
	}

	/**
	 * @return array
	 */
	public function dataSameLocations(): array
	{
		return [
			'identical' => [
				'https://example.org/immobilien/123-wohnung-m2',
				'https://example.org/immobilien/123-wohnung-m2',
			],
			'trailing slash' => [
				'https://example.org/immobilien/123-wohnung-m2',
				'https://example.org/immobilien/123-wohnung-m2/',
			],
			'encoded versus decoded' => [
				'https://example.org/immobilien/123-wohnung-m%c2%b2',
				'https://example.org/immobilien/123-wohnung-m²',
			],
			'double encoded by the web server' => [
				'https://example.org/immobilien/123-wohnung-m%c2%b2',
				'https://example.org/immobilien/123-wohnung-m%25c2%25b2',
			],
			'upper case octets' => [
				'https://example.org/immobilien/123-wohnung-m%c2%b2',
				'https://example.org/immobilien/123-wohnung-m%C2%B2',
			],
			'encoded space' => [
				'https://example.org/immobilien/123-haus am see',
				'https://example.org/immobilien/123-haus%20am%20see',
			],
		];
	}

	/**
	 * @dataProvider dataSameLocations
	 * @covers \onOffice\WPlugin\Utility\UrlHelper::isSameLocation
	 *
	 * @param string $url
	 * @param string $otherUrl
	 */
	public function testIsSameLocation(string $url, string $otherUrl)
	{
		$this->assertTrue(UrlHelper::isSameLocation($url, $otherUrl));
		$this->assertTrue(UrlHelper::isSameLocation($otherUrl, $url));
	}

	/**
	 * @covers \onOffice\WPlugin\Utility\UrlHelper::isSameLocation
	 */
	public function testIsSameLocationRecognizesDifferentLocations()
	{
		$this->assertFalse(UrlHelper::isSameLocation(
			'https://example.org/immobilien/123-wohnung-m2',
			'https://example.org/immobilien/124-wohnung-m2'
		));
		$this->assertFalse(UrlHelper::isSameLocation(
			'https://example.org/immobilien/123',
			'https://example.org/immobilien/123-wohnung-m2'
		));
	}
}
