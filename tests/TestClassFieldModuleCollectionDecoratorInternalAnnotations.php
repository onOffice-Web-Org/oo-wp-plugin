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

declare (strict_types=1);

namespace onOffice\tests;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorInternalAnnotations;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorReadAddress;
use onOffice\WPlugin\Types\FieldsCollection;
use WP_UnitTestCase;

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

	public function testGetAllFields()
	{
		$pDecoratorAddress = new FieldModuleCollectionDecoratorReadAddress(new FieldsCollection);
		$pDecoratorAnnotated = new FieldModuleCollectionDecoratorInternalAnnotations($pDecoratorAddress);

		$this->assertEquals(count($pDecoratorAddress->getAllFields()), count($pDecoratorAnnotated->getAllFields()));
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
