<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorInternalAnnotations;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorReadAddress;
use onOffice\WPlugin\Types\FieldsCollection;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassFieldModuleCollectionDecoratorInternalAnnotations
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGetFieldByModuleAndName()
	{
		$pDecoratorAddress = new FieldModuleCollectionDecoratorReadAddress(new FieldsCollection);
		$module = onOfficeSDK::MODULE_ADDRESS;
		$pFieldDefaultPhoneExternal = $pDecoratorAddress->getFieldByModuleAndName($module, 'defaultphone');
		$pFieldDefaultMailExternal = $pDecoratorAddress->getFieldByModuleAndName($module, 'defaultemail');
		$pFieldDefaultFaxExternal = $pDecoratorAddress->getFieldByModuleAndName($module, 'defaultfax');
		$this->assertEquals('Phone', $pFieldDefaultPhoneExternal ->getLabel());
		$this->assertEquals('E-Mail', $pFieldDefaultMailExternal->getLabel());
		$this->assertEquals('Fax', $pFieldDefaultFaxExternal->getLabel());

		$pDecoratorAnnotations = new FieldModuleCollectionDecoratorInternalAnnotations
			($pDecoratorAddress);
		$pFieldDefaultPhone = $pDecoratorAnnotations->getFieldByModuleAndName($module, 'defaultphone');
		$pFieldDefaultMail = $pDecoratorAnnotations->getFieldByModuleAndName($module, 'defaultemail');
		$pFieldDefaultFax = $pDecoratorAnnotations->getFieldByModuleAndName($module, 'defaultfax');
		$this->assertEquals('Phone (Marked as default in onOffice)', $pFieldDefaultPhone->getLabel());
		$this->assertEquals('E-Mail (Marked as default in onOffice)', $pFieldDefaultMail->getLabel());
		$this->assertEquals('Fax (Marked as default in onOffice)', $pFieldDefaultFax->getLabel());
	}


	/**
	 *
	 */

	public function testGetAllFields()
	{
		$pDecoratorAddress = new FieldModuleCollectionDecoratorReadAddress(new FieldsCollection);
		$pDecoratorAnnotated = new FieldModuleCollectionDecoratorInternalAnnotations($pDecoratorAddress);

		$this->assertEquals(count($pDecoratorAddress), count($pDecoratorAnnotated));
		$newFieldAnnotations = $pDecoratorAnnotated->getFieldAnnotations();

		foreach ($pDecoratorAnnotated->getAllFields() as $pField) {
			$module = $pField->getModule();
			$name = $pField->getName();
			$newLabel = $newFieldAnnotations[$module][$name] ?? null;
			if ($newLabel !== null) {
				$this->assertEquals($newLabel, $pField->getLabel());
			}
		}
	}
}
