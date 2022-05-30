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
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigEstate;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelOption;
use WP_UnitTestCase;

class TestClassFormModelBuilderDBEstateListSettings
	extends WP_UnitTestCase
{
	/** @var InputModelDBFactory */
	private $_pInputModelFactoryDBEntry;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pInputModelFactoryDBEntry = new InputModelDBFactory(new InputModelDBFactoryConfigEstate);
	}


	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings::createInputModelRandomSort
	 */
	public function testCreateInputModelRandomSort()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBEstateListSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue'])
			->getMock();

		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->willReturn('1');

		$pInputModelDB = $pInstance->createInputModelRandomSort();
		$this->assertInstanceOf(InputModelDB::class,$pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'checkbox');
	}


	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings::createInputModelSortByChosen
	 */
	public function testCreateInputModelSortByChosen()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBEstateListSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue', 'getOnlyDefaultSortByFields', 'readFieldnames'])
			->getMock();

		$pInstance->method('readFieldnames')
			->with('estate')
			->willReturn([
				'wohnflaeche' => 'wohnflaeche',
				'grundstuecksflaeche' => 'grundstuecksflaeche',
				'gesamtflaeche' => 'gesamtflaeche',
				'kaufpreis' => 'Kaufpreis',
				'kaltmiete' => 'Kaltmiete']);

		$pInstance->method('getOnlyDefaultSortByFields')
			->with('estate')
			->willReturn([
				'kaufpreis' => 'Kaufpreis',
				'kaltmiete' => 'Kaltmiete']);

		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->with('sortbyuservalues')->willReturn(null);

		$pInputModelDB = $pInstance->createInputModelSortByChosen();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals('chosen', $pInputModelDB->getHtmlType());
	}


	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings::createInputModelSortByChosenStandard
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings::getDataOfSortByInput
	 */
	public function testCreateInputModelSortByChosenStandard()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBEstateListSettings::class)
		                  ->disableOriginalConstructor()
		                  ->setMethods(['getInputModelDBFactory', 'getValue', 'getOnlyDefaultSortByFields', 'readFieldnames'])
		                  ->getMock();

		$pInstance->method('readFieldnames')
		          ->with('estate')
		          ->willReturn([
			          'wohnflaeche' => 'wohnflaeche',
			          'grundstuecksflaeche' => 'grundstuecksflaeche',
			          'gesamtflaeche' => 'gesamtflaeche',
			          'kaufpreis' => 'Kaufpreis',
			          'kaltmiete' => 'Kaltmiete']);

		$pInstance->method('getOnlyDefaultSortByFields')
		          ->with('estate')
		          ->willReturn([
			          'kaufpreis' => 'Kaufpreis',
			          'kaltmiete' => 'Kaltmiete']);
		$data = [
			"group" => [
				"Popular" => [
					"kaufpreis" => "Kaufpreis",
					"kaltmiete" => "Kaltmiete"
				],
				"All"     => [
					"gesamtflaeche"       => "gesamtflaeche",
					"grundstuecksflaeche" => "grundstuecksflaeche",
					"wohnflaeche"         => "wohnflaeche",
				]
			]
		];
		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->with('sortby')->willReturn(null);

		$pInputModelDB = $pInstance->createInputModelSortByChosenStandard();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals('chosen', $pInputModelDB->getHtmlType());
		$this->assertEquals($data, $pInstance->getDataOfSortByInput());
	}


	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings::createInputModelSortByChosen
	 */
	public function testCreateInputModelSortByChosenGroup()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBEstateListSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue', 'getOnlyDefaultSortByFields', 'readFieldnames'])
			->getMock();

		$pInstance->method('readFieldnames')
			->with('estate')
			->willReturn([
				'wohnflaeche' => 'wohnflaeche',
				'grundstuecksflaeche' => 'grundstuecksflaeche',
				'gesamtflaeche' => 'gesamtflaeche',
				'kaufpreis' => 'Kaufpreis',
				'kaltmiete' => 'Kaltmiete']);


		$pInstance->method('getOnlyDefaultSortByFields')
			->with('estate')
			->willReturn([
				'kaufpreis' => 'Kaufpreis',
				'kaltmiete' => 'Kaltmiete']);

		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->with('sortbyuservalues')->willReturn(null);

		$pInputModelDB = $pInstance->createInputModelSortByChosen();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$expectValue = [
			'group' => [
				'Popular' => [
					'kaufpreis' => 'Kaufpreis',
					'kaltmiete' => 'Kaltmiete'
				],
				'All' => [
					'wohnflaeche' => 'wohnflaeche',
					'grundstuecksflaeche' => 'grundstuecksflaeche',
					'gesamtflaeche' => 'gesamtflaeche',
				]
			]
		];
		$this->assertEquals($expectValue, $pInputModelDB->getValuesAvailable());
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings::createInputModelSortByChosen
	 */
	public function testCreateInputModelSortByChosenGroupNotPopular()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBEstateListSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue', 'getOnlyDefaultSortByFields', 'readFieldnames'])
			->getMock();

		$pInstance->method('readFieldnames')
			->with('estate')
			->willReturn([
				'data1' => 'Data 1',
				'data2' => 'Data 2'
			]);

		$pInstance->method('getOnlyDefaultSortByFields')
			->with('estate')
			->willReturn([]);

		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->with('sortbyuservalues')->willReturn(null);

		$pInputModelDB = $pInstance->createInputModelSortByChosen();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$expectValue = [
			'group' => [
				'All' => [
					'data1' => 'Data 1',
					'data2' => 'Data 2'
				]
			]
		];
		$this->assertEquals($expectValue, $pInputModelDB->getValuesAvailable());
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings::createInputModelSortByDefault
	 */
	public function testCreateInputModelSortByDefaultWithValue()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBEstateListSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue', 'getOnlyDefaultSortByFields', 'readFieldnames'])
			->getMock();

		$pInstance->method('readFieldnames')
			->with('estate')
			->willReturn([
				'wohnflaeche' => 'wohnflaeche',
				'grundstuecksflaeche' => 'grundstuecksflaeche',
				'gesamtflaeche' => 'gesamtflaeche',
				'kaufpreis' => 'Kaufpreis',
				'kaltmiete' => 'Kaltmiete']);


		$pInstance->method('getOnlyDefaultSortByFields')
			->with('estate')
			->willReturn([
				'kaufpreis' => 'Kaufpreis',
				'kaltmiete' => 'Kaltmiete']);
		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);

		$pInstance->expects($this->exactly(3))
			->method('getValue')
			->withConsecutive(['sortByUserDefinedDefault'], ['sortbyuservalues'], ['sortByUserDefinedDirection'])
			->will($this->onConsecutiveCalls('kaufpreis#ASC', ['kaufpreis'], '1'));

		$pInputModelDB = $pInstance->createInputModelSortByDefault();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings::createInputModelSortByDefault
	 */
	public function testCreateInputModelSortByDefaultWithoutValue()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBEstateListSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue', 'getOnlyDefaultSortByFields', 'readFieldnames'])
			->getMock();

		$pInstance->method('readFieldnames')
				  ->with('estate')
				  ->willReturn([
					  'wohnflaeche' => 'wohnflaeche',
					  'grundstuecksflaeche' => 'grundstuecksflaeche',
					  'gesamtflaeche' => 'gesamtflaeche',
					  'kaufpreis' => 'Kaufpreis',
					  'kaltmiete' => 'Kaltmiete']);

		$pInstance->method('getOnlyDefaultSortByFields')
			->with('estate')
			->willReturn([
				'kaufpreis' => 'Kaufpreis',
				'kaltmiete' => 'Kaltmiete']);
		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->willReturn([]);

		$pInputModelDB = $pInstance->createInputModelSortByDefault();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings::createInputModelSortBySpec
	 */
	public function testCreateInputModelSortBySpec()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBEstateListSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue', 'getOnlyDefaultSortByFields'])
			->getMock();

		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->willReturn('0');

		$pInputModelDB = $pInstance->createInputModelSortBySpec();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getValue(), '0');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings::getListViewLabels
	 */
	public function testGetListViewLabels()
	{
		$expected = [
			'default' => 'Default',
			'reference' => 'Reference Estates',
			'favorites' => 'Favorites List'];

		$this->assertEquals(FormModelBuilderDBEstateListSettings::getListViewLabels(), $expected);
	}


	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings::createInputModelShowReferenceEstate
	 */
	public function testCreateInputModelShowReferenceEstate()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBEstateListSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue', 'getOnlyDefaultSortByFields'])
			->getMock();

		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->willReturn('0');

		$pInputModelDB = $pInstance->createInputModelShowReferenceEstate();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getValue(), '0');
		$this->assertEquals('checkbox', $pInputModelDB->getHtmlType());
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings::createInputModelListType
	 */
	public function testCreateInputModelListType()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBEstateListSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue', 'getOnlyDefaultSortByFields'])
			->getMock();

		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->willReturn('0');

		$pInputModelDB = $pInstance->createInputModelListType();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getValue(), '0');
		$this->assertEquals('select', $pInputModelDB->getHtmlType());
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings::createInputModelShowStatus
	 */
	public function testCreateInputModelShowStatus()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBEstateListSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue', 'getOnlyDefaultSortByFields'])
			->getMock();

		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->willReturn('0');

		$pInputModelDB = $pInstance->createInputModelShowStatus();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getValue(), '0');
		$this->assertEquals('checkbox', $pInputModelDB->getHtmlType());
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings::createInputModelPictureTypes
	 */
	public function testCreateInputModelPictureTypes()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBEstateListSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue', 'getOnlyDefaultSortByFields'])
			->getMock();

		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->willReturn(null);

		$pInputModelDB = $pInstance->createInputModelPictureTypes();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEmpty($pInputModelDB->getValue());
		$this->assertEquals('checkbox', $pInputModelDB->getHtmlType());
	}


	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings::createInputModelExpose
	 */
	public function testCreateInputModelExpose()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBEstateListSettings::class)
		                  ->disableOriginalConstructor()
		                  ->setMethods(['getInputModelDBFactory', 'getValue', 'getOnlyDefaultSortByFields', 'readExposes'])
		                  ->getMock();

		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->willReturn('0');
		$pInstance->method('readExposes')->willReturn(['testExp']);

		$pInputModelDB = $pInstance->createInputModelExpose();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getValue(), '0');
		$this->assertEquals('select', $pInputModelDB->getHtmlType());
	}


	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings::getInputModelIsFilterable
	 */
	public function testGetInputModelIsFilterable()
	{
		$pInstance = new FormModelBuilderDBEstateListSettings();

		$pInputModelDB = $pInstance->getInputModelIsFilterable();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals(InputModelBase::HTML_TYPE_CHECKBOX, $pInputModelDB->getHtmlType());
		$this->assertEquals([$pInstance, 'callbackValueInputModelIsFilterable'], $pInputModelDB->getValueCallback());
	}


	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings::createInputModelFilter
	 */
	public function testCreateInputModelFilter()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBEstateListSettings::class)
		                  ->disableOriginalConstructor()
		                  ->setMethods(['getInputModelDBFactory', 'getValue', 'getOnlyDefaultSortByFields', "readFilters"])
		                  ->getMock();

		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('readFilters')->with(onOfficeSDK::MODULE_ESTATE)->willReturn(['a']);
		$pInstance->method('getValue')->willReturn('0');

		$pInputModelDB = $pInstance->createInputModelFilter();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getValue(), '0');
		$this->assertEquals($pInputModelDB->getValuesAvailable(), [""]);
		$this->assertEquals('select', $pInputModelDB->getHtmlType());
	}
}
