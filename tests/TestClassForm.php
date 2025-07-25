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
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationOwner;
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
	private $_pInterestForm = null;

	/** @var Form */
	private $_pOwnerForm = null;

	/**
	 * @throws Exception
	 * @before
	 */
	public function prepare()
	{
		add_filter('locale', fn() => 'en_EN');
		$pContainerBuilder = new ContainerBuilder();
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();
		$pFieldsCollectionBuilder = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
			->disableOriginalConstructor()
			->getMock();
		$pFieldsCollectionBuilder->expects($this->exactly(2))->method('addFieldsAddressEstate')
			->will($this->returnCallback(function(FieldsCollection $pFieldsCollection) use ($pFieldsCollectionBuilder) {
				$pFieldText = new Field('testFieldText', 'testModule');
				$pFieldText->setType(FieldTypes::FIELD_TYPE_TEXT);
				$pFieldsCollection->addField($pFieldText);
				$pFieldMultiSelect = new Field('testFieldMultiSelect', 'testModule');
				$pFieldMultiSelect->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pFieldMultiSelect);
				$pFieldRange = new Field('testRange', 'testModule');
				$pFieldRange->setIsRangeField(true);
				$pFieldRange->setType(FieldTypes::FIELD_TYPE_FLOAT);
				$pFieldsCollection->addField($pFieldRange);
				return $pFieldsCollectionBuilder;
			}));
		$this->_pContainer->set(FieldsCollectionBuilderShort::class, $pFieldsCollectionBuilder);

		$pDataFormConfigurationFactory = $this->getMockBuilder(DataFormConfigurationFactory::class)
			->disableOriginalConstructor()
			->setMethods(['loadByFormName'])
			->getMock();
		$this->_pContainer->set(DataFormConfigurationFactory::class, $pDataFormConfigurationFactory);
		$pDataFormConfigurationInterest = new DataFormConfigurationInterest();
		$pDataFormConfigurationInterest->setId(13);
		$pDataFormConfigurationInterest->setInputs(['testModule' => 'testInput1']);
		$pDataFormConfigurationInterest->addHiddenFields('test-hidden');

		$pDataFormConfigurationOwner = new DataFormConfigurationOwner();
		$pDataFormConfigurationOwner->setId(14);
		$pDataFormConfigurationOwner->setInputs(['testModule' => 'testInput2']);
		$pDataFormConfigurationOwner->addTitlePerMultipagePage($this->getTitlePerMultipageNativePage1());
		$pDataFormConfigurationOwner->addTitlePerMultipagePage($this->getTitlePerMultipageEnglishPage1());
		$pDataFormConfigurationOwner->addTitlePerMultipagePage($this->getTitlePerMultipageNativePage2());
		$pDataFormConfigurationOwner->addTitlePerMultipagePage($this->getTitlePerMultipageNativePage3());
		$pDataFormConfigurationOwner->addTitlePerMultipagePage($this->getTitlePerMultipageEnglishPage3());

		$pRecordManagerReadForm = $this->getMockBuilder(RecordManagerReadForm::class)
			->getMock();

		$pRecordManagerFactory = $this->getMockBuilder(RecordManagerFactory::class)
			->getMock();
		$pRecordManagerFactory->expects($this->exactly(2))->method('create')
			->will($this->returnValue($pRecordManagerReadForm));
		$this->_pContainer->set(RecordManagerFactory::class, $pRecordManagerFactory);

		$pDataFormConfigurationFactory->expects($this->exactly(2))->method('loadByFormName')
			->will($this->onConsecutiveCalls($pDataFormConfigurationInterest, $pDataFormConfigurationOwner));

		$pDefaultValueModelToOutputConverter = $this->getMockBuilder(DefaultValueModelToOutputConverter::class)
			->disableOriginalConstructor()
			->getMock();
		$this->_pContainer->set(DefaultValueModelToOutputConverter::class, $pDefaultValueModelToOutputConverter);

		$pDefaultValueModelToOutputConverter->expects($this->atLeastOnce())->method('getConvertedMultiFields')
			->will($this->onConsecutiveCalls(['testValue'], ['testMulti1', 'testMulti2'], [['min' => 13.1, 'max' => 14.]]));
		add_option('onoffice-settings-honeypot', true);

		$this->_pInterestForm = new Form('testForm1', Form::TYPE_INTEREST, $this->_pContainer);
		$this->_pOwnerForm = new Form('testForm2', Form::TYPE_OWNER, $this->_pContainer);
	}

	/**
	 *
	 */
	public function testGetFieldValueWithDefaultValue()
	{
		foreach ($this->_pInterestForm as $pSubject) {
			$this->assertEquals('testValue', $pSubject->getFieldValue('testFieldText'));
			$this->assertEquals(['testMulti1', 'testMulti2'],
					$pSubject->getFieldValue('testFieldMultiSelect', true));
			$this->assertEquals(['min' => 13.1, 'max' => 14.],
					$pSubject->getFieldValue('testRange', true));
		}
	}


	/**
	 *
	 */
	public function testIsHiddenField()
	{
		$this->assertEquals(true, $this->_pInterestForm->isHiddenField('test-hidden'));
	}

	/**
	 *	Native is given
	 *	Translation is given
	 *	Correct translation expected
	 */
	public function testGetPageTitlesByCurrentLanguagePage1()
	{
		$this->assertEquals("Your contact details", $this->_pOwnerForm->getPageTitlesByCurrentLanguage()[0]['value']);
	}

	/**
	 *	Native is given
	 *	No Translation is given
	 *  Native expected
	 */
	public function testGetPageTitlesByCurrentLanguagePage2()
	{
		$this->assertEquals("Ihre Immobilie", $this->_pOwnerForm->getPageTitlesByCurrentLanguage()[1]['value']);
	}

	/**
	 *	Native is empty
	 *	Translation is empty
	 *  Fallback expected
	 */
	public function testGetPageTitlesByCurrentLanguagePage3()
	{
		$this->assertEquals("Page 3", $this->_pOwnerForm->getPageTitlesByCurrentLanguage()[2]['value']);
	}

	/**
	 * @return array
	 */
	private function getTitlePerMultipageNativePage1(): array
	{
		return [
			'form_multipage_title_id' => 1,
			'form_id' => 14,
			'locale' => 'native',
			'value' => 'Ihre Kontaktdaten',
			'page' => 1,
		];
	}

	/**
	 * @return array
	 */
	private function getTitlePerMultipageEnglishPage1(): array
	{
		return [
			'form_multipage_title_id' => 2,
			'form_id' => 14,
			'locale' => 'en_EN',
			'value' => 'Your contact details',
			'page' => 1,
		];
	}

	/**
	 * @return array
	 */
	private function getTitlePerMultipageNativePage2(): array
	{
		return [
			'form_multipage_title_id' => 3,
			'form_id' => 14,
			'locale' => 'native',
			'value' => 'Ihre Immobilie',
			'page' => 2,
		];
	}

	/**
	 * @return array
	 */
	private function getTitlePerMultipageNativePage3(): array
	{
		return [
			'form_multipage_title_id' => 4,
			'form_id' => 14,
			'locale' => 'native',
			'value' => '',
			'page' => 3,
		];
	}

	/**
	 * @return array
	 */
	private function getTitlePerMultipageEnglishPage3(): array
	{
		return [
			'form_multipage_title_id' => 5,
			'form_id' => 14,
			'locale' => 'en_EN',
			'value' => '',
			'page' => 3,
		];
	}
}