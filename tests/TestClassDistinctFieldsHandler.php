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

use DI\Container;
use onOffice\SDK\onOfficeSDK;
use onOffice\tests\SDKWrapperMocker;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\DistinctFieldsFilter;
use onOffice\WPlugin\Field\DistinctFieldsHandler;
use onOffice\WPlugin\Field\DistinctFieldsHandlerModelBuilder;
use onOffice\WPlugin\RequestVariablesSanitizer;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\WP\WPScriptStyleDefault;
use WP_UnitTestCase;
use function json_decode;

/**
 *
 * Test for class DistinctFieldsHandler
 *
 */

class TestClassDistinctFieldsHandler
	extends WP_UnitTestCase
{
	/** @var DistinctFieldsHandler */
	private $_pInstance = null;

	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pFieldsCollectionBuilderShort = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
			->setMethods(['addFieldsAddressEstate', 'addFieldsSearchCriteria'])
			->setConstructorArgs([new Container])
			->getMock();

		$this->_pFieldsCollectionBuilderShort->method('addFieldsAddressEstate')
			->with($this->anything())
			->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
				$pFieldObjektart = new Field('objektart', onOfficeSDK::MODULE_ESTATE);
				$pFieldObjektart->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pFieldObjektart);

				$pFieldNutzungsart = new Field('nutzungsart', onOfficeSDK::MODULE_ESTATE);
				$pFieldNutzungsart->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pFieldNutzungsart);

				return $this->_pFieldsCollectionBuilderShort;
			}));

		$this->_pFieldsCollectionBuilderShort->method('addFieldsSearchCriteria')
			->with($this->anything())
			->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
				$pField1 = new Field('objektart', onOfficeSDK::MODULE_SEARCHCRITERIA);
				$pField1->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pField1);

				return $this->_pFieldsCollectionBuilderShort;
			}));

		$pRequestVariables = new RequestVariablesSanitizer();
		$pModelBuilder = new DistinctFieldsHandlerModelBuilder($pRequestVariables);
		$pDistinctFieldsFilter = new DistinctFieldsFilter($this->_pFieldsCollectionBuilderShort);
		$pSDKWrapperMocker = $this->buildSDKWrapper();

		$this->_pInstance = new DistinctFieldsHandler(
				$pModelBuilder, $pSDKWrapperMocker, $this->_pFieldsCollectionBuilderShort, $pDistinctFieldsFilter);
	}


	/**
	 *
	 * @return SDKWrapperMocker
	 *
	 */

	private function buildSDKWrapper(): SDKWrapperMocker
	{
		$pSDKWrapperMocker = new SDKWrapperMocker();

		$parametersEstatesObjektart = [
			'language' => 'ENG',
			'module' => 'estate',
			'field' => 'objektart',
			'filter' => [
				'nutzungsart' => [['op' => 'in', 'val' => ['wohnen']]]
			],
			'georangesearch' => ['radius' => '0']
		];

		$parametersEstates = [
			'language' => 'ENG',
			'module' => 'estate',
			'field' => 'nutzungsart',
			'filter' => [
				'objektart' => [['op' => 'in', 'val' => ['haus']]]
			],
			'georangesearch' => ['radius' => '0']
		];

		$responseEstates = file_get_contents
			(__DIR__.'/resources/Field/AppResponseDistinctFieldsHandlerEstate.json');
		$responseEstatesFields = json_decode($responseEstates, true);

		$responseEstatesObjektart = file_get_contents
			(__DIR__.'/resources/Field/AppResponseDistinctFieldsHandlerEstateObjektart.json');
		$responseEstatesFieldsObjektart = json_decode($responseEstatesObjektart, true);

		$pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'distinctValues',
			'', $parametersEstates, null, $responseEstatesFields);

		$pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'distinctValues',
			'', $parametersEstatesObjektart, null, $responseEstatesFieldsObjektart);
		return $pSDKWrapperMocker;
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandler::__construct
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandler::check
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandler::retrieveValues
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandler::buildParameters
	 * @covers onOffice\WPlugin\Field\DistinctFieldsHandler::editMultiselectableField
	 *
	 */

	public function testCheck()
	{
		$_POST = [
			'field' => 'objektart[]',
			'inputValues' => '{\\"\\":\\"OK\\",\\"nutzungsart\\":[\\"wohnen\\"],\\"objektart\\":[\\"haus\\"],\\"radius\\":\\"0\\",\\"oo_formid\\":\\"contactform\\",\\"oo_formno\\":\\"10\\",\\"Id\\":\\"2370\\"}',
			'module' => 'estate',
			'distinctValues' => ['nutzungsart','objektart'],
		];

		$expectedValues = [
			'nutzungsart[]' => [
				'wohnen' => 'Wohnen',
				'waz' => 'WAZ',
				'anlage' => 'Anlage'
			],
			'objektart[]' => [
				'haus' => 'Haus',
				'wohnung' => 'Wohnung',
				'grundstueck' => 'Grundstück',
				'sonstige' => 'Sonstige',
				'gastgewerbe' => 'Gastgewerbe'
			]
		];

		$this->_pInstance->check();
		$this->assertEquals($expectedValues, $this->_pInstance->check());
	}
}