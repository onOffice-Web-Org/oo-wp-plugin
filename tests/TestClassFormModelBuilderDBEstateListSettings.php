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

use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigEstate;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelLabel;
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
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings::createInputModelSortBySetting
	 */
	public function testCreateInputModelSortBySetting()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBEstateListSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue'])
			->getMock();

		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->willReturn('1');

		$pInputModelDB = $pInstance->createInputModelSortBySetting();
		$this->assertInstanceOf(InputModelDB::class,$pInputModelDB);
		$this->assertEquals($pInputModelDB->getValue(), '1');
		$this->assertEquals($pInputModelDB->getHtmlType(), InputModelOption::HTML_TYPE_CHECKBOX);
	}


	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings::createInputModelSortByChosen
	 */
	public function testCreateInputModelSortByChosen()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBEstateListSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue', 'getOnlyDefaultSortByFields'])
			->getMock();

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
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings::createInputModelSortByDefault
	 */
	public function testCreateInputModelSortByDefaultWithValue()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBEstateListSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue', 'getOnlyDefaultSortByFields'])
			->getMock();

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
			->setMethods(['getInputModelDBFactory', 'getValue', 'getOnlyDefaultSortByFields'])
			->getMock();

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

    public function testCreateSortableFieldList()
    {
        $pInstance = $this->getMockBuilder(FormModelBuilderDBEstateListSettings::class)
            ->disableOriginalConstructor()
            ->getMock();

        $instance = $pInstance->createSortableFieldList('address', 'checkbox', false);
        $this->assertEquals($instance->getReferencedInputModels(), null);
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

	public function testCreateInputModelEmbedCode()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBEstateListSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue'])
			->getMock();

		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->willReturn('1');
		$pInputModelFormEmbedCode = $pInstance->createInputModelEmbedCode();
		$this->assertInstanceOf(InputModelLabel::class, $pInputModelFormEmbedCode);
		$this->assertEquals($pInputModelFormEmbedCode->getHtmlType(), 'label');
	}

	public function testCreateInputModelButton()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBEstateListSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue'])
			->getMock();
		$pInputModelButton = $pInstance->createInputModelButton();
		$this->assertInstanceOf(InputModelLabel::class, $pInputModelButton);
		$this->assertEquals($pInputModelButton->getHtmlType(), 'button');
	}
}
