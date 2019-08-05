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

namespace onOffice\tests;

use onOffice\WPlugin\Field\DistinctFieldsHandlerModel;

use WP_UnitTestCase;

/**
 *
 */

class TestClassDistinctFieldsHandlerModel
	extends WP_UnitTestCase
{

	/** @var DistinctFieldsHandlerModel */
	private $_pInstance = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pInstance = new DistinctFieldsHandlerModel();
	}


	/**
	 *
	 */

	public function testConstruct()
	{
		$this->assertInstanceOf(DistinctFieldsHandlerModel::class, $this->_pInstance);
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerModel::setModule
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerModel::getModule
	 *
	 */

	public function testSetModule()
	{
		$this->_pInstance->setModule('estate');
		$this->assertEquals('estate', $this->_pInstance->getModule());
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerModel::setDistinctFields
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerModel::getDistinctFields
	 *
	 */

	public function testSetDistinctFields()
	{
		$distinctFields = ['nutzungsart', 'objektart'];
		$this->_pInstance->setDistinctFields($distinctFields);
		$this->assertEquals($distinctFields, $this->_pInstance->getDistinctFields());
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerModel::setInputValues
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerModel::getInputValues
	 *
	 */

	public function testSetInputValues()
	{
		$inputValues = [
			'objektart' => ['wohnung'],
			'vermarktungsart' => ['kauf']
			];

		$this->_pInstance->setInputValues($inputValues);
		$this->assertEquals($inputValues, $this->_pInstance->getInputValues());
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerModel::setGeoPositionFields
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerModel::getGeoPositionFields
	 *
	 */

	public function testsetGeoPositionFields()
	{
		$geoFields = ['range'];

		$this->_pInstance->setGeoPositionFields($geoFields);
		$this->assertEquals($geoFields, $this->_pInstance->getGeoPositionFields());
	}
}
