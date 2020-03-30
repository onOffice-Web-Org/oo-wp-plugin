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
use onOffice\WPlugin\Field\Collection\FieldsCollectionFieldDuplicatorForGeoEstate;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;

class TestClassFieldsCollectionFieldDuplicatorForGeoEstate
	extends \WP_UnitTestCase
{
	/**
	 * @throws UnknownFieldException
	 */
	public function testDuplicateFields()
	{
		$pSubject = new FieldsCollectionFieldDuplicatorForGeoEstate;
		$pFieldsCollection = new FieldsCollection;
		$pFieldLand = new Field('land', onOfficeSDK::MODULE_ESTATE);
		$pFieldLand->setPermittedvalues(['asd1', 'asd2']);
		$pFieldsCollection->addField($pFieldLand);
		$this->assertCount(1, $pFieldsCollection->getAllFields());
		$pSubject->duplicateFields($pFieldsCollection);
		$this->assertCount(2, $pFieldsCollection->getAllFields());
		$pFieldCountry = $pFieldsCollection->getFieldByModuleAndName(onOfficeSDK::MODULE_ESTATE, 'country');
		$this->assertSame(['asd1', 'asd2'], $pFieldCountry->getPermittedvalues());
	}

	public function testDuplicateFieldsWithoutFields()
	{
		$pSubject = new FieldsCollectionFieldDuplicatorForGeoEstate;
		$pFieldsCollection = new FieldsCollection;
		$pSubject->duplicateFields($pFieldsCollection);
		$this->assertCount(0, $pFieldsCollection->getAllFields());
	}
}