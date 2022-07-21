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

use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypeTitle;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 * @covers \onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypeTitle
 *
 */

class TestClassEstateViewFieldModifierTypeTitle
	extends WP_UnitTestCase
{
	/** @var array */
	private $_exampleRecord = [
		'test1' => 'test',
		'test2' => 'hi',
		'objekttitel' => 'Nice Estate',
		'objektart' => 'haus',
		'vermarktungsart' => 'Kauf',
		'ort' => 'Aachen',
		'objektnr_extern' => 'JJ1337',
	];


	/**
	 *
	 */

	public function testApiFields()
	{
		$expectation = [
			'test1',
			'test2',
			'objekttitel',
			'objektart',
			'vermarktungsart',
			'ort',
			'objektnr_extern',
		];

		$pEstateViewFieldModifierTypeTitle = new EstateViewFieldModifierTypeTitle(['test1', 'test2']);
		$apiFields = $pEstateViewFieldModifierTypeTitle->getAPIFields();
		$this->assertEquals($expectation, $apiFields);
	}


	/**
	 *
	 */

	public function testVisibleFields()
	{
		$expectation = [
			'test1',
			'test2',
			'objekttitel',
			'objektart',
			'vermarktungsart',
			'ort',
			'objektnr_extern',
		];

		$pEstateViewFieldModifierTypeTitle = new EstateViewFieldModifierTypeTitle(['test1', 'test2']);
		$visibleFields = $pEstateViewFieldModifierTypeTitle->getVisibleFields();
		$this->assertEquals($expectation, $visibleFields);
	}


	/**
	 *
	 */

	public function testReduceRecord()
	{
		$pEstateViewFieldModifierTypeTitle = new EstateViewFieldModifierTypeTitle(['test1', 'test2']);
		$record = $pEstateViewFieldModifierTypeTitle->reduceRecord($this->_exampleRecord);
		// this won't do any change
		$this->assertEquals($this->_exampleRecord, $record);
	}
	
	/**
	 * @covers onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypeTitle::getAPICustomFields
	 */
	public function testApiCustomFields()
	{
		$expectation = [
			'objekttitel',
			'objektart',
			'objekttyp',
			'vermarktungsart',
			'plz',
			'ort',
			'bundesland',
			'objektnr_extern',
			'wohnflaeche',
			'grundstuecksflaeche',
			'nutzflaeche',
			'anzahl_zimmer',
			'anzahl_badezimmer',
			'kaufpreis',
			'kaltmiete',
			'objektbeschreibung',
			'lage',
			'ausstatt_beschr',
			'sonstige_angaben',
		];
		
		$pEstateViewFieldModifierTypeTitle = new EstateViewFieldModifierTypeTitle(['test1', 'test2']);
		$apiCustomFields = $pEstateViewFieldModifierTypeTitle->getAPICustomFields();
		$this->assertEquals($expectation, $apiCustomFields);
	}
	
	/**
	 * @covers onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypeTitle::getVisibleCustomFields
	 */
	
	public function testVisibleCustomFields()
	{
		$expectation = [
			'objekttitel',
			'objektart',
			'objekttyp',
			'vermarktungsart',
			'plz',
			'ort',
			'bundesland',
			'objektnr_extern',
			'wohnflaeche',
			'grundstuecksflaeche',
			'nutzflaeche',
			'anzahl_zimmer',
			'anzahl_badezimmer',
			'kaufpreis',
			'kaltmiete',
			'objektbeschreibung',
			'lage',
			'ausstatt_beschr',
			'sonstige_angaben',
		];
		
		$pEstateViewFieldModifierTypeTitle = new EstateViewFieldModifierTypeTitle(['test1', 'test2']);
		$visibleCustomFields = $pEstateViewFieldModifierTypeTitle->getVisibleCustomFields();
		$this->assertEquals($expectation, $visibleCustomFields);
	}
}
