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
use onOffice\WPlugin\Field\Collection\FieldsCollectionConfiguratorEstate;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;

class TestClassFieldsCollectionConfiguratorEstate
	extends \WP_UnitTestCase
{
	/**
	 * @throws UnknownFieldException
	 */
	public function testConfigureForEstate()
	{
		$pFieldsCollection = $this->buildFieldsCollectionSelect();
		$pSubject = new FieldsCollectionConfiguratorEstate;
		$pFieldsCollectionNew = $pSubject->configureFoEstate($pFieldsCollection);
		$this->assertCount(1, $pFieldsCollectionNew->getAllFields());
		foreach ($pFieldsCollectionNew->getAllFields() as $pField) {
			$pFieldOriginal = $pFieldsCollection->getFieldByModuleAndName
				($pField->getModule(), $pField->getName());
			$this->assertNotSame($pFieldOriginal, $pField);
			$this->assertThat($pField, $this->callback(function(Field $pField) {
				return
					($pField->getType() === FieldTypes::FIELD_TYPE_MULTISELECT);
			}));
		}
	}

	/**
	 * @throws UnknownFieldException
	 */
	public function testBuildForEstateType()
	{
		$pSubject = new FieldsCollectionConfiguratorEstate;
		$pFieldsCollection = new FieldsCollection;
		$pFieldSingleSelect = new Field('testFieldSingleselect', onOfficeSDK::MODULE_ESTATE);
		$pFieldSingleSelect->setType(FieldTypes::FIELD_TYPE_SINGLESELECT);
		$pFieldsCollection->addField($pFieldSingleSelect);
		$pCollectionInterest = $pSubject->buildForEstateType($pFieldsCollection);
		$this->assertEquals(FieldTypes::FIELD_TYPE_MULTISELECT,
			$pCollectionInterest->getFieldByKeyUnsafe('testFieldSingleselect')->getType());
	}

	/**
	 * @return FieldsCollection
	 */
	private function buildFieldsCollectionSelect(): FieldsCollection
	{
		$pFieldsCollection = new FieldsCollection;
		$pField = new Field('testFieldSingleselect', onOfficeSDK::MODULE_ESTATE);
		$pField->setType(FieldTypes::FIELD_TYPE_SINGLESELECT);
		$pFieldsCollection->addField($pField);
		return $pFieldsCollection;
	}
}