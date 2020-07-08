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

use onOffice\WPlugin\Types\ImageTypes;
use ReflectionClass;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassImageTypes
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testIsImageType()
	{
		$this->assertTrue(ImageTypes::isImageType(ImageTypes::PHOTO));
		$this->assertTrue(ImageTypes::isImageType(ImageTypes::ENERGY_PASS_RANGE));
		$this->assertTrue(ImageTypes::isImageType(ImageTypes::PANORAMA));
		$this->assertTrue(ImageTypes::isImageType(ImageTypes::PHOTO_BIG));
		$this->assertFalse(ImageTypes::isImageType('unknown'));
	}


	/**
	 *
	 */

	public function testGetAllImageTypesTranslated()
	{
		$pReflection = new ReflectionClass(ImageTypes::class);
		$nameConstants = $pReflection->getConstants();
		unset($nameConstants['IMAGE_TYPES']);
		$this->assertEqualSets($nameConstants, array_keys(ImageTypes::getAllImageTypesTranslated()));
	}
}
