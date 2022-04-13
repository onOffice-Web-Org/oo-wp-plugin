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

use onOffice\tests\ViewFieldModifierTypesTestBase;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypes;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 * @covers onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypes
 *
 */

class TestClassEstateViewFieldModifierTypes
	extends ViewFieldModifierTypesTestBase
{
	/**
	 *
	 */

	public function setUp()
	{
		parent::set_up();
		$this->setViewFieldModifierTypes(new EstateViewFieldModifierTypes);
	}


	/**
	 *
	 */

	public function testGetForbiddenAPIFields()
	{
		$pViewFieldModifierTypes = $this->getViewFieldModifierTypes();
		$this->assertEquals([GeoPosition::FIELD_GEO_POSITION],
			$pViewFieldModifierTypes->getForbiddenAPIFields());
	}
}
