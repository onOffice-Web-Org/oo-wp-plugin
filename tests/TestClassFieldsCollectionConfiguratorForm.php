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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\Collection\FieldsCollectionConfiguratorForm;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;

class TestClassFieldsCollectionConfiguratorForm
	extends \WP_UnitTestCase
{
	/**
	 * @throws UnknownFieldException
	 */
	public function testConfigureForApplicantSearchForm()
	{
		$pFieldsCollection = $this->buildFieldsCollectionRange();
		$pSubject = new FieldsCollectionConfiguratorForm;
		$pFieldsCollectionNew = $pSubject->configureForApplicantSearchForm($pFieldsCollection);

		$this->assertCount(2, $pFieldsCollectionNew->getAllFields());

		foreach ($pFieldsCollectionNew->getAllFields() as $pField) {
			$pFieldOriginal = $pFieldsCollection->getFieldByModuleAndName
				($pField->getModule(), $pField->getName());
			$this->assertNotSame($pFieldOriginal, $pField);
			$this->assertFalse($pField->getIsRangeField());
		}
	}

	/**
	 * @throws UnknownFieldException
	 */
	public function testConfigureForInterestForm()
	{
		$pFieldsCollection = $this->buildFieldsCollectionSelect();
		$pSubject = new FieldsCollectionConfiguratorForm;
		$pFieldsCollectionNew = $pSubject->configureForInterestForm($pFieldsCollection);
		foreach ($pFieldsCollectionNew->getAllFields() as $pField) {
			$pFieldOriginal = $pFieldsCollection->getFieldByModuleAndName
				($pField->getModule(), $pField->getName());
			$this->assertNotSame($pFieldOriginal, $pField);
			$this->assertThat($pField, $this->callback(function(Field $pField) {
				return
					($pField->getName() === 'vermarktungsart' && $pField->getType() === FieldTypes::FIELD_TYPE_SINGLESELECT) ||
					($pField->getName() !== 'vermarktungsart' && $pField->getType() === FieldTypes::FIELD_TYPE_MULTISELECT);
			}));
		}
	}

	/**
	 * @throws UnknownFieldException
	 */
	public function testBuildForFormType()
	{
		$pSubject = new FieldsCollectionConfiguratorForm;
		$pFieldsCollection = new FieldsCollection;
		$pFieldRange = new Field('a', 'a');
		$pFieldRange->setIsRangeField(true);
		$pFieldSingleSelect = new Field('b', onOfficeSDK::MODULE_SEARCHCRITERIA);
		$pFieldSingleSelect->setType(FieldTypes::FIELD_TYPE_SINGLESELECT);
		$pFieldsCollection->addField($pFieldRange);
		$pFieldsCollection->addField($pFieldSingleSelect);
		$pCollectionApplicantSearch = $pSubject->buildForFormType($pFieldsCollection, Form::TYPE_APPLICANT_SEARCH);
		$this->assertFalse($pCollectionApplicantSearch
			->getFieldByKeyUnsafe('a')->getIsRangeField());
		$pCollectionInterest = $pSubject->buildForFormType($pFieldsCollection, Form::TYPE_INTEREST);
		$this->assertEquals(FieldTypes::FIELD_TYPE_MULTISELECT,
			$pCollectionInterest->getFieldByKeyUnsafe('b')->getType());
	}

	/**
	 * @return FieldsCollection
	 */
	private function buildFieldsCollectionRange(): FieldsCollection
	{
		$pFieldsCollection = new FieldsCollection;
		$pField1 = new Field('testField', 'testModule');
		$pField1->setType(FieldTypes::FIELD_TYPE_INTEGER);
		$pField1->setIsRangeField(true);
		$pField2 = new Field('testField2', 'testModule2');
		$pField2->setType(FieldTypes::FIELD_TYPE_INTEGER);
		$pField2->setIsRangeField(true);
		$pFieldsCollection->addField($pField1);
		$pFieldsCollection->addField($pField2);
		return $pFieldsCollection;
	}

	/**
	 * @return FieldsCollection
	 */
	private function buildFieldsCollectionSelect(): FieldsCollection
	{
		$pFieldsCollection = new FieldsCollection;
		$pField3 = new Field('testFieldSingleselect', onOfficeSDK::MODULE_SEARCHCRITERIA);
		$pField3->setType(FieldTypes::FIELD_TYPE_SINGLESELECT);
		$pField4 = new Field('vermarktungsart', onOfficeSDK::MODULE_SEARCHCRITERIA);
		$pField4->setType(FieldTypes::FIELD_TYPE_SINGLESELECT);
		$pFieldsCollection->addField($pField3);
		$pFieldsCollection->addField($pField4);
		return $pFieldsCollection;
	}
}