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

use DI\Container;
use DI\ContainerBuilder;
use onOffice\WPlugin\DataView\DataSimilarEstatesSettingsHandler;
use onOffice\WPlugin\DataView\DataSimilarView;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderSimilarEstateSettings;
use onOffice\WPlugin\Model\InputModel\InputModelOptionFactorySimilarView;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\WP\WPOptionWrapperTest;
use WP_UnitTestCase;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\Field;

class TestClassFormModelBuilderSimilarEstateSettings
	extends WP_UnitTestCase
{

	/** */
	const VALUES_BY_ROW = [
		'fields' => [
			'Objektnr_extern',
			'wohnflaeche',
			'kaufpreis',
		],
		'similar_estates_template' => '/test/similar/template.php',
		'same_kind' => true,
		'same_maketing_method' => true,
		'show_archived' => true,
		'show_reference' => true,
		'radius' => 35,
		'amount' => 13,
		'enablesimilarestates' => true,
	];

	/** @var bool */
	private $_dataSimilarViewActive = true;

	/** @var InputModelOptionFactorySimilarView */
	private $_pInputModelOptionFactorySimilarViewDBEntry;

	/** @var DataSimilarView */
	private $_pDataSimilarView = null;

	/** @var Container */
	private $_pContainer;

	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort = null;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pInputModelOptionFactorySimilarViewDBEntry = new InputModelOptionFactorySimilarView('onoffice');
		
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();

		$this->_pFieldsCollectionBuilderShort = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
				->setMethods(['addFieldsAddressEstate', 'addFieldsEstateDecoratorReadAddressBackend',  'addFieldsEstateGeoPosisionBackend'])
				->setConstructorArgs([$this->_pContainer])
				->getMock();
		$this->_pContainer->set(FieldsCollectionBuilderShort::class, $this->_pFieldsCollectionBuilderShort);
		$this->_pFieldsCollectionBuilderShort->method('addFieldsAddressEstate')
			->with($this->anything())
			->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
				$pField1 = new Field('objekttitel', onOfficeSDK::MODULE_ESTATE);
				$pFieldsCollection->addField($pField1);

				return $this->_pFieldsCollectionBuilderShort;
			}));
		$this->_pFieldsCollectionBuilderShort->method('addFieldsEstateDecoratorReadAddressBackend')
			->with($this->anything())
			->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
				$pField1 = new Field('objektart', onOfficeSDK::MODULE_ESTATE);
				$pFieldsCollection->addField($pField1);

				return $this->_pFieldsCollectionBuilderShort;
			}));
			
		$this->_pFieldsCollectionBuilderShort->method('addFieldsEstateGeoPosisionBackend')
				->with($this->anything())
				->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
			$pField1 = new Field('objekttyp', onOfficeSDK::MODULE_ESTATE);
			$pFieldsCollection->addField($pField1);

			return $this->_pFieldsCollectionBuilderShort;
		}));
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderSimilarEstateSettings::GetCheckboxEnableSimilarEstates
	 */
	public function testGetCheckboxEnableSimilarEstates()
	{
		$row = self::VALUES_BY_ROW;

		$pWPOptionsWrapper = new WPOptionWrapperTest();
		$pDataSimilarEstatesSettingsHandler = new DataSimilarEstatesSettingsHandler($pWPOptionsWrapper);
		$this->_pDataSimilarView = $pDataSimilarEstatesSettingsHandler->createDataSimilarEstatesSettingsByValues($row);


		$pInstance = $this->getMockBuilder(FormModelBuilderSimilarEstateSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getValue'])
			->getMock();
		$pInstance->generate('test');
		$pInstance->setInputModelSimilarViewFactory($this->_pInputModelOptionFactorySimilarViewDBEntry);

		$pInputModelDB = $pInstance->getCheckboxEnableSimilarEstates();
		$this->assertEquals($pInputModelDB->getHtmlType(), 'checkbox');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderSimilarEstateSettings::createInputModelSimilarEstateKind
	 */
	public function testCreateInputModelSimilarEstateKind()
	{
		$row = self::VALUES_BY_ROW;

		$pWPOptionsWrapper = new WPOptionWrapperTest();
		$pDataSimilarEstatesSettingsHandler = new DataSimilarEstatesSettingsHandler($pWPOptionsWrapper);
		$this->_pDataSimilarView = $pDataSimilarEstatesSettingsHandler->createDataSimilarEstatesSettingsByValues($row);


		$pInstance = $this->getMockBuilder(FormModelBuilderSimilarEstateSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getValue'])
			->getMock();
		$pInstance->generate('test');
		$pInstance->setInputModelSimilarViewFactory($this->_pInputModelOptionFactorySimilarViewDBEntry);
		$pInstance->method('getValue')->willReturn('1');

		$pInputModelDB = $pInstance->createInputModelSimilarEstateKind();
		$this->assertEquals($pInputModelDB->getValue(), '1');
		$this->assertEquals($pInputModelDB->getHtmlType(), 'checkbox');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderSimilarEstateSettings::createInputModelSimilarEstateMarketingMethod
	 */
	public function testCreateInputModelSimilarEstateMarketingMethod()
	{
		$row = self::VALUES_BY_ROW;

		$pWPOptionsWrapper = new WPOptionWrapperTest();
		$pDataSimilarEstatesSettingsHandler = new DataSimilarEstatesSettingsHandler($pWPOptionsWrapper);
		$this->_pDataSimilarView = $pDataSimilarEstatesSettingsHandler->createDataSimilarEstatesSettingsByValues($row);


		$pInstance = $this->getMockBuilder(FormModelBuilderSimilarEstateSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getValue'])
			->getMock();
		$pInstance->generate('test');
		$pInstance->setInputModelSimilarViewFactory($this->_pInputModelOptionFactorySimilarViewDBEntry);
		$pInstance->method('getValue')->willReturn('1');

		$pInputModelDB = $pInstance->createInputModelSimilarEstateMarketingMethod();
		$this->assertEquals($pInputModelDB->getValue(), '1');
		$this->assertEquals($pInputModelDB->getHtmlType(), 'checkbox');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderSimilarEstateSettings::createInputModelFieldsConfigByCategory
	 */
	public function testCreateInputModelFieldsConfigByCategory()
	{
		$formModel = new FormModelBuilderSimilarEstateSettings();
		$result = $formModel->createInputModelFieldsConfigByCategory('1', ['name'], 'label');
		$this->assertInstanceOf(InputModelOption::class, $result);
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderSimilarEstateSettings::createInputModelFieldsConfigByCategory
	 */
	public function testCreateInputModelFieldsConfigByCategoryEmptyValue()
	{
		$formModel = new FormModelBuilderSimilarEstateSettings();
		$result = $formModel->createInputModelFieldsConfigByCategory('1', ['name'], 'label');
		$formModel->setValues(['']);
		$this->assertEmpty($result->getValue());
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderSimilarEstateSettings::createInputModelTemplate
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderSimilarEstateSettings::setInputModelSimilarViewFactory
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderSimilarEstateSettings::getTemplateValueByField
	 */
	public function testCreateInputModelTemplate()
	{
		$row = self::VALUES_BY_ROW;

		$pWPOptionsWrapper = new WPOptionWrapperTest();
		$pDataSimilarEstatesSettingsHandler = new DataSimilarEstatesSettingsHandler($pWPOptionsWrapper);
		$this->_pDataSimilarView = $pDataSimilarEstatesSettingsHandler->createDataSimilarEstatesSettingsByValues($row);

		$pInstance = $this->getMockBuilder(FormModelBuilderSimilarEstateSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getValue'])
			->getMock();
		$pInstance->generate('test');
		$pInstance->setInputModelSimilarViewFactory($this->_pInputModelOptionFactorySimilarViewDBEntry);
		$pInstance->method('getValue')->willReturn('1');

		$pInputModelDB = $pInstance->createInputModelTemplate(InputModelOptionFactorySimilarView::INPUT_FIELD_SIMILAR_ESTATES_TEMPLATE);
		$this->assertEquals($pInputModelDB->getHtmlType(), InputModelOption::HTML_TYPE_TEMPLATE_LIST);
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderSimilarEstateSettings::createInputModelSameEstatePostalCode
	 */
	public function testCreateInputModelSameEstatePostalCode()
	{
		$row = self::VALUES_BY_ROW;

		$pWPOptionsWrapper = new WPOptionWrapperTest();
		$pDataSimilarEstatesSettingsHandler = new DataSimilarEstatesSettingsHandler($pWPOptionsWrapper);
		$this->_pDataSimilarView = $pDataSimilarEstatesSettingsHandler->createDataSimilarEstatesSettingsByValues($row);


		$pInstance = $this->getMockBuilder(FormModelBuilderSimilarEstateSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getValue'])
			->getMock();
		$pInstance->generate('test');
		$pInstance->setInputModelSimilarViewFactory($this->_pInputModelOptionFactorySimilarViewDBEntry);

		$pInputModelDB = $pInstance->createInputModelSameEstatePostalCode();
		$this->assertEquals($pInputModelDB->getHtmlType(), 'checkbox');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderSimilarEstateSettings::createInputModelSameEstateRadius
	 */
	public function testCreateInputModelSameEstateRadius()
	{
		$row = self::VALUES_BY_ROW;

		$pWPOptionsWrapper = new WPOptionWrapperTest();
		$pDataSimilarEstatesSettingsHandler = new DataSimilarEstatesSettingsHandler($pWPOptionsWrapper);
		$this->_pDataSimilarView = $pDataSimilarEstatesSettingsHandler->createDataSimilarEstatesSettingsByValues($row);


		$pInstance = $this->getMockBuilder(FormModelBuilderSimilarEstateSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getValue'])
			->getMock();
		$pInstance->generate('test');
		$pInstance->setInputModelSimilarViewFactory($this->_pInputModelOptionFactorySimilarViewDBEntry);

		$pInputModelDB = $pInstance->createInputModelSameEstateRadius();
		$this->assertEquals($pInputModelDB->getHtmlType(), 'text');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderSimilarEstateSettings::CreateInputModelSameEstateAmount
	 */
	public function testCreateInputModelSameEstateAmount()
	{
		$row = self::VALUES_BY_ROW;

		$pWPOptionsWrapper = new WPOptionWrapperTest();
		$pDataSimilarEstatesSettingsHandler = new DataSimilarEstatesSettingsHandler($pWPOptionsWrapper);
		$this->_pDataSimilarView = $pDataSimilarEstatesSettingsHandler->createDataSimilarEstatesSettingsByValues($row);


		$pInstance = $this->getMockBuilder(FormModelBuilderSimilarEstateSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getValue'])
			->getMock();
		$pInstance->generate('test');
		$pInstance->setInputModelSimilarViewFactory($this->_pInputModelOptionFactorySimilarViewDBEntry);

		$pInputModelDB = $pInstance->createInputModelSameEstateAmount();
		$this->assertEquals($pInputModelDB->getHtmlType(), 'text');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderSimilarEstateSettings::createButtonModelFieldsConfigByCategory
	 */
	public function testCreateButtonModelFieldsConfigByCategory()
	{
		$pInstanceFields = $this->getMockBuilder( DataDetailView::class )
		                        ->disableOriginalConstructor()
		                        ->setMethods( [ 'getFields' ] )
		                        ->getMock();
		$pInstance = $this->getMockBuilder( FormModelBuilderSimilarEstateSettings::class )
		                        ->disableOriginalConstructor()
		                        ->setMethods( [ 'getValue' ] )
		                        ->getMock();
		$pInstance->method( 'getValue' )->willReturn( $pInstanceFields );
		$pInstance->method( 'getValue' )->willReturn( '' );

		$pInputModelDB = $pInstance->createButtonModelFieldsConfigByCategory('category','name','label');

		$this->assertInstanceOf(InputModelOption::class, $pInputModelDB);
		$this->assertEquals( 'buttonHandleField', $pInputModelDB->getHtmlType() );
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderSimilarEstateSettings::createInputModelShowPriceOnRequest
	 */
	public function testCreateInputModelShowPriceOnRequest()
	{
		$row = self::VALUES_BY_ROW;

		$pWPOptionsWrapper = new WPOptionWrapperTest();
		$pDataSimilarEstatesSettingsHandler = new DataSimilarEstatesSettingsHandler($pWPOptionsWrapper);
		$this->_pDataSimilarView = $pDataSimilarEstatesSettingsHandler->createDataSimilarEstatesSettingsByValues($row);

		$pInstance = $this->getMockBuilder(FormModelBuilderSimilarEstateSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getValue'])
			->getMock();
		$pInstance->generate('test');
		$pInstance->setInputModelSimilarViewFactory($this->_pInputModelOptionFactorySimilarViewDBEntry);
		$pInstance->method('getValue')->willReturn('1');

		$pInputModelDB = $pInstance->createInputModelShowPriceOnRequest();
		$this->assertEquals($pInputModelDB->getHtmlType(), InputModelOption::HTML_TYPE_CHECKBOX);
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderSimilarEstateSettings::createInputModelPictureTypes
	 */
	public function testCreateInputModelPictureTypes()
	{
		$row = self::VALUES_BY_ROW;

		$pWPOptionsWrapper = new WPOptionWrapperTest();
		$pDataSimilarEstatesSettingsHandler = new DataSimilarEstatesSettingsHandler($pWPOptionsWrapper);
		$this->_pDataSimilarView = $pDataSimilarEstatesSettingsHandler->createDataSimilarEstatesSettingsByValues($row);


		$pInstance = $this->getMockBuilder(FormModelBuilderSimilarEstateSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getValue'])
			->getMock();
		$pInstance->generate('test');

		$pInputModelDB = $pInstance->createInputModelPictureTypes();
		$this->assertEquals($pInputModelDB->getHtmlType(), 'checkbox');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderSimilarEstateSettings::createSearchFieldForFieldLists
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderSimilarEstateSettings::getFieldsCollection
	 */
	public function testCreateSearchFieldForFieldLists()
	{
		$pFormModelBuilderSimilarEstateSettings = new FormModelBuilderSimilarEstateSettings($this->_pContainer);
		$pFormModelBuilderSimilarEstateSettings->generate('test');
		$pInputModelOption = $pFormModelBuilderSimilarEstateSettings->createSearchFieldForFieldLists('estate', 'searchFieldForFieldLists');

		$this->assertInstanceOf(InputModelOption::class, $pInputModelOption);
		$this->assertNotEmpty($pInputModelOption->getValuesAvailable());
		$this->assertEquals($pInputModelOption->getHtmlType(), 'searchFieldForFieldLists');
	}
}