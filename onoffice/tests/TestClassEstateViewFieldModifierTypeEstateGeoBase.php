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

use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypeEstateGeoBase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassEstateViewFieldModifierTypeEstateGeoBase
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGetApiFields()
	{
		$viewFields = [GeoPosition::FIELD_GEO_POSITION, 'testField'];
		$pViewFieldModifier = $this->generateMockObject($viewFields);
		$result = $pViewFieldModifier->getAPIFields();
		$expectedResult = [
			'testField',
			'virtualStreet',
			'virtualHouseNumber',
			'laengengrad',
			'breitengrad',
			'virtualAddress',
			'virtualLatitude',
			'virtualLongitude',
			'objektadresse_freigeben',
			'strasse',
		];
		$this->assertEqualSets($expectedResult, $result);
	}


	/**
	 *
	 */

	public function testGetVisibleFields()
	{
		$viewFields = [GeoPosition::FIELD_GEO_POSITION, 'testField'];
		$pViewFieldModifier = $this->generateMockObject($viewFields);

		$expectedVisibleFields = ['breitengrad', 'laengengrad', 'testField'];
		$this->assertEqualSets($expectedVisibleFields, $pViewFieldModifier->getVisibleFields());
	}


	/**
	 *
	 */

	public function testReduceRecordVirtual()
	{
		$viewFields = [GeoPosition::FIELD_GEO_POSITION, 'testField'];
		$pViewFieldModifier = $this->generateMockObject($viewFields);

		$record = [
			'virtualAddress' => '1',
			'strasse' => 'Echte Straße',
			'virtualStreet' => 'Virtuelle Straße',
			'hausnummer' => '10',
			'virtualHouseNumber' => '20',
			'laengengrad' => '13.3672133',
			'breitengrad' => '52.5250839',
			'virtualLongitude' => '13.411026',
			'virtualLatitude' => '52.5219184',
		];
		$result = $pViewFieldModifier->reduceRecord($record);

		$expectedResult = [
			'virtualAddress' => '1',
			'strasse' => 'Virtuelle Straße',
			'virtualStreet' => 'Virtuelle Straße',
			'hausnummer' => '20',
			'virtualHouseNumber' => '20',
			'laengengrad' => '13.411026',
			'breitengrad' => '52.5219184',
			'virtualLongitude' => '13.411026',
			'virtualLatitude' => '52.5219184',
		];
		$this->assertEquals($expectedResult, $result);
	}


	/**
	 *
	 */

	public function testReduceRecordHideAddress()
	{
		$viewFields = [GeoPosition::FIELD_GEO_POSITION, 'testField'];
		$pViewFieldModifier = $this->generateMockObject($viewFields);

		$record = [
			'virtualAddress' => '0',
			'objektadresse_freigeben' => '0',
			'strasse' => 'Echte Straße',
			'hausnummer' => '10',
			'laengengrad' => '13.3672133',
			'breitengrad' => '52.5250839',
		];

		$expectedResult = [
			'virtualAddress' => '0',
			'objektadresse_freigeben' => '0',
			'hausnummer' => '10',
			'laengengrad' => 0,
			'breitengrad' => 0,
		];

		$this->assertEquals($expectedResult, $pViewFieldModifier->reduceRecord($record));
		unset($record['strasse']);
		$record['objektadresse_freigeben'] = '1';
		$expectedResult['objektadresse_freigeben'] = '1';
		$this->assertEquals($expectedResult, $pViewFieldModifier->reduceRecord($record));
	}


	/**
	 *
	 */

	public function testGetViewFields()
	{
		$viewFields = [GeoPosition::FIELD_GEO_POSITION, 'testField'];
		$pViewFieldModifier = $this->generateMockObject($viewFields);

		$pClosure = function() {return $this->getViewFields();};
		$result = Closure::bind($pClosure, $pViewFieldModifier,
			EstateViewFieldModifierTypeEstateGeoBase::class)();
		$this->assertEquals($viewFields, $result);
	}


	/**
	 *
	 * @param array $viewFields
	 * @return EstateViewFieldModifierTypeEstateGeoBase
	 *
	 */

	private function generateMockObject(array $viewFields): EstateViewFieldModifierTypeEstateGeoBase
	{
		return $this->getMockForAbstractClass
			(EstateViewFieldModifierTypeEstateGeoBase::class, [$viewFields]);
	}
}