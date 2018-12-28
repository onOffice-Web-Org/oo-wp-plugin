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
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\ViewFieldModifier\AddressViewFieldModifierTypeDefault;
use onOffice\WPlugin\ViewFieldModifier\AddressViewFieldModifierTypes;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypeDefault;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypeMap;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypes;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypeTitle;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierFactory;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassViewFieldModifierFactory
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testCreateForAddress()
	{
		$pViewFieldModifierFactory = new ViewFieldModifierFactory(onOfficeSDK::MODULE_ADDRESS);
		$pViewFieldModifier = $pViewFieldModifierFactory->create
			(AddressViewFieldModifierTypes::MODIFIER_TYPE_DEFAULT, ['test', 'test-asdf']);
		$this->assertEquals(['test', 'asdf'], $pViewFieldModifier->getAPIFields());
		$this->assertEquals(['test', 'test-asdf'], $pViewFieldModifier->getVisibleFields());
		$this->assertInstanceOf(AddressViewFieldModifierTypeDefault::class, $pViewFieldModifier);
	}


	/**
	 *
	 */

	public function testCreateForEstate()
	{
		$pViewFieldModifierFactory = new ViewFieldModifierFactory(onOfficeSDK::MODULE_ESTATE);
		$pViewFieldModifierDefault = $pViewFieldModifierFactory->create
			(EstateViewFieldModifierTypes::MODIFIER_TYPE_DEFAULT);
		$this->assertInstanceOf(EstateViewFieldModifierTypeDefault::class, $pViewFieldModifierDefault);

		$pViewFieldModifierMap = $pViewFieldModifierFactory->create
			(EstateViewFieldModifierTypes::MODIFIER_TYPE_MAP);
		$this->assertInstanceOf(EstateViewFieldModifierTypeMap::class, $pViewFieldModifierMap);

		$pViewFieldModifierTitle = $pViewFieldModifierFactory->create
			(EstateViewFieldModifierTypes::MODIFIER_TYPE_TITLE);
		$this->assertInstanceOf(EstateViewFieldModifierTypeTitle::class, $pViewFieldModifierTitle);
	}


	/**
	 *
	 * @expectedException UnexpectedValueException
	 *
	 */

	public function testCreateUnknown()
	{
		$pViewFieldModifierFactory = new ViewFieldModifierFactory(onOfficeSDK::MODULE_ESTATE);
		$pViewFieldModifierFactory->create('unknown');
	}


	/**
	 *
	 * @expectedException UnexpectedValueException
	 *
	 */

	public function testGetMappingClassUnknown()
	{
		$pViewFieldModifierFactory = new ViewFieldModifierFactory('unknownModule');
		$pViewFieldModifierFactory->getMapping();
	}


	/**
	 *
	 */

	public function testGetMappingAddress()
	{
		$pViewFieldModifierFactory = new ViewFieldModifierFactory(onOfficeSDK::MODULE_ADDRESS);
		$this->checkMapping($pViewFieldModifierFactory);
	}


	/**
	 *
	 */

	public function testGetMappingEstate()
	{
		$pViewFieldModifierFactory = new ViewFieldModifierFactory(onOfficeSDK::MODULE_ESTATE);
		$this->checkMapping($pViewFieldModifierFactory);
	}


	/**
	 *
	 */

	public function testGetForbiddenAPIFields()
	{
		$pViewFieldModifierFactoryEstate = new ViewFieldModifierFactory(onOfficeSDK::MODULE_ESTATE);
		$this->assertEquals([GeoPosition::FIELD_GEO_POSITION],
			$pViewFieldModifierFactoryEstate->getForbiddenAPIFields());

		$pViewFieldModifierFactoryAddress = new ViewFieldModifierFactory(onOfficeSDK::MODULE_ADDRESS);
		$this->assertEquals([], $pViewFieldModifierFactoryAddress->getForbiddenAPIFields());
	}


	/**
	 *
	 * @param ViewFieldModifierFactory $pViewFieldModifierFactory
	 *
	 */

	private function checkMapping(ViewFieldModifierFactory $pViewFieldModifierFactory)
	{
		$mapping = $pViewFieldModifierFactory->getMapping();
		$this->assertGreaterThanOrEqual(1, count($mapping));
	}
}
