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

use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelButtonShowPublishedProperites;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2023, onOffice(R) GmbH
 *
 */

class TestClassInputModelButtonShowPublishedProperites
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testLabel()
	{
		$pModel = new InputModelButtonShowPublishedProperites('Show published properties');

		$this->assertEquals('Show published properties', $pModel->getLabel());
		$this->assertEmpty($pModel->getIdentifier());
	}


	/**
	 *
	 */

	public function testType()
	{
		$pModel = new InputModelButtonShowPublishedProperites('Show published properties');
		$this->assertEquals(InputModelBase::HTML_TYPE_BUTTON_SHOW_PUBLISHED_PROPERTIES, $pModel->getHtmlType());
	}
}
