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

use onOffice\WPlugin\Field\DistinctFieldsHandlerModel;
use onOffice\WPlugin\Field\DistinctFieldsHandlerModelBuilder;
use onOffice\WPlugin\RequestVariablesSanitizer;
use onOffice\WPlugin\WP\WPScriptStyleDefault;
use WP_UnitTestCase;


/**
 *
 */

class TestClassDistinctFieldsHandlerModelBuilder
	extends WP_UnitTestCase
{

	/** @var DistinctFieldsHandlerModelBuilder */
	private $_pInstance = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pInstance = new DistinctFieldsHandlerModelBuilder(new RequestVariablesSanitizer(), new WPScriptStyleDefault());
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerModelBuilder::buildDataModel
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerModelBuilder::buildInputValuesForModule
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerModelBuilder::getModule
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerModelBuilder::getDistinctValues
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerModelBuilder::getInputValues
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerModelBuilder::getScriptStyle
	 *
	 */

	public function testForEstate()
	{
		$inputValues =
			[
				"" => "OK",
				"nutzungsart[]" => ["wohnen"],
				"radius" => "0",
				"oo_formid" => 'contactform',
				"oo_formno" => '10',
				"Id" => '2370'
			];


		$_POST = [
			'field' => 'nutzungsart[]',
			'inputValues' => '{\\"\\":\\"OK\\",\\"nutzungsart[]\\":[\\"wohnen\\"],\\"radius\\":\\"0\\",\\"oo_formid\\":\\"contactform\\",\\"oo_formno\\":\\"10\\",\\"Id\\":\\"2370\\"}',
			'module' => 'estate',
			'distinctValues' => ['nutzungsart', 'objektart'],
		];

		$pResultModel = $this->_pInstance->buildDataModel();

		$this->assertInstanceOf(DistinctFieldsHandlerModel::class, $pResultModel);
		$this->assertEquals('estate', $this->_pInstance->getModule());
		$this->assertEquals(['nutzungsart', 'objektart'], $this->_pInstance->getDistinctValues());
		$this->assertEquals($inputValues, $this->_pInstance->getInputValues());
		$this->assertEquals(['radius' => '0'], $pResultModel->getGeoPositionFields());
		$this->assertInstanceOf(WPScriptStyleDefault::class, $this->_pInstance->getScriptStyle());
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerModelBuilder::buildDataModel
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandlerModelBuilder::buildInputValuesForModule
	 *
	 */

	public function testForSearchcriteria()
	{
		$_POST = [
			'field' => 'objektart',
			'inputValues' => '{\\"oo_formid\\":\\"applsearchform\\",\\"oo_formno\\":\\"1\\",\\"wohnflaeche\\":\\"\\",\\"anzahl_zimmer\\":\\"\\",\\"objektart\\":\\"haus\\",\\"objekttyp\\":\\"\\",\\"kaltmiete\\":\\"\\",\\"range_plz\\":\\"\\"}',
			'module' => 'searchcriteria',
			'distinctValues' =>	['objektart', 'objekttyp'],
		  ];

		$inputValues = [
			"oo_formid" => 'applsearchform',
			"oo_formno" => '1',
			"wohnflaeche" => '',
			"anzahl_zimmer" => '',
			"objektart" => 'haus',
			"objekttyp" => '',
			"kaltmiete" => '',
			"range_plz" => '',
		];

		$pResultModel = $this->_pInstance->buildDataModel();

		$this->assertInstanceOf(DistinctFieldsHandlerModel::class, $pResultModel);
		$this->assertEquals('searchcriteria', $this->_pInstance->getModule());
		$this->assertEquals(['objektart', 'objekttyp'], $this->_pInstance->getDistinctValues());
		$this->assertEquals($inputValues, $this->_pInstance->getInputValues());
		$this->assertEquals(['zip' => ''], $pResultModel->getGeoPositionFields());
		$this->assertInstanceOf(WPScriptStyleDefault::class, $this->_pInstance->getScriptStyle());
	}
}