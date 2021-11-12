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

use onOffice\WPlugin\Model\InputModelOption;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */
class TestClassInputModelOption
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGetAndSetValues()
	{
		$pModel = new InputModelOption('hello', 'world', 'label', 'type');
		$pModel->setType('Type');
		$pModel->setDescription('Description');
		$pModel->setDefault('Default');
		$pModel->setShowInRest(true);
		$pModel->setSanitizeCallback(function () {
			return true;
		});
		$this->assertEquals($pModel->getType(), "Type");
		$this->assertEquals($pModel->getDescription(), "Description");
		$this->assertEquals($pModel->getDefault(), "Default");
		$this->assertEquals($pModel->getShowInRest(), true);
		$this->assertNotEmpty($pModel->getSanitizeCallback());
	}
}
