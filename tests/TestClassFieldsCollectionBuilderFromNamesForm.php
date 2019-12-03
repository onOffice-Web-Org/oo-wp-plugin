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

use DI\DependencyException;
use DI\NotFoundException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderFromNamesForm;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use PHPUnit\Framework\MockObject\MockObject;

class TestClassFieldsCollectionBuilderFromNamesForm
	extends \WP_UnitTestCase
{
	/** @var FieldsCollectionBuilderFromNamesForm */
	private $_pSubject = null;

	/**
	 * @before
	 */
	public function prepare()
	{
		$pFieldsCollectionBuilderShort = $this->buildFieldsCollectionBuilderShortMock();
		$this->_pSubject = new FieldsCollectionBuilderFromNamesForm($pFieldsCollectionBuilderShort);
	}

	/**
	 *
	 */
	public function testBuildFieldsCollection()
	{
		$pNewFieldsCollection = $this->_pSubject->buildFieldsCollection(['testAddress', 'testEstate']);
		$pExpectedFieldsCollection = $this->buildExpectedFieldsCollection();
		$this->assertEquals($pExpectedFieldsCollection, $pNewFieldsCollection);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownFieldException
	 * @expectedException \onOffice\WPlugin\Field\UnknownFieldException
	 */
	public function testBuildFieldsCollectionUnknownField()
	{
		$this->_pSubject->buildFieldsCollection(['testUnknown']);
	}

	/**
	 * @return FieldsCollection
	 */
	private function buildExpectedFieldsCollection(): FieldsCollection
	{
		$exampleFields = $this->buildExampleFields();
		$pFieldsCollection = new FieldsCollection;
		$pFieldsCollection->addField($exampleFields[0]);
		$pFieldsCollection->addField($exampleFields[1]);
		return $pFieldsCollection;
	}

	/**
	 * @return MockObject
	 */
	private function buildFieldsCollectionBuilderShortMock(): MockObject
	{
		$pFieldsCollectionBuilderShort = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
			->setMethods([
				'addFieldsAddressEstate',
				'addFieldsFormBackend',
				'addFieldsSearchCriteria',
				'addFieldsSearchCriteriaSpecificBackend'
			])->disableOriginalConstructor()
			->getMock();
		$pFieldsCollectionBuilderShort->expects($this->once())
			->method('addFieldsAddressEstate')->willReturnCallback(
				function(FieldsCollection $pFieldsCollectionInner) use ($pFieldsCollectionBuilderShort) {
					$fieldsAddressEstate = $this->buildExampleFields();
					$pFieldsCollectionInner->addField($fieldsAddressEstate[0]);
					$pFieldsCollectionInner->addField($fieldsAddressEstate[1]);
					return $pFieldsCollectionBuilderShort;
				});
		$pFieldsCollectionBuilderShort->expects($this->once())
			->method('addFieldsFormBackend')->willReturnSelf();
		$pFieldsCollectionBuilderShort->expects($this->once())
			->method('addFieldsSearchCriteria')->willReturnSelf();
		$pFieldsCollectionBuilderShort->expects($this->once())
			->method('addFieldsSearchCriteriaSpecificBackend')->willReturnSelf();
		return $pFieldsCollectionBuilderShort;
	}

	/**
	 * @return array
	 */
	private function buildExampleFields(): array
	{
		return [
			new Field('testAddress', onOfficeSDK::MODULE_ADDRESS),
			new Field('testEstate', onOfficeSDK::MODULE_ESTATE),
		];
	}
}
