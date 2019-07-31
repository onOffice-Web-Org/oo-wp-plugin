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

use onOffice\WPlugin\Field\DistinctFieldsHandlerEnvironment;
use WP_UnitTestCase;

/**
 *
 */

class TestClassDistinctFieldsHandlerEnvironment
	extends WP_UnitTestCase
{
	/** */
	const INPUT_VALUES = [
			'objektart' => ['wohnung'],
			'vermarktungsart' => ['kauf']
		];

	/** */
	const DISTINCT_FIELDS = ['objekttyp'];

	/** */
	const GEO_POSITION_FIELDS = ['range'];

	/** */
	const MODULE = 'estate';

	/** @var DistinctFieldsHandlerEnvironment */
	private $_pInstance = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pInstance = new DistinctFieldsHandlerEnvironment();
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerEnvironment::setModule
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerEnvironment::getModule
	 *
	 */

	public function testSetModule()
	{
		$this->_pInstance->setModule(self::MODULE);
		$this->assertEquals(self::MODULE, $this->_pInstance->getModule());
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerEnvironment::setInputValues
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerEnvironment::getInputValues
	 *
	 */

	public function testSetInputValues()
	{
		$this->_pInstance->setInputValues(self::INPUT_VALUES);
		$this->assertEquals(self::INPUT_VALUES, $this->_pInstance->getInputValues());
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerEnvironment::setDistinctFields
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerEnvironment::getDistinctFields
	 *
	 */

	public function testSetDistinctFields()
	{
		$this->_pInstance->setDistinctFields(self::DISTINCT_FIELDS);
		$this->assertEquals(self::DISTINCT_FIELDS, $this->_pInstance->getDistinctFields());
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerEnvironment::setGeoPositionFields
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerEnvironment::getGeoPositionFields
	 *
	 */

	public function testSetGeoPositionFields()
	{
		$this->_pInstance->setGeoPositionFields(self::GEO_POSITION_FIELDS);
		$this->assertEquals(self::GEO_POSITION_FIELDS, $this->_pInstance->getGeoPositionFields());
	}
}
