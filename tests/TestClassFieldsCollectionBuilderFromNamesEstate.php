<?php

/**
 *
 *    Copyright (C) 2023 onOffice GmbH
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
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderFromNamesEstate;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;

class TestClassFieldsCollectionBuilderFromNamesEstate
	extends \WP_UnitTestCase
{
	/** @var FieldsCollectionBuilderFromNamesEstate */
	private $_pSubject = null;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pSubject = new FieldsCollectionBuilderFromNamesEstate();
	}

	/**
	 *
	 */
	public function testBuildFieldsCollectionFromBaseCollection()
	{
		$pBaseFieldsCollection = $this->buildExampleFieldsCollection();
		$pNewFieldsCollection = $this->_pSubject->buildFieldsCollectionFromBaseCollection
			(['testEstate'], $pBaseFieldsCollection);
		$pExpectedFieldsCollection = $this->buildExpectedFieldsCollection();
		$this->assertEquals($pExpectedFieldsCollection, $pNewFieldsCollection);
	}

	public function testBuildFieldsCollectionUnknownField()
	{
		$this->expectException(\onOffice\WPlugin\Field\UnknownFieldException::class);
		$pBaseFieldsCollection = $this->buildExampleFieldsCollection();
		$this->_pSubject->buildFieldsCollectionFromBaseCollection(['testUnknown'], $pBaseFieldsCollection);
	}

	/**
	 * @return FieldsCollection
	 */
	private function buildExpectedFieldsCollection(): FieldsCollection
	{
		$exampleFields = $this->buildExampleFields();
		$pFieldsCollection = new FieldsCollection;
		$pFieldsCollection->addField($exampleFields[0]);
		return $pFieldsCollection;
	}

	/**
	 * @return FieldsCollection
	 */
	private function buildExampleFieldsCollection(): FieldsCollection
	{
		$pFieldsCollectionInner = new FieldsCollection;
		$fieldsAddressEstate = $this->buildExampleFields();
		$pFieldsCollectionInner->addField($fieldsAddressEstate[0]);
		return $pFieldsCollectionInner;
	}

	/**
	 * @return array
	 */
	private function buildExampleFields(): array
	{
		return [
			new Field('testEstate', onOfficeSDK::MODULE_ESTATE),
		];
	}
}
