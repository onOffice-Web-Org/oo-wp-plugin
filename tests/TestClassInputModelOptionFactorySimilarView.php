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
use onOffice\WPlugin\DataView\DataSimilarView;
use onOffice\WPlugin\Model\ExceptionInputModelMissingField;
use onOffice\WPlugin\Model\InputModel\InputModelOptionFactorySimilarView;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassInputModelOptionFactorySimilarView
	extends WP_UnitTestCase
{

	/** @var string */
	private $_optionGroup = 'onoffice';

	/**
	 *
	 */

	public function testConstruct()
	{
		$pInputModelOptionFactorySimilarView = new InputModelOptionFactorySimilarView($this->_optionGroup);
		$this->assertInstanceOf(InputModelOptionFactorySimilarView::class,
			$pInputModelOptionFactorySimilarView);
	}

	public function testCreate()
	{
		$pInputModelOptionFactorySimilarView = new InputModelOptionFactorySimilarView($this->_optionGroup);
		$label = 'Label Test';
		$pInputModelOption = $pInputModelOptionFactorySimilarView->create('enablesimilarestates', $label);

		$this->assertEquals($pInputModelOption->getOptionGroup(), 'onoffice');
		$this->assertEquals($pInputModelOption->getName(), 'enablesimilarestates');
		$this->assertEquals($pInputModelOption->getDescriptionTextHTML(), null);
	}

	public function testNoNameCreate()
	{
		$this->expectException(ExceptionInputModelMissingField::class);
		$pInputModelOptionFactorySimilarView = new InputModelOptionFactorySimilarView($this->_optionGroup);
		$label = 'Label Test';
		$pInputModelOptionFactorySimilarView->create('test', $label);
	}
}
