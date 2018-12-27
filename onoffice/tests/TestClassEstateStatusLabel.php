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

namespace onOffice\tests;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Types\EstateStatusLabel;
use onOffice\WPlugin\Types\FieldsCollection;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassEstateStatusLabel
	extends WP_UnitTestCase
{
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $_pFieldnames = null;


	/**
	 *
	 * @before
	 *
	 */

	public function setUpFieldnames()
	{
		$this->_pFieldnames = $this->getMock
			(Fieldnames::class, ['getFieldLabel', 'loadLanguage'], [new FieldsCollection()]);
	}


	/**
	 *
	 */

	public function testConstruct()
	{
		$pEstateStatusLabel = new EstateStatusLabel(['asdf' => '1']);
		$this->assertEquals(['asdf' => '1'], $pEstateStatusLabel->getEstateValues());
		$this->assertGreaterThan(3, $pEstateStatusLabel->getFieldsByPrio());
		$this->assertInstanceOf(Fieldnames::class, $pEstateStatusLabel->getFieldnames());
	}


	/**
	 *
	 */

	public function testGetLabel()
	{
		$fields = [
			'reserviert',
			'verkauft',
			'top_angebot',
			'preisreduktion',
			'courtage_frei',
			'objekt_des_tages',
			'neu',
		];

		for ($i = 1; $i <= count($fields); $i++) {
			$current = $fields[count($fields)-$i];
			$valuePart = array_fill(0, count($fields), '0');
			$values = array_combine($fields, $valuePart);
			$values[$current] = '1';

			$this->setUpFieldnames();
			$this->_pFieldnames->expects($this->any())
				->method('getFieldLabel')->with($this->equalTo($current), $this->equalTo(onOfficeSDK::MODULE_ESTATE))
				->will($this->returnValue($current.'-label'));
			$this->assertEquals($current.'-label', $this->getNewEstateStatusLabel($values)->getLabel());
		}
	}


	/**
	 *
	 */

	public function testGetLabelEmpty()
	{
		$this->assertEquals('', $this->getNewEstateStatusLabel([])->getLabel());
	}


	/**
	 *
	 * @param array $values
	 * @return EstateStatusLabel
	 *
	 */

	private function getNewEstateStatusLabel(array $values): EstateStatusLabel
	{
		$pEstateStatusLabel = new EstateStatusLabel($values, $this->_pFieldnames);
		return $pEstateStatusLabel;
	}
}
