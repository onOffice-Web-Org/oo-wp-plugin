<?php

/**
 *
 *    Copyright (C) 2020 onOffice GmbH
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
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\Field\FieldModuleCollection;
use onOffice\WPlugin\Field\FieldnamesEnvironment;
use onOffice\WPlugin\Field\FieldnamesEnvironmentTest;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Model\ExceptionInputModelMissingField;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderEstateDetailSettings;
use onOffice\WPlugin\Model\InputModel\InputModelOptionFactoryDetailView;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\WP\WPOptionWrapperTest;
use WP_UnitTestCase;
use onOffice\WPlugin\DataView\DataDetailView;
use wpdb;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use DI\Container;
use DI\ContainerBuilder;
use onOffice\WPlugin\Types\Field;

class TestClassFormModelBuilderEstateDetailSettings
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
		'access-control' => true,
		'enablesimilarestates' => true,
		'show_status' => true
	];

	/** @var InputModelOptionFactoryDetailView */
	private $_pInputModelDetailViewFactory;

	/** @var DataSimilarView */
	private $_pDataDetailView = null;

	/** @var FieldnamesEnvironmentTest */
	private $_pFieldnamesEnvironment = null;

	/** @var Fieldnames */
	private $_pFieldnames = null;

	/** @var Container */
	private $_pContainer;

	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort = null;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pFieldnamesEnvironment = new FieldnamesEnvironmentTest();
		$fieldParameters               = [
				'labels'      => true,
				'showContent' => true,
				'showTable'   => true,
				'language'    => 'ENG',
				'modules'     => ['address', 'estate'],
				'realDataTypes' => true,
		];
		$pSDKWrapperMocker             = $this->_pFieldnamesEnvironment->getSDKWrapper();
		$responseGetFields             = json_decode
		(file_get_contents(__DIR__ . '/resources/ApiResponseGetFields.json'), true);
		/* @var $pSDKWrapperMocker SDKWrapperMocker */
		$pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'fields', '',
				$fieldParameters, null, $responseGetFields);
		$this->_pFieldnames = new Fieldnames(new FieldsCollection(), false, $this->_pFieldnamesEnvironment);
		$this->_pInputModelDetailViewFactory = new InputModelOptionFactoryDetailView('onoffice');

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

				$pField1 = new Field('objektnr_extern', onOfficeSDK::MODULE_ESTATE);
				$pFieldsCollection->addField($pField1);

				return $this->_pFieldsCollectionBuilderShort;
			}));
		$this->_pFieldsCollectionBuilderShort->method('addFieldsEstateDecoratorReadAddressBackend')
			->with($this->anything())
			->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {

				$pField1 = new Field('objektart', onOfficeSDK::MODULE_ADDRESS);
				$pFieldsCollection->addField($pField1);

				return $this->_pFieldsCollectionBuilderShort;
			}));
			
		$this->_pFieldsCollectionBuilderShort->method('addFieldsEstateGeoPosisionBackend')
				->with($this->anything())
				->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
			$pField1 = new Field('city', onOfficeSDK::MODULE_ESTATE);
			$pFieldsCollection->addField($pField1);

			return $this->_pFieldsCollectionBuilderShort;
		}));
	}
	
	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderEstateDetailSettings::__construct
	 */
	
	public function testConstruct()
	{
		$pInstance = new FormModelBuilderEstateDetailSettings($this->_pContainer, $this->_pFieldnames);
		$this->assertInstanceOf(FormModelBuilderEstateDetailSettings::class, $pInstance);
	}
	
	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderEstateDetailSettings::CreateInputModelShortCodeForm
	 */
	public function testCreateInputModelShortCodeForm()
	{
		$row = self::VALUES_BY_ROW;

		$pWPOptionsWrapper = new WPOptionWrapperTest();
		$pDataDetailViewHandler = new DataDetailViewHandler($pWPOptionsWrapper);
		$this->_pDataDetailView = $pDataDetailViewHandler->createDetailViewByValues($row);

		$pInstance = $this->getMockBuilder(FormModelBuilderEstateDetailSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getValue', 'readNameShortCodeForm'])
			->getMock();
		$pInstance->expects($this->exactly(1))
			->method('readNameShortCodeForm')
			->willReturnOnConsecutiveCalls(['' => '[oo_form form="Default Form"]'],['' => '[oo_form form="Default Form"]']);
		$pInstance->generate('test');

		$pInputModelDB = $pInstance->createInputModelShortCodeForm();
		$this->assertEquals($pInputModelDB->getHtmlType(), 'select');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderEstateDetailSettings::createInputModelPictureTypes
	 */
	public function testCreateInputModelPictureTypes()
	{
		$row = self::VALUES_BY_ROW;

		$pWPOptionsWrapper = new WPOptionWrapperTest();
		$pDataSimilarEstatesSettingsHandler = new DataDetailViewHandler($pWPOptionsWrapper);
		$this->_pDataDetailView = $pDataSimilarEstatesSettingsHandler->createDetailViewByValues($row);


		$pInstance = $this->getMockBuilder(FormModelBuilderEstateDetailSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getValue'])
			->getMock();
		$pInstance->generate('test');

		$pInputModelDB = $pInstance->createInputModelPictureTypes();
		$this->assertEquals($pInputModelDB->getHtmlType(), 'checkbox');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderEstateDetailSettings::createInputAccessControl
	 */
	public function testCreateInputAccessControl()
	{
		$row = self::VALUES_BY_ROW;

		$pWPOptionsWrapper = new WPOptionWrapperTest();
		$pDataSimilarEstatesSettingsHandler = new DataDetailViewHandler($pWPOptionsWrapper);
		$this->_pDataDetailView = $pDataSimilarEstatesSettingsHandler->createDetailViewByValues($row);


		$pInstance = $this->getMockBuilder(FormModelBuilderEstateDetailSettings::class)
		                  ->disableOriginalConstructor()
		                  ->setMethods(['getValue'])
		                  ->getMock();
		$pInstance->generate('test');

		$pInputModelDB = $pInstance->createInputAccessControl();
		$this->assertEquals($pInputModelDB->getHtmlType(), 'checkbox');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderEstateDetailSettings::createInputRestrictAccessControl
	 */
	public function testCreateInputRestrictAccessControl()
	{
		$row = self::VALUES_BY_ROW;

		$pWPOptionsWrapper                  = new WPOptionWrapperTest();
		$pDataSimilarEstatesSettingsHandler = new DataDetailViewHandler( $pWPOptionsWrapper );
		$this->_pDataDetailView             = $pDataSimilarEstatesSettingsHandler->createDetailViewByValues( $row );


		$pInstance = $this->getMockBuilder( FormModelBuilderEstateDetailSettings::class )
		                  ->disableOriginalConstructor()
		                  ->setMethods( [ 'getValue' ] )
		                  ->getMock();
		$pInstance->generate( 'test' );

		$pInputModelDB = $pInstance->createInputRestrictAccessControl();
		$this->assertEquals( $pInputModelDB->getHtmlType(), 'checkbox' );
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderEstateDetailSettings::createInputModelMovieLinks
	 */
	public function testCreateInputModelMovieLinks()
	{
		$row = self::VALUES_BY_ROW;

		$pWPOptionsWrapper = new WPOptionWrapperTest();
		$pDataSimilarEstatesSettingsHandler = new DataDetailViewHandler($pWPOptionsWrapper);
		$this->_pDataDetailView = $pDataSimilarEstatesSettingsHandler->createDetailViewByValues($row);


		$pInstance = $this->getMockBuilder(FormModelBuilderEstateDetailSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getValue'])
			->getMock();
		$pInstance->generate('test');

		$pInputModelDB = $pInstance->createInputModelMovieLinks();
		$this->assertEquals($pInputModelDB->getHtmlType(), 'select');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderEstateDetailSettings::createInputModelOguloLinks
	 */
	public function testCreateInputModelOguloLinks()
	{
		$pFormModelBuilderDBEstateDetailSettings = new FormModelBuilderEstateDetailSettings($this->_pContainer, $this->_pFieldnames);
		$pFormModelBuilderDBEstateDetailSettings->generate('test');
		$pInputModelDB = $pFormModelBuilderDBEstateDetailSettings->createInputModelOguloLinks();
		$this->assertEquals($pInputModelDB->getHtmlType(), 'select');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderEstateDetailSettings::createInputModelObjectLinks
	 */
	public function testCreateInputModelObjectLinks()
	{
		$pFormModelBuilderDBEstateDetailSettings = new FormModelBuilderEstateDetailSettings($this->_pContainer, $this->_pFieldnames);
		$pFormModelBuilderDBEstateDetailSettings->generate('test');
		$pInputModelDB = $pFormModelBuilderDBEstateDetailSettings->createInputModelObjectLinks();
		$this->assertEquals($pInputModelDB->getHtmlType(), 'select');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderEstateDetailSettings::createInputModelLinks
	 */
	public function testCreateInputModelLinks()
	{
		$pFormModelBuilderDBEstateDetailSettings = new FormModelBuilderEstateDetailSettings($this->_pContainer, $this->_pFieldnames);
		$pFormModelBuilderDBEstateDetailSettings->generate('test');
		$pInputModelDB = $pFormModelBuilderDBEstateDetailSettings->createInputModelLinks();
		$this->assertEquals($pInputModelDB->getHtmlType(), 'select');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderEstateDetailSettings::createInputModelShowStatus
	 * @throws ExceptionInputModelMissingField
	 */
	public function testCreateInputModelShowStatus()
	{
		$pFormModelBuilderDBEstateDetailSettings = new FormModelBuilderEstateDetailSettings($this->_pContainer, $this->_pFieldnames);
		$pFormModelBuilderDBEstateDetailSettings->generate('test');
		$pInputModelDB = $pFormModelBuilderDBEstateDetailSettings->createInputModelShowStatus();
		$this->assertEquals($pInputModelDB->getHtmlType(), 'checkbox');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderEstateDetailSettings::CreateInputModelTemplate
	 */
	public function testCreateInputModelTemplate()
	{
		$row = self::VALUES_BY_ROW;

		$pWPOptionsWrapper = new WPOptionWrapperTest();
		$pDataDetailViewHandler = new DataDetailViewHandler($pWPOptionsWrapper);
		$this->_pDataDetailView = $pDataDetailViewHandler->createDetailViewByValues($row);

		$pInstance = $this->getMockBuilder(FormModelBuilderEstateDetailSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getValue', 'readTemplatePaths'])
			->getMock();
		$pInstance->expects($this->exactly(1))
			->method('readTemplatePaths');
		$pInstance->generate('test');

		$pInputModelDB = $pInstance->createInputModelTemplate();
		$this->assertEquals($pInputModelDB->getHtmlType(), 'templateList');
	}
	
	/**
	* @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderEstateDetailSettings::createInputModelExpose
	*/
	
	public function testCreateInputModelExpose()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderEstateDetailSettings::class)
			->disableOriginalConstructor()
			->setMethods(['readExposes'])
			->getMock();
		$pInstance->generate('test');
		$pInstance->method('readExposes')->willReturn([]);
		$pInputModelDB = $pInstance->createInputModelExpose();
		$this->assertEquals($pInputModelDB->getHtmlType(), 'select');
	}
	
	/**
	* @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderEstateDetailSettings::createButtonModelFieldsConfigByCategory
	*/

	public function testCreateButtonModelFieldsConfigByCategory()
	{
		$pInstanceAddressFields = $this->getMockBuilder( DataDetailView::class )
		                               ->disableOriginalConstructor()
		                               ->setMethods( [ 'getAddressFields' ] )
		                               ->getMock();
		$pInstanceFields = $this->getMockBuilder( DataDetailView::class )
		                               ->disableOriginalConstructor()
		                               ->setMethods( [ 'getFields' ] )
		                               ->getMock();
		$pInstance = $this->getMockBuilder( FormModelBuilderEstateDetailSettings::class )
		                               ->disableOriginalConstructor()
		                               ->setMethods( [ 'getValue' ] )
		                               ->getMock();
		$pValues = array_merge( [ $pInstanceAddressFields, $pInstanceFields ], [] );
		$pInstance->method( 'getValue' )->willReturn( $pValues );
		$pInstance->method( 'getValue' )->willReturn( '' );

		$pInputModelDB = $pInstance->createButtonModelFieldsConfigByCategory( 'category', 'name', 'label' );

		$this->assertInstanceOf(InputModelOption::class, $pInputModelDB);
		$this->assertEquals( 'buttonHandleField', $pInputModelDB->getHtmlType() );
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderEstateDetailSettings::createInputModelShowPriceOnRequest
	 */
	public function testCreateInputModelShowPriceOnRequest()
	{
		$pFormModelBuilderDBEstateDetailSettings = new FormModelBuilderEstateDetailSettings($this->_pContainer, $this->_pFieldnames);
		$pFormModelBuilderDBEstateDetailSettings->generate('test');
		$pInputModelDB = $pFormModelBuilderDBEstateDetailSettings->createInputModelShowPriceOnRequest();
		$this->assertNotEmpty($pInputModelDB->getValuesAvailable());
		$this->assertEquals($pInputModelDB->getHtmlType(), 'checkbox');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderEstateDetailSettings::createSortableFieldList
	 */
	public function testCreateSortableFieldList()
	{
		$pFormModelBuilderEstateDetailSettings = new FormModelBuilderEstateDetailSettings($this->_pContainer, $this->_pFieldnames);
		$pFormModelBuilderEstateDetailSettings->generate('test');
		$pInputModelOption = $pFormModelBuilderEstateDetailSettings->createSortableFieldList('estate', 'complexSortableDetailList');

		$this->assertInstanceOf(InputModelOption::class, $pInputModelOption);
		$this->assertNotEmpty($pInputModelOption->getValuesAvailable());
		$this->assertEquals($pInputModelOption->getHtmlType(), 'complexSortableDetailList');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderEstateDetailSettings::createSearchFieldForFieldLists
	 */
	public function testCreateSearchFieldForFieldLists()
	{
		$pFormModelBuilderEstateDetailSettings = new FormModelBuilderEstateDetailSettings($this->_pContainer, $this->_pFieldnames);
		$pFormModelBuilderEstateDetailSettings->generate('test');
		$pInputModelOption = $pFormModelBuilderEstateDetailSettings->createSearchFieldForFieldLists(['estate', 'address'], 'searchFieldForFieldLists');

		$this->assertInstanceOf(InputModelOption::class, $pInputModelOption);
		$this->assertNotEmpty($pInputModelOption->getValuesAvailable());
		$this->assertEquals($pInputModelOption->getHtmlType(), 'searchFieldForFieldLists');
	}
}