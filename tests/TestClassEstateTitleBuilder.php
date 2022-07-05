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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\ArrayContainer;
use onOffice\WPlugin\Controller\EstateTitleBuilder;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\EstateDetail;
use onOffice\WPlugin\Filter\DefaultFilterBuilderDetailView;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypes;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypeTitle;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierFactory;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 * @covers \onOffice\WPlugin\Controller\EstateTitleBuilder
 *
 */

class TestClassEstateTitleBuilder
	extends WP_UnitTestCase
{
	/** @var EstateTitleBuilder */
	private $_pEstateTitleBuilder = null;


	/**
	 *
	 */

	public function testConstruct()
	{
		$pEstateTitleBuilder = new EstateTitleBuilder();
		$this->assertInstanceOf(DataDetailViewHandler::class,
			$pEstateTitleBuilder->getDataDetailViewHandler());
		$this->assertInstanceOf(DefaultFilterBuilderDetailView::class,
			$pEstateTitleBuilder->getDefaultFilterBuilder());
		$this->assertInstanceOf(EstateDetail::class, $pEstateTitleBuilder->getEstateDetail());
		$this->assertInstanceOf(ViewFieldModifierFactory::class,
			$pEstateTitleBuilder->getViewFieldModifierFactory());
	}


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$pViewFieldModifierFactory = $this->getMockBuilder(ViewFieldModifierFactory::class)
			->setConstructorArgs([onOfficeSDK::MODULE_ESTATE])
			->getMock();
		$pDataDetailViewHandler = $this->getMockBuilder(DataDetailViewHandler::class)
			->getMock();
		$pEstateDetail = $this->getMockBuilder(EstateDetail::class)
			->setConstructorArgs([new DataDetailView()])
			->getMock();
		$pDefaultFilterBuilder = $this->getMockBuilder(DefaultFilterBuilderDetailView::class)
			->getMock();

		$this->_pEstateTitleBuilder = new EstateTitleBuilder
			($pViewFieldModifierFactory, $pDataDetailViewHandler, $pEstateDetail, $pDefaultFilterBuilder);
	}


	/**
	 *
	 * @return ArrayContainer
	 *
	 */

	private function getArrayContainer(): ArrayContainer
	{
		$keyValues = [
			'objekttitel' => 'Beautiful Apartment',
			'objektart' => 'House',
			'vermarktungsart' => 'Sale',
			'ort' => 'Aachen',
			'objektnr_extern' => 'JD133',
		];

		$pArrayContainer = new ArrayContainer($keyValues);
		return $pArrayContainer;
	}
	
	/**
	 * @covers \onOffice\WPlugin\Controller\EstateTitleBuilder::buildCustomFieldTitle
	 */
	public function testBuildCustomFieldTitle()
	{
		$format = [
			"onoffice_titel"            => 'objekttitel',
			"onoffice_title"            => 'objekttitel',
			"onoffice_beschreibung"     => 'objektbeschreibung',
			"onoffice_description"      => 'objektbeschreibung',
			"onoffice_ort"              => 'ort',
			"onoffice_city"             => 'ort',
			"onoffice_plz"              => 'plz',
			"onoffice_postal_code"      => 'plz',
			"onoffice_objektart"        => 'objektart',
			"onoffice_property_class"   => 'objektart',
			"onoffice_vermarktungsart"  => 'vermarktungsart',
			"onoffice_marketing_method" => 'vermarktungsart',
			"onoffice_datensatznr"      => 'Id',
			"onoffice_id"               => 'Id',
			"onoffice_immo_nr"          => 'objektnr_extern',
			"onoffice_prop_no"          => 'objektnr_extern'
		];
		$pViewFieldModifier = new EstateViewFieldModifierTypeTitle([]);
		$pViewFieldModifierFactory = $this->_pEstateTitleBuilder->getViewFieldModifierFactory();
		$pViewFieldModifierFactory->expects($this->exactly(2))->method('create')
			->with(EstateViewFieldModifierTypes::MODIFIER_TYPE_TITLE)
			->will($this->returnValue($pViewFieldModifier));
		$pEstateList = $this->_pEstateTitleBuilder->getEstateDetail();
		$pEstateList->expects($this->exactly(2))->method('loadSingleEstate')->with(3);
		$pEstateList->expects($this->exactly(2))->method('estateIterator')
			->with(EstateViewFieldModifierTypes::MODIFIER_TYPE_TITLE)
			->will($this->onConsecutiveCalls($this->getArrayContainer(), false));
		$pDefaultFilterBuilder = $this->_pEstateTitleBuilder->getDefaultFilterBuilder();
		$pDefaultFilterBuilder->expects($this->exactly(2))->method('setEstateId')->with(3);
		$title = $this->_pEstateTitleBuilder->buildCustomFieldTitle(3, $format['onoffice_titel']);
		
		$this->assertEquals('Beautiful Apartment', $title);
		
		$this->assertEmpty($this->_pEstateTitleBuilder->buildCustomFieldTitle(3, ''));
	}
	
}