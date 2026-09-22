<?php

/**
 *
 *    Copyright (C) 2025 onOffice GmbH
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

use ReflectionClass;
use onOffice\WPlugin\Controller\EstateViewSimilarEstates;
use onOffice\WPlugin\Controller\EstateViewSimilarEstatesEnvironmentTest;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataViewSimilarEstates;
use WP_UnitTestCase;

/**
 * Similar estates must be found in every language.
 *
 * The formatted API output returns objektart/vermarktungsart translated into the
 * requested language ('Villa o Villetta', 'Vendita'), while the API filter only
 * accepts the untranslated key ('haus', 'kauf'). Feeding the translated label into
 * the filter matched in German only - where label and key happen to be equal - and
 * returned zero similar estates in every other language.
 *
 * A translated estate is also a record of its own, so the main language id alone is
 * not enough to keep the estate out of its own list of similar estates.
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2025, onOffice(R) GmbH
 *
 */

class TestClassEstateViewSimilarEstatesMultiLanguage
	extends WP_UnitTestCase
{
	/** Id of the main language record */
	const MAIN_LANG_ID = 300;

	/** Id of the italian record belonging to MAIN_LANG_ID */
	const TRANSLATED_ID = 417;

	/** @var array */
	private $_resultSubRecords = [
		305 => [
			'Id' => 305,
			'laengengrad' => 50.3333,
			'breitengrad' => 13.777,
			'strasse' => 'Teststreet',
			'plz' => '12345',
			'land' => 'Testcountry',
		],
	];

	/**
	 * The italian record of the estate: the id differs from the main language id and
	 * objektart/vermarktungsart arrive translated.
	 *
	 * @var array
	 */
	private $_mainRecordsTranslated = [
		self::TRANSLATED_ID => [
			'Id' => self::TRANSLATED_ID,
			'laengengrad' => 50.24584,
			'breitengrad' => 13.3847,
			'strasse' => '',
			'plz' => '',
			'land' => '',
			'vermarktungsart' => 'Vendita',
			'objektart' => 'Villa o Villetta',
		],
	];

	/** @var array The same record unformatted - always carries the untranslated keys */
	private $_mainRecordsRaw = [
		self::TRANSLATED_ID => [
			'id' => self::TRANSLATED_ID,
			'elements' => [
				'vermarktungsart' => 'kauf',
				'objektart' => 'haus',
			],
		],
	];


	/**
	 * @param bool $withRawRecords
	 * @return EstateViewSimilarEstates
	 */

	private function buildSimilarEstates(bool $withRawRecords = true): EstateViewSimilarEstates
	{
		$pDataViewSimilarEstates = new DataViewSimilarEstates();
		$pDataViewSimilarEstates->setTemplate('resources/templates/unitlist.php');
		$pDataViewSimilarEstates->setSameEstateKind(true);
		$pDataViewSimilarEstates->setSameMarketingMethod(true);

		$pEnvironment = new EstateViewSimilarEstatesEnvironmentTest($pDataViewSimilarEstates);
		$pEnvironment->getEstateList()->setEstateData($this->_resultSubRecords);
		$pEnvironment->getEstateList()->loadEstates();

		$pDataView = new DataListView(1, 'test');
		$pDataView->setFields(['Id', 'laengengrad', 'breitengrad', 'strasse', 'plz', 'land']);

		$pEstateListBase = new EstateListMocker($pDataView);
		$pEstateListBase->setEstateData($this->_mainRecordsTranslated);
		$pEstateListBase->setMainLangIds([self::TRANSLATED_ID => self::MAIN_LANG_ID]);

		if ($withRawRecords) {
			$pEstateListBase->setEstateDataRaw($this->_mainRecordsRaw);
		}

		$pEstateListBase->loadEstates();

		$pSimilarEstates = new EstateViewSimilarEstates($pDataViewSimilarEstates, $pEnvironment);
		$pSimilarEstates->loadByMainEstates($pEstateListBase);

		return $pSimilarEstates;
	}


	/**
	 * The sub lists are indexed by the main language id, so that is the only key the
	 * output can be requested with.
	 *
	 * @param EstateViewSimilarEstates $pSimilarEstates
	 * @return array
	 */

	private function getBuiltFilter(EstateViewSimilarEstates $pSimilarEstates): array
	{
		$pReflection = new ReflectionClass($pSimilarEstates);
		$pProperty = $pReflection->getProperty('_estateListsByMainId');
		$pProperty->setAccessible(true);
		$estateLists = $pProperty->getValue($pSimilarEstates);

		$this->assertArrayHasKey(self::MAIN_LANG_ID, $estateLists,
			'sub list must be indexed by the main language id');

		return $estateLists[self::MAIN_LANG_ID]->getDefaultFilterBuilder()->buildFilter();
	}


	/**
	 * The regression: a translated label must never end up in the filter.
	 */

	public function testFilterUsesUntranslatedEstateKind()
	{
		$filter = $this->getBuiltFilter($this->buildSimilarEstates());

		$this->assertEquals([['op' => '=', 'val' => 'haus']], $filter['objektart']);
	}


	/**
	 *
	 */

	public function testFilterUsesUntranslatedMarketingMethod()
	{
		$filter = $this->getBuiltFilter($this->buildSimilarEstates());

		$this->assertEquals([['op' => '=', 'val' => 'kauf']], $filter['vermarktungsart']);
	}


	/**
	 * Both the main language id and the id of the translated record have to be
	 * excluded, otherwise the estate is similar to itself in every other language.
	 */

	public function testEstateIsExcludedFromItsOwnSimilarEstates()
	{
		$filter = $this->getBuiltFilter($this->buildSimilarEstates());
		$excludedIds = $filter['Id'][0]['val'];

		$this->assertContains(self::MAIN_LANG_ID, $excludedIds);
		$this->assertContains(self::TRANSLATED_ID, $excludedIds);
	}


	/**
	 * Requesting the output with the main language id must work even though the
	 * record that was iterated carries the translated id.
	 */

	public function testOutputIsGeneratedForTranslatedRecord()
	{
		$pSimilarEstates = $this->buildSimilarEstates();

		$this->assertEquals([305], $pSimilarEstates->getSubEstateIds(self::MAIN_LANG_ID));
		$this->assertNotEmpty($pSimilarEstates->generateHtmlOutput(self::MAIN_LANG_ID));
	}


	/**
	 * Installations without raw records must keep working - the formatted value is
	 * the documented fallback.
	 */

	public function testFallsBackToFormattedValueWithoutRawRecords()
	{
		$filter = $this->getBuiltFilter($this->buildSimilarEstates(false));

		$this->assertEquals([['op' => '=', 'val' => 'Villa o Villetta']], $filter['objektart']);
	}
}
