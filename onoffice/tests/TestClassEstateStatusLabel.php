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
use onOffice\WPlugin\Field\UnknownFieldException;
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
	/** @var Fieldnames */
	private $_pFieldnamesInactive = null;

	/** @var Fieldnames */
	private $_pFieldnamesActive = null;


	/**
	 *
	 * @before
	 *
	 */

	public function setUpFieldnames()
	{
		$this->_pFieldnamesInactive = $this->getMock(Fieldnames::class,
			['getFieldLabel', 'getFieldInformation', 'loadLanguage'], [new FieldsCollection()]);
		$this->_pFieldnamesInactive->method('getFieldInformation')
			->will($this->returnCallback(function($field, $module) {
				if ($field === 'objekt_des_tages' || $module !== onOfficeSDK::MODULE_ESTATE) {
					throw new UnknownFieldException;
				}

				return ['label' => $field.'-label'];
			}));
		$this->_pFieldnamesActive = $this->getMock(Fieldnames::class,
			['getFieldLabel', 'getFieldInformation', 'loadLanguage'], [new FieldsCollection(), true]);
		$this->_pFieldnamesActive
			->method('getFieldInformation')
			->will($this->returnValueMap([
				[
					'vermarktungsart',
					onOfficeSDK::MODULE_ESTATE,
					[
						'permittedvalues' => [
							'miete' => 'rent',
							'kauf' => 'sale',
						],
					],

				], [
					'objekt_des_tages',
					onOfficeSDK::MODULE_ESTATE,
					['label' => 'objekt_des_tages-label'],

				],
			]));
	}


	/**
	 *
	 */

	public function testConstruct()
	{
		$pEstateStatusLabel = new EstateStatusLabel();
		$this->assertGreaterThan(3, $pEstateStatusLabel->getFieldsByPrio());
		$this->assertInstanceOf(Fieldnames::class, $pEstateStatusLabel->getFieldnamesActive());
		$this->assertInstanceOf(Fieldnames::class, $pEstateStatusLabel->getFieldnamesInactive());
		$this->assertNotEquals($pEstateStatusLabel->getFieldnamesActive(),
			$pEstateStatusLabel->getFieldnamesInActive());
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
			$values['vermarktungsart'] = 'erbpacht';

			$this->setUpFieldnames();
			$this->assertEquals($current.'-label', $this->getNewEstateStatusLabel()->getLabel($values));
		}
	}


	/**
	 *
	 */

	public function testGetLabelSoldForRent()
	{
		$values = [
			'reserviert' => '0',
			'verkauft' => '1',
			'vermarktungsart' => 'sale',
		];
		$this->setUpFieldnames();
		$this->assertEquals('sold', $this->getNewEstateStatusLabel()->getLabel($values));

		$values['vermarktungsart'] = 'rent';
		$this->assertEquals('rented', $this->getNewEstateStatusLabel()->getLabel($values));
	}


	/**
	 *
	 */

	public function testGetLabelEmpty()
	{
		$this->assertEquals('', $this->getNewEstateStatusLabel()->getLabel([]));
	}


	/**
	 *
	 * @return EstateStatusLabel
	 *
	 */

	private function getNewEstateStatusLabel(): EstateStatusLabel
	{
		$pEstateStatusLabel = new EstateStatusLabel($this->_pFieldnamesActive, $this->_pFieldnamesInactive);
		return $pEstateStatusLabel;
	}
}
