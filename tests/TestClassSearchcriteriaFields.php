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
use onOffice\WPlugin\Field\SearchcriteriaFields;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use WP_UnitTestCase;


/**
 *
 */

class TestClassSearchcriteriaFields
	extends WP_UnitTestCase
{
	/** @var SearchcriteriaFields */
	private $_pSubject = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$pFieldsCollectionBuilder = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
			->setMethods(['addFieldsSearchCriteria', 'addFieldsAddressEstate'])
			->setConstructorArgs([new Container])
			->getMock();
		$pFieldsCollectionBuilder->method('addFieldsSearchCriteria')->with($this->anything())
			->will($this->returnCallback(function(FieldsCollection $pFieldsCollection) use
				($pFieldsCollectionBuilder): FieldsCollectionBuilderShort {
				$pField1 = new Field('testvarchar', onOfficeSDK::MODULE_SEARCHCRITERIA);
				$pField1->setType(FieldTypes::FIELD_TYPE_VARCHAR);
				$pField2 = new Field('testvarcharestate', onOfficeSDK::MODULE_ESTATE);
				$pField2->setType(FieldTypes::FIELD_TYPE_VARCHAR);
				$pField3 = new Field('testint', onOfficeSDK::MODULE_SEARCHCRITERIA);
				$pField3->setType(FieldTypes::FIELD_TYPE_INTEGER);
				$pFieldsCollection->addField($pField1);
				$pFieldsCollection->addField($pField2);
				$pFieldsCollection->addField($pField3);
				return $pFieldsCollectionBuilder;
			}));
		$pFieldsCollectionBuilder->method('addFieldsAddressEstate')->with($this->anything())
			->will($this->returnCallback(function(FieldsCollection $pFieldsCollection) use
			($pFieldsCollectionBuilder): FieldsCollectionBuilderShort {
				$pField1 = new Field('testvarchar', onOfficeSDK::MODULE_SEARCHCRITERIA);
				$pField1->setType(FieldTypes::FIELD_TYPE_VARCHAR);
				$pField2 = new Field('testvarcharestate', onOfficeSDK::MODULE_ESTATE);
				$pField2->setType(FieldTypes::FIELD_TYPE_VARCHAR);
				$pField3 = new Field('testint', onOfficeSDK::MODULE_SEARCHCRITERIA);
				$pField3->setType(FieldTypes::FIELD_TYPE_INTEGER);
				$pFieldsCollection->addField($pField1);
				$pFieldsCollection->addField($pField2);
				$pFieldsCollection->addField($pField3);
				return $pFieldsCollectionBuilder;
			}));
		$this->_pSubject = new SearchcriteriaFields($pFieldsCollectionBuilder);
	}


	/**
	 *
	 */

	public function testGetFormFields()
	{
		$inputFormFields = [
			'testA' => 'searchcriteria',
			'testB' => 'searchcriteria',
		];
		$result = $this->_pSubject->getFormFields($inputFormFields);
		$this->assertEquals([
			'testA' => 'searchcriteria',
			'testB' => 'searchcriteria',
			'testvarchar' => 'searchcriteria',
			'testint' => 'searchcriteria',
		], $result);
	}


	/**
	 *
	 */

	public function testGetFormFieldsWithRangeFields()
	{
		$inputFormFields = [
			'testA' => 'searchcriteria',
			'testB' => 'searchcriteria',
		];
		$result = $this->_pSubject->getFormFieldsWithRangeFields($inputFormFields);
		$this->assertEquals([
			'testA' => 'searchcriteria',
			'testB' => 'searchcriteria',
			'testvarchar' => 'searchcriteria',
			'testint__von' => 'searchcriteria',
			'testint__bis' => 'searchcriteria',
		], $result);
	}

	/**
	 *
	 */
	public function testGetMessageFieldLabelsOfInputs()
	{
		$inputFormFields = [
			'message' => 'abc'
		];
		$result = $this->_pSubject->getFieldLabelsOfInputs($inputFormFields);
		$this->assertEquals([
			'message' => 'abc',
		], $result);
	}
}
