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

use onOffice\WPlugin\AddressList;
use onOffice\WPlugin\Controller\EstateListEnvironmentDefault;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\UnknownViewException;
use onOffice\WPlugin\EstateFiles;
use onOffice\WPlugin\Field\OutputFields;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListView;
use onOffice\WPlugin\Filter\GeoSearchBuilderEmpty;
use onOffice\WPlugin\Filter\GeoSearchBuilderFromInputVars;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Types\EstateStatusLabel;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypes;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierHandler;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassEstateListEnvironmentDefault
	extends WP_UnitTestCase
{
	/** @var EstateListEnvironmentDefault */
	private $_pSubject = null;


	/**
	 *
	 */

	public function testGetAddressList()
	{
		$this->assertInstanceOf(AddressList::class, $this->_pSubject->getAddressList());
	}


	/**
	 *
	 */

	public function testGetEstateFiles()
	{
		$this->assertInstanceOf(EstateFiles::class, $this->_pSubject->getEstateFiles([]));
	}


	/**
	 *
	 */

	public function testGetFieldnames()
	{
		$this->assertInstanceOf(Fieldnames::class, $this->_pSubject->getFieldnames());
	}


	/**
	 *
	 */

	public function testGetGeoSearchBuilder()
	{
		$this->assertInstanceOf(GeoSearchBuilderFromInputVars::class,
			$this->_pSubject->getGeoSearchBuilder());
	}


	/**
	 *
	 */

	public function testGetSDKWrapper()
	{
		$this->assertInstanceOf(SDKWrapper::class, $this->_pSubject->getSDKWrapper());
	}


	/**
	 *
	 * @depends testGetGeoSearchBuilder
	 *
	 */

	public function testSetGeoSearchBuilder()
	{
		$this->_pSubject->setGeoSearchBuilder(new GeoSearchBuilderEmpty);
		$this->assertInstanceOf(GeoSearchBuilderEmpty::class, $this->_pSubject->getGeoSearchBuilder());
	}


	/**
	 *
	 * @expectedException onOffice\WPlugin\DataView\UnknownViewException
	 *
	 */

	public function testGetEmptyDefaultFilterBuilder()
	{
		$this->_pSubject->getDefaultFilterBuilder();
	}


	/**
	 *
	 * @depends testGetEmptyDefaultFilterBuilder
	 *
	 */

	public function testDefaultFilterBuilder()
	{
		$this->_pSubject->setDefaultFilterBuilder
			(new DefaultFilterBuilderListView(new DataListView(1, 'test')));
		$this->assertInstanceOf
			(DefaultFilterBuilderListView::class, $this->_pSubject->getDefaultFilterBuilder());
	}


	/**
	 *
	 */

	public function testGetDataDetailView()
	{
		$this->assertInstanceOf(DataDetailView::class, $this->_pSubject->getDataDetailView());
	}


	/**
	 *
	 */

	public function testGetOutputFields()
	{
		$pOutputFields = $this->_pSubject->getOutputFields(new DataListView(1, 'test'));
		$this->assertInstanceOf(OutputFields::class, $pOutputFields);
	}


	/**
	 *
	 */

	public function testShuffle()
	{
		$values = [1, 2, 3, 4, 5, 6];
		$valuesShuffled = $values;
		$this->_pSubject->shuffle($valuesShuffled);
		$this->assertEqualSets($values, $valuesShuffled);
	}


	/**
	 *
	 */

	public function testGetViewFieldModifierHandler()
	{
		$pViewFieldModifierHandler = $this->_pSubject->getViewFieldModifierHandler
			([], EstateViewFieldModifierTypes::MODIFIER_TYPE_DEFAULT);
		$this->assertInstanceOf(ViewFieldModifierHandler::class, $pViewFieldModifierHandler);
	}


	/**
	 *
	 */

	public function testGetEstateStatusLabel()
	{
		$this->assertInstanceOf(EstateStatusLabel::class, $this->_pSubject->getEstateStatusLabel());
	}


	/**
	 *
	 * @before
	 *
	 */

	public function generate()
	{
		$this->_pSubject = new EstateListEnvironmentDefault();
	}
}
