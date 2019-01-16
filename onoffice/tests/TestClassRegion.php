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

use onOffice\WPlugin\Region\Region;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassRegion
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testConstruct()
	{
		$pRegion = new Region('testId', 'FRA');
		$this->assertEquals([], $pRegion->getChildren());
		$this->assertEquals('', $pRegion->getCountry());
		$this->assertEquals('', $pRegion->getDescription());
		$this->assertEquals('testId', $pRegion->getId());
		$this->assertEquals('FRA', $pRegion->getLanguage());
		$this->assertEquals('', $pRegion->getName());
		$this->assertEquals([], $pRegion->getPostalCodes());
		$this->assertEquals('', $pRegion->getState());
	}


	/**
	 *
	 */

	public function testGetterSetter()
	{
		$pRegion = new Region('IdParis', 'FRA');
		$pSubRegion = new Region('subTest', 'FRA');
		$pRegion->setChildren([$pSubRegion]);
		$this->assertEquals([$pSubRegion], $pRegion->getChildren());
		$pRegion->setCountry('France');
		$this->assertEquals('France', $pRegion->getCountry());
		$pRegion->setDescription('Une déscription');
		$this->assertEquals('Une déscription', $pRegion->getDescription());
		$this->assertEquals('IdParis', $pRegion->getId());
		$pRegion->setName('Paris');
		$this->assertEquals('Paris', $pRegion->getName());
		$pRegion->setPostalCodes(range(75000, 75020));
		$this->assertEquals([
			75000, 75001, 75002, 75003, 75004, 75005, 75006, 75007, 75008, 75009, 75010, 75011,
			75012, 75013, 75014, 75015, 75016, 75017, 75018, 75019, 75020,
		], $pRegion->getPostalCodes());
		$pRegion->setState('Département de Paris');
		$this->assertEquals('Département de Paris', $pRegion->getState());
	}
}
