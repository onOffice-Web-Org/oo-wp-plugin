<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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
use onOffice\WPlugin\Model\ExceptionInputModelMissingField;
use WP_UnitTestCase;
use onOffice\WPlugin\Model\InputModel\InputModelOptionFactoryAddressDetailView;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2024, onOffice(R) GmbH
 *
 */

class TestClassInputModelOptionFactoryAddressDetailView
	extends WP_UnitTestCase
{

	/** @var string */
	private $_optionGroup = 'onoffice';

	/**
	 *
	 */

	public function testConstruct()
	{
		$pInputModelOptionFactoryAddressDetailView = new InputModelOptionFactoryAddressDetailView($this->_optionGroup);
		$this->assertInstanceOf(InputModelOptionFactoryAddressDetailView::class,
			$pInputModelOptionFactoryAddressDetailView);
	}

	public function testCreate()
	{
		$pInputModelOptionFactoryAddressDetailView = new InputModelOptionFactoryAddressDetailView($this->_optionGroup);
		$label = 'Label Test';
		$pInputModelOption = $pInputModelOptionFactoryAddressDetailView->create('fields', $label);

		$this->assertEquals($pInputModelOption->getOptionGroup(), 'onoffice');
		$this->assertEquals($pInputModelOption->getName(), 'fields');
		$this->assertEquals($pInputModelOption->getDescriptionTextHTML(), null);
		$this->assertEquals($pInputModelOption->getDescriptionRadioTextHTML(), []);
	}

	public function testNoNameCreate()
	{
		$this->expectException(ExceptionInputModelMissingField::class);
		$pInputModelOptionFactoryAddressDetailView = new InputModelOptionFactoryAddressDetailView($this->_optionGroup);
		$label = 'Label Test';
		$pInputModelOptionFactoryAddressDetailView->create('test', $label);
	}
}
