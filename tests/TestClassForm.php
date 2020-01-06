<?php

/**
 *
 *    Copyright (C) 2020 onOffice GmbH
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
use DI\ContainerBuilder;
use Exception;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationFactory;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationInterest;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\DefaultValue\ModelToOutputConverter\DefaultValueModelToOutputConverter;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Record\RecordManagerFactory;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;

class TestClassForm
	extends \WP_UnitTestCase
{
	/** @var Container */
	private $_pContainer = null;

	/** @var Form */
	private $_pSubject = null;

	/**
	 * @throws Exception
	 * @before
	 */
	public function prepare()
	{
		$pContainerBuilder = new ContainerBuilder();
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();

		$pFieldsCollectionBuilder = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
			->disableOriginalConstructor()
			->getMock();
		$pFieldsCollectionBuilder->expects($this->once())->method('addFieldsAddressEstate')
			->will($this->returnCallback(function(FieldsCollection $pFieldsCollection) use ($pFieldsCollectionBuilder) {
				$pFieldText = new Field('testFieldText', 'testModule');
				$pFieldText->setType(FieldTypes::FIELD_TYPE_TEXT);
				$pFieldsCollection->addField($pFieldText);
				$pFieldMultiSelect = new Field('testFieldMultiSelect', 'testModule');
				$pFieldMultiSelect->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pFieldMultiSelect);
				return $pFieldsCollectionBuilder;
			}));
		$this->_pContainer->set(FieldsCollectionBuilderShort::class, $pFieldsCollectionBuilder);

		$pDataFormConfigurationFactory = $this->getMockBuilder(DataFormConfigurationFactory::class)
			->disableOriginalConstructor()
			->setMethods(['loadByFormName'])
			->getMock();
		$this->_pContainer->set(DataFormConfigurationFactory::class, $pDataFormConfigurationFactory);
		$pDataFormConfiguration = new DataFormConfigurationInterest;
		$pDataFormConfiguration->setId(13);
		$pDataFormConfiguration->setInputs(['testModule' => 'testInput1']);

		$pRecordManagerReadForm = $this->getMockBuilder(RecordManagerReadForm::class)
			->getMock();

		$pRecordManagerFactory = $this->getMockBuilder(RecordManagerFactory::class)
			->getMock();
		$pRecordManagerFactory->expects($this->once())->method('create')
			->will($this->returnValue($pRecordManagerReadForm));
		$this->_pContainer->set(RecordManagerFactory::class, $pRecordManagerFactory);

		$pDataFormConfigurationFactory->expects($this->once())->method('loadByFormName')
			->will($this->returnValue($pDataFormConfiguration));

		$pDefaultValueModelToOutputConverter = $this->getMockBuilder(DefaultValueModelToOutputConverter::class)
			->disableOriginalConstructor()
			->getMock();
		$this->_pContainer->set(DefaultValueModelToOutputConverter::class, $pDefaultValueModelToOutputConverter);

		$pDefaultValueModelToOutputConverter->expects($this->atLeastOnce())->method('getConvertedField')
			->will($this->onConsecutiveCalls(['testValue'], ['testMulti1', 'testMulti2']));

		$this->_pSubject = new Form('testForm1', Form::TYPE_INTEREST, $this->_pContainer);
	}

	/**
	 *
	 */
	public function testGetFieldValueWithDefaultValue()
	{
		$this->assertEquals('testValue', $this->_pSubject->getFieldValue('testFieldText'));
		$this->assertEquals(['testMulti1', 'testMulti2'],
			$this->_pSubject->getFieldValue('testFieldMultiSelect', true));
	}
}