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

use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelLabel;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassInputModelLabel
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testValues()
	{
		$pModel = new InputModelLabel('hello', 'world');

		$this->assertEquals('hello', $pModel->getLabel());
		$this->assertEquals('world', $pModel->getValue());
		$this->assertEquals(InputModelLabel::VALUE_ENCLOSURE_ITALIC, $pModel->getValueEnclosure());
		$this->assertEmpty($pModel->getIdentifier());

		$pModel->setValueEnclosure(InputModelLabel::VALUE_ENCLOSURE_CODE);
		$this->assertEquals(InputModelLabel::VALUE_ENCLOSURE_CODE, $pModel->getValueEnclosure());
	}


	/**
	 *
	 */

	public function testType()
	{
		$pModel = new InputModelLabel('test', 'hi');
		$this->assertEquals(InputModelBase::HTML_TYPE_LABEL, $pModel->getHtmlType());
	}
}
