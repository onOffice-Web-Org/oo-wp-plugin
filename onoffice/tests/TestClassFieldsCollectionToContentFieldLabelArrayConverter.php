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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\Collection\FieldsCollectionToContentFieldLabelArrayConverter;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use WP_UnitTestCase;


/**
 *
 */

class TestClassFieldsCollectionToContentFieldLabelArrayConverter
	extends WP_UnitTestCase
{
	/** @var FieldsCollectionToContentFieldLabelArrayConverter */
	private $_pSubject = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pSubject = new FieldsCollectionToContentFieldLabelArrayConverter();
	}


	/**
	 *
	 */

	public function testConvert()
	{
		$pFieldsCollection = $this->buildFieldsCollection();
		$expectedResult = [
			'testCategory2' => [
				'testField3' => 'A Test Label 3',
				'testField1' => 'Z Test Label 1',
			],
			'testCategory1' => [
				'testField2' => 'C Test Label 2',
			],
		];

		$result = $this->_pSubject->convert($pFieldsCollection, onOfficeSDK::MODULE_ADDRESS);
		$this->assertSame($expectedResult, $result);
	}


	/**
	 *
	 * @return FieldsCollection
	 *
	 */

	private function buildFieldsCollection(): FieldsCollection
	{
		$pField1 = new Field('testField1', onOfficeSDK::MODULE_ADDRESS);
		$pField1->setLabel('Z Test Label 1');
		$pField1->setCategory('testCategory2');

		$pField2 = new Field('testField2', onOfficeSDK::MODULE_ADDRESS);
		$pField2->setLabel('C Test Label 2');
		$pField2->setCategory('testCategory1');

		$pField3 = new Field('testField3', onOfficeSDK::MODULE_ADDRESS);
		$pField3->setLabel('A Test Label 3');
		$pField3->setCategory('testCategory2');

		$pField4 = new Field('testField4', onOfficeSDK::MODULE_ESTATE);
		$pField4->setLabel('Test Label 4');
		$pField4->setCategory('testCategory1');

		$pFieldsCollection = new FieldsCollection;
		$pFieldsCollection->addField($pField1);
		$pFieldsCollection->addField($pField2);
		$pFieldsCollection->addField($pField3);
		$pFieldsCollection->addField($pField4);
		return $pFieldsCollection;
	}
}
