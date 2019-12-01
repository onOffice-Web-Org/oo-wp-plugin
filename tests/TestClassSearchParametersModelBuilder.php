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
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\CompoundFieldsFilter;
use onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel;
use onOffice\WPlugin\Filter\SearchParameters\SearchParametersModelBuilder;
use onOffice\WPlugin\RequestVariablesSanitizer;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Utility\Logger;
use WP_UnitTestCase;

class TestClassSearchParametersModelBuilder
	extends WP_UnitTestCase
{
	/** @var Logger */
	private $_pLogger;

	/** @var CompoundFieldsFilter */
	private $_pCompoundFields;

	/** @var RequestVariablesSanitizer */
	private $_pRequestVariablesSanitizer;

	/** @var FieldsCollectionBuilderShort */
	private $_pBuilderShort;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pLogger = $this->getMockBuilder(Logger::class)->getMock();
		$this->_pRequestVariablesSanitizer = new RequestVariablesSanitizer();
		$this->_pCompoundFields = new CompoundFieldsFilter;

		$this->_pBuilderShort = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
			->setMethods(['addFieldsAddressEstate', 'addFieldsSearchCriteria'])
			->setConstructorArgs([new Container])
			->getMock();
		$this->_pBuilderShort->method('addFieldsAddressEstate')
			->with($this->anything())
			->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
				$pFieldAnrede = new Field('Anrede', onOfficeSDK::MODULE_ADDRESS);
				$pFieldAnrede->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pFieldAnrede);

				$pFieldTitle = new Field('Titel', onOfficeSDK::MODULE_ADDRESS);
				$pFieldTitle->setType(FieldTypes::FIELD_TYPE_VARCHAR);
				$pFieldsCollection->addField($pFieldTitle);

				$pFieldOrt = new Field('Ort', onOfficeSDK::MODULE_ADDRESS);
				$pFieldOrt->setType(FieldTypes::FIELD_TYPE_VARCHAR);
				$pFieldsCollection->addField($pFieldOrt);

				$pFieldNutzungsart = new Field('nutzungsart', onOfficeSDK::MODULE_ESTATE);
				$pFieldNutzungsart->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pFieldNutzungsart);

				$pFieldOrtEstate = new Field('ort', onOfficeSDK::MODULE_ESTATE);
				$pFieldOrtEstate->setType(FieldTypes::FIELD_TYPE_VARCHAR);
				$pFieldsCollection->addField($pFieldOrtEstate);

				$pFieldKaufpreis = new Field('kaufpreis', onOfficeSDK::MODULE_ESTATE);
				$pFieldKaufpreis->setType(FieldTypes::FIELD_TYPE_INTEGER);
				$pFieldsCollection->addField($pFieldKaufpreis);
				return $this->_pBuilderShort;
			}));
	}

	/**
	 * @covers onOffice\WPlugin\Filter\SearchParameters\SearchParametersModelBuilder::build
	 */
	public function testBuild()
	{
		$_GET = [
			'Anrede' => ['Herr','Frau', 'Firma'],
			'Titel' => '',
			'Ort' => 'Aachen',
		];

		$pInstance = new SearchParametersModelBuilder($this->_pCompoundFields, $this->_pRequestVariablesSanitizer, $this->_pLogger);
		$pModel = $pInstance->build(['Anrede', 'Titel', 'Ort'], onOfficeSDK::MODULE_ADDRESS, $this->_pBuilderShort);
		$this->assertInstanceOf(SearchParametersModel::class, $pModel);
		$this->assertEquals(['Anrede' => ['Herr', 'Frau', 'Firma'], 'Ort'=>'Aachen'], $pModel->getParameters());


	}

	/**
	 *  @covers onOffice\WPlugin\Filter\SearchParameters\SearchParametersModelBuilder::build
	 */
	public function testBuildWithUnknownField()
	{
		$_GET = [
			'Anrede' => ['Herr', 'Frau', 'Firma'],
			'Titel' => '',
			'Ort' => 'Aachen'
		];

		$pInstance = new SearchParametersModelBuilder($this->_pCompoundFields, $this->_pRequestVariablesSanitizer, $this->_pLogger);
		$pModel = $pInstance->build(['asd', 'Ort'], onOfficeSDK::MODULE_ADDRESS, $this->_pBuilderShort);
		$this->assertEquals(['Ort' => 'Aachen'], $pModel->getParameters());
	}

	public function testBuildEstateNumField()
	{
		$_GET = [
			'kaufpreis__von'=> '10000',
			'kaufpreis__bis'=> '20000',
		];

		$pInstance = new SearchParametersModelBuilder($this->_pCompoundFields, $this->_pRequestVariablesSanitizer, $this->_pLogger);
		$pModel = $pInstance->build(['asd', 'Ort', 'kaufpreis'], onOfficeSDK::MODULE_ESTATE, $this->_pBuilderShort);
		$this->assertEquals(['kaufpreis__von', 'kaufpreis__bis', 'kaufpreis'], $pModel->getAllowedGetParameters());
		$this->assertEquals(['kaufpreis__von'=> '10000','kaufpreis__bis'=> '20000',], $pModel->getParameters());
	}
}