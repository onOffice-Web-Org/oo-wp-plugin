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

use onOffice\WPlugin\DataView\DataViewSimilarEstates;
use onOffice\WPlugin\Filter\DefaultFilterBuilderSimilarEstates;
use onOffice\WPlugin\Filter\FilterConfigurationSimilarEstates;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassDefaultFilterBuilderSimilarEstates
	extends WP_UnitTestCase
{
	/** @var FilterConfigurationSimilarEstates */
	private $_pFilterConfigurationSimilarEstates = null;

	/** @var DefaultFilterBuilderSimilarEstates */
	private $_pDefaultFilterBuilderSimilarEstates = null;


	/**
	 *
	 */

	public function setUp()
	{
		parent::setUp();

		$pDataViewEstates = new DataViewSimilarEstates();
		$pDataViewEstates->setSameEstateKind(false);
		$pDataViewEstates->setSameMarketingMethod(false);
		$this->_pFilterConfigurationSimilarEstates = new FilterConfigurationSimilarEstates
			($pDataViewEstates);
		$this->_pDefaultFilterBuilderSimilarEstates = new DefaultFilterBuilderSimilarEstates
			($this->_pFilterConfigurationSimilarEstates);
		$this->_pFilterConfigurationSimilarEstates->setEstateKind('wohnung');
		$this->_pFilterConfigurationSimilarEstates->setMarketingMethod('kauf');
		$this->_pFilterConfigurationSimilarEstates->setPostalCode('52070');
	}


	/**
	 *
	 */

	public function testBuildFilterBase()
	{
		$result = $this->_pDefaultFilterBuilderSimilarEstates->buildFilter();
		$expectation = [
			'veroeffentlichen' => [
				['op' => '=', 'val' => 1],
			],
			'verkauft' => [
				['op' => '!=', 'val' => 1],
			],
			'reserviert' => [
				['op' => '!=', 'val' => 1],
			],
			'referenz' => [
				['op' => '!=', 'val' => 1],
			],
		];
		$this->assertEquals($expectation, $result);
	}


	/**
	 *
	 */

	public function testBuildFilterSameEstateKind()
	{
		$this->getDataViewSimilarEstates()->setSameEstateKind(true);
		$result = $this->_pDefaultFilterBuilderSimilarEstates->buildFilter();
		$expectation = [
			'veroeffentlichen' => [
				['op' => '=', 'val' => 1],
			],
			'objektart' => [
				['op' => '=', 'val' => 'wohnung'],
			],
			'verkauft' => [
				['op' => '!=', 'val' => 1],
			],
			'reserviert' => [
				['op' => '!=', 'val' => 1],
			],
			'referenz' => [
				['op' => '!=', 'val' => 1],
			],
		];
		$this->assertEquals($expectation, $result);
	}


	/**
	 *
	 */

	public function testBuildFilterSameMarketingMethod()
	{
		$this->getDataViewSimilarEstates()->setSameMarketingMethod(true);
		$result = $this->_pDefaultFilterBuilderSimilarEstates->buildFilter();
		$expectation = [
			'veroeffentlichen' => [
				['op' => '=', 'val' => 1],
			],
			'vermarktungsart' => [
				['op' => '=', 'val' => 'kauf'],
			],
			'verkauft' => [
				['op' => '!=', 'val' => 1],
			],
			'reserviert' => [
				['op' => '!=', 'val' => 1],
			],
			'referenz' => [
				['op' => '!=', 'val' => 1],
			],
		];
		$this->assertEquals($expectation, $result);
	}


	/**
	 *
	 */

	public function testBuildFilterSamePostalCode()
	{
		$this->getDataViewSimilarEstates()->setSamePostalCode(true);
		$result = $this->_pDefaultFilterBuilderSimilarEstates->buildFilter();
		$expectation = [
			'veroeffentlichen' => [
				['op' => '=', 'val' => 1],
			],
			'verkauft' => [
				['op' => '!=', 'val' => 1],
			],
			'reserviert' => [
				['op' => '!=', 'val' => 1],
			],
			'referenz' => [
				['op' => '!=', 'val' => 1],
			],
			'plz' => [
				['op' => '=', 'val' => '52070'],
			],
		];
		$this->assertEquals($expectation, $result);
	}

	/**
	 *
	 */

	public function testExcludeIds()
	{
		$this->getDataViewSimilarEstates()->setSamePostalCode(true);
		$pFilterBuilder = $this->_pDefaultFilterBuilderSimilarEstates;
		$pFilterBuilder->setExcludeIds([13, 37]);
		$filter = $pFilterBuilder->buildFilter();

		$expectation = [
			'veroeffentlichen' => [
				['op' => '=', 'val' => 1],
			],
			'verkauft' => [
				['op' => '!=', 'val' => 1],
			],
			'reserviert' => [
				['op' => '!=', 'val' => 1],
			],
			'referenz' => [
				['op' => '!=', 'val' => 1],
			],
			'plz' => [
				['op' => '=', 'val' => '52070'],
			],
			'Id' => [
				['op' => 'not in', 'val' => [13, 37]],
			],
		];
		$this->assertEquals($expectation, $filter);
		$this->assertEquals([13, 37], $pFilterBuilder->getExcludeIds());
	}


	/**
	 *
	 */

	public function testBuildFilterCombined()
	{
		$this->getDataViewSimilarEstates()->setSameEstateKind(true);
		$this->getDataViewSimilarEstates()->setSameMarketingMethod(true);
		$this->getDataViewSimilarEstates()->setSamePostalCode(true);
		$result = $this->_pDefaultFilterBuilderSimilarEstates->buildFilter();
		$expectation = [
			'veroeffentlichen' => [
				['op' => '=', 'val' => 1],
			],
			'verkauft' => [
				['op' => '!=', 'val' => 1],
			],
			'reserviert' => [
				['op' => '!=', 'val' => 1],
			],
			'referenz' => [
				['op' => '!=', 'val' => 1],
			],
			'vermarktungsart' => [
				['op' => '=', 'val' => 'kauf'],
			],
			'plz' => [
				['op' => '=', 'val' => '52070'],
			],
			'objektart' => [
				['op' => '=', 'val' => 'wohnung'],
			],
		];
		$this->assertEquals($expectation, $result);
	}


	/**
	 *
	 * @return DataViewSimilarEstates
	 *
	 */

	private function getDataViewSimilarEstates(): DataViewSimilarEstates
	{
		return $this->_pFilterConfigurationSimilarEstates->getDataViewSimilarEstates();
	}
}
