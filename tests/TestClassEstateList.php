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

use Closure;
use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\SDK\Exception\HttpFetchNoResultException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\AddressList;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\API\APIEmptyResultException;
use onOffice\WPlugin\ArrayContainerEscape;
use onOffice\WPlugin\Controller\DetailViewPostSaveController;
use onOffice\WPlugin\Controller\EstateListEnvironment;
use onOffice\WPlugin\Controller\EstateListEnvironmentDefault;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\UnknownViewException;
use onOffice\WPlugin\EstateFiles;
use onOffice\WPlugin\EstateList;
use onOffice\WPlugin\EstateUnits;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\OutputFields;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListView;
use onOffice\WPlugin\Filter\DefaultFilterBuilderPresetEstateIds;
use onOffice\WPlugin\Filter\GeoSearchBuilderEmpty;
use onOffice\WPlugin\Filter\GeoSearchBuilderFromInputVars;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Types\EstateStatusLabel;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use WP_Rewrite;
use WP_UnitTestCase;
use function json_decode;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassEstateList
	extends WP_UnitTestCase
{
	/** @var EstateList */
	private $_pEstateList = null;

	/** @var Container */
	private $_pContainer;

	/** @var EstateListEnvironment */
	private $_pEnvironment = null;

	/** @var SDKWrapperMocker */
	private $_pSDKWrapperMocker = null;

	/** @var array */
	private $_estatePicturesByEstateId = [
		15 => [
			2 => [
				'id' => 2,
				'url' => 'https://test.url/image/2.jpg',
				'title' => 'Awesome image',
				'text' => 'An image for test',
				'type' => 'Titelbild',
			],
			3 => [
				'id' => 3,
				'url' => 'https://test.url/image/3.png',
				'title' => 'Another awesome image',
				'text' => 'Another image for test',
				'type' => 'Foto',
			],
			4 => [
				'id' => 4,
				'url' => 'https://test.url/image/4.png',
				'title' => 'Another awesome image',
				'text' => 'Another image for test',
				'type' => 'Foto_gross',
			],
		],
	];


	/**
	 *
	 */

	public function testConstruct()
	{
		$pDataView = new DataListView(1, 'test');
		$pEstateList = new EstateList($pDataView);
		$this->assertInstanceOf(EstateListEnvironmentDefault::class, $pEstateList->getEnvironment());
	}


	/**
	 *
	 */

	public function testLoadEstates()
	{
		$this->_pEstateList->loadEstates();
		$pClosureGetEstateResult = Closure::bind(function() {
			return $this->_records;
		}, $this->_pEstateList, EstateList::class);
		$this->assertCount(5, $pClosureGetEstateResult());
	}


	/**
	 *
	 */

	public function testLoadRandomEstates()
	{
		$pDataViewRandom = $this->getDataViewRandom();
		$this->_pEstateList->loadEstates(1, $pDataViewRandom);
		$pClosureGetEstateResult = Closure::bind(function() {
			return $this->_records;
		}, $this->_pEstateList, EstateList::class);
		$this->assertCount(5, $pClosureGetEstateResult());
	}


	/**
	 *
	 */

	public function testEstateIterator()
	{
		$this->_pEstateList->loadEstates();

		foreach (range(0, 4) as $iter) {
			$pEstate = $this->_pEstateList->estateIterator();
			$this->assertInstanceOf(ArrayContainerEscape::class, $pEstate);
		}

		$this->assertFalse($this->_pEstateList->estateIterator());
		$this->_pEstateList->resetEstateIterator();
		$this->assertInstanceOf(ArrayContainerEscape::class, $this->_pEstateList->estateIterator());
	}


	/**
	 *
	 */

	public function testEstateIteratorBadState()
	{
		$this->assertFalse($this->_pEstateList->estateIterator());
	}


	/**
	 *
	 */

	public function testGetEstateOverallCount()
	{
		$this->_pEstateList->loadEstates();
		$this->assertEquals(9, $this->_pEstateList->getEstateOverallCount());
	}


	/**
	 *
	 */

	public function testGetFieldLabel()
	{
		$pFields = new FieldsCollection();
		$pFieldnamesMock = $this->getMockBuilder(Fieldnames::class)
			->setMethods(['getFieldLabel'])
			->setConstructorArgs([$pFields])
			->getMock();
		$pFieldnamesMock->method('getFieldLabel')->with(
				$this->equalTo('testfield'), $this->equalTo(onOfficeSDK::MODULE_ESTATE))
			->willReturn('Test Field');
		$this->_pEnvironment->method('getFieldnames')->willReturn($pFieldnamesMock);
		$this->assertEquals('Test Field', $this->_pEstateList->getFieldLabel('testfield'));
	}


	/**
	 *
	 */

	public function testGetEstateLink()
	{
		global $wp_filter;
		$this->_pEstateList->loadEstates();
		$this->_pEstateList->estateIterator(); // jump to the first estate
		$pDataDetailView = new DataDetailView();
		$pDataDetailView->setPageId(0);
		$pDataDetailViewHandler = $this->getMockBuilder(DataDetailViewHandler::class)
			->disableOriginalConstructor()
			->setMethods(['getDetailView'])
			->getMock();
		$pDataDetailViewHandler->method('getDetailView')->willReturn($pDataDetailView);
		$this->_pEnvironment->method('getDataDetailViewHandler')->willReturn($pDataDetailViewHandler);

		$this->assertEquals('#', $this->_pEstateList->getEstateLink());

		$this->set_permalink_structure('/%postname%/');
		$savePostBackup = $wp_filter['save_post'];
		$wp_filter['save_post'] = new \WP_Hook;
		$pWPPost = self::factory()->post->create_and_get([
			'post_author' => 1,
			'post_content' => '[oo_estate view="detail"]',
			'post_title' => 'Details',
			'post_type' => 'page',
		]);
		$wp_filter['save_post'] = $savePostBackup;
		$pDataDetailView->setPageId($pWPPost->ID);

		// slash missing at the end, which WP inserts in production
		$this->assertEquals('http://example.org/details/15', $this->_pEstateList->getEstateLink());
	}


	/**
	 *
	 */

	public function testGetEstateMovieLinks()
	{
		$this->assertEquals([], $this->_pEstateList->getEstateMovieLinks());
	}


	/**
	 *
	 */

	public function testGetMovieEmbedPlayers()
	{
		$this->assertEquals([], $this->_pEstateList->getMovieEmbedPlayers());
	}


	/**
	 *
	 */

	public function testGetEstatePictures()
	{
		$this->_pEstateList->loadEstates();
		$this->_pEstateList->estateIterator();

		$this->assertEqualSets([2, 3, 4], $this->_pEstateList->getEstatePictures());
		$this->assertEquals([2], $this->_pEstateList->getEstatePictures(['Titelbild']));
	}


	/**
	 *
	 */

	public function testGetEstatePictureUrl()
{
	$this->_pEstateList->loadEstates();
	$this->_pEstateList->estateIterator();
	$this->assertEquals($this->_estatePicturesByEstateId[15][2]['url'],
		$this->_pEstateList->getEstatePictureUrl(2));
	$this->assertEquals($this->_estatePicturesByEstateId[15][3]['url'].'@200x300',
		$this->_pEstateList->getEstatePictureUrl(3, ['width' => 200, 'height' => 300]));
}


	/**
	 *
	 */

	public function testGetEstatePictureTitle()
	{
		$expectation = [
			$this->_estatePicturesByEstateId[15][2]['title'],
			$this->_estatePicturesByEstateId[15][3]['title'],
		];
		$this->doTestGetEstatePictureMethodGeneric('getEstatePictureTitle', $expectation);
	}


	/**
	 *
	 */

	public function testGetEstatePictureText()
	{
		$expectation = [
			$this->_estatePicturesByEstateId[15][2]['text'],
			$this->_estatePicturesByEstateId[15][3]['text'],
		];
		$this->doTestGetEstatePictureMethodGeneric('getEstatePictureText', $expectation);
	}


	/**
	 *
	 */

	public function testGetEstatePictureValues()
	{
		$expectation = [
			$this->_estatePicturesByEstateId[15][2],
			$this->_estatePicturesByEstateId[15][3],
		];
		$this->doTestGetEstatePictureMethodGeneric('getEstatePictureValues', $expectation);
	}

	/**
	 * @param string $methodName
	 * @param array $expectedResults
	 * @throws APIEmptyResultException
	 * @throws UnknownViewException
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws HttpFetchNoResultException
	 * @throws ApiClientException
	 */
	private function doTestGetEstatePictureMethodGeneric(string $methodName, array $expectedResults)
	{
		$this->_pEstateList->loadEstates();
		$this->_pEstateList->estateIterator();
		$this->assertEquals($expectedResults[0], $this->_pEstateList->$methodName(2));
		$this->assertEquals($expectedResults[1], $this->_pEstateList->$methodName(3));
	}


	/**
	 *
	 */

	public function testGetEstateContactIds()
	{
		$this->_pEstateList->loadEstates();
		$this->_pEstateList->estateIterator();
		$this->assertEquals([], $this->_pEstateList->getEstateContactIds());

		$this->_pEstateList->estateIterator();
		$this->assertEquals([50], $this->_pEstateList->getEstateContactIds());

		$this->_pEstateList->estateIterator();
		$this->assertEquals([52], $this->_pEstateList->getEstateContactIds());
	}


	/**
	 *
	 */

	public function testGetEstateContacts()
	{
		$valueMap = [
			['50', ['Vorname' => 'John', 'Name' => 'Doe']],
			['52', ['Vorname' => 'Max', 'Name' => 'Mustermann']],
		];
		$pAddressDataMock = $this->getMockBuilder(AddressList::class)
			->setMethods(['__construct', 'getAddressById', 'loadAdressesById'])
			->getMock();
		$pAddressDataMock->expects($this->once())->method('loadAdressesById')->with([50, 52], ['Vorname', 'Name']);
		$pAddressDataMock->method('getAddressById')->willReturnMap($valueMap);
		$this->_pEnvironment->method('getAddressList')->willReturn($pAddressDataMock);
		$this->_pEstateList->loadEstates();
		$this->_pEstateList->estateIterator();
		$this->assertEquals([], $this->_pEstateList->getEstateContacts());
		$this->_pEstateList->estateIterator();
		$this->assertEquals([new ArrayContainerEscape(['Vorname' => 'John', 'Name' => 'Doe'])],
			$this->_pEstateList->getEstateContacts());
		$this->_pEstateList->estateIterator();
		$this->assertEquals([new ArrayContainerEscape(['Vorname' => 'Max', 'Name' => 'Mustermann'])],
			$this->_pEstateList->getEstateContacts());
	}


	/**
	 *
	 */

	public function testGetCurrentEstateId()
	{
		$expectedEstateIds = [15, 1051, 1082, 1193, 5448];
		$this->_pEstateList->loadEstates();

		foreach ($expectedEstateIds as $estateId) {
			$this->_pEstateList->estateIterator();
			$this->assertEquals($estateId, $this->_pEstateList->getCurrentEstateId());
		}
	}


	/**
	 *
	 */

	public function testGetCurrentMultiLangEstateMainId()
	{
		$expectedEstateIds = [15, 1051, 1082, 1193, 1071];
		$this->_pEstateList->loadEstates();

		foreach ($expectedEstateIds as $estateId) {
			$this->_pEstateList->estateIterator();
			$this->assertEquals($estateId, $this->_pEstateList->getCurrentMultiLangEstateMainId());
		}
	}


	/**
	 *
	 */

	public function testGetEstateUnits()
	{
		$pDataListView = new DataListView(1, 'defaultUnits');
		$pEstateUnitsMock = $this->getMockBuilder(EstateUnits::class)
			->setMethods([
				'getEstateUnitsByName',
				'loadByMainEstates',
				'getSubEstateCount',
				'generateHtmlOutput',
			])
			->setConstructorArgs([$pDataListView])
			->getMock();
		$pEstateUnitsMock
			->expects($this->once())
			->method('loadByMainEstates')
			->with($this->equalTo($this->_pEstateList));
		$pEstateUnitsMock
			->expects($this->once())
			->method('getSubEstateCount')
			->with($this->equalTo(15))
			->willReturn(2);
		$pEstateUnitsMock
			->expects($this->once())
			->method('generateHtmlOutput')
			->with($this->equalTo(15))
			->willReturn('Entities output for estate 15');
		$this->_pEnvironment
			->method('getEstateUnitsByName')
			->with('defaultUnits')
			->willReturn($pEstateUnitsMock);

		$this->_pEstateList->loadEstates();
		$this->_pEstateList->estateIterator();

		$this->assertNull($this->_pEstateList->getUnitsViewName());
		$this->assertEquals('', $this->_pEstateList->getEstateUnits());

		$this->_pEstateList->setUnitsViewName('defaultUnits');
		$this->assertEquals('defaultUnits', $this->_pEstateList->getUnitsViewName());

		$this->assertEquals('Entities output for estate 15', $this->_pEstateList->getEstateUnits());
	}


	/**
	 *
	 */

	public function testGetDocument()
	{
		$this->_pEstateList->loadEstates();
		$this->_pEstateList->estateIterator();
		$this->assertEquals('http://example.org/document-pdf/test/15', $this->_pEstateList->getDocument());

		$this->_pEstateList->estateIterator();
		$this->assertEquals('http://example.org/document-pdf/test/1051', $this->_pEstateList->getDocument());
	}


	/**
	 *
	 */

	public function testGetDocumentEmpty()
	{
		$pEstateList = new EstateList(new DataListView(13, 'test'));
		$pEstateList->estateIterator();
		$this->assertEmpty($pEstateList->getDocument());

		$pEstateList->estateIterator();
		$this->assertEmpty($pEstateList->getDocument());
	}


	/**
	 *
	 */

	public function testGetVisibleFilterableFields()
	{
		$pMockOutputFields = $this->getMockBuilder(OutputFields::class)
			->setMethods(['getVisibleFilterableFields'])
			->disableOriginalConstructor()
			->getMock();
		$pMockOutputFields->expects($this->once())
			->method('getVisibleFilterableFields')
			->willReturn(['objektart' => 'haus', 'objekttyp' => 'reihenhaus']);
		$this->_pContainer->set(OutputFields::class, $pMockOutputFields);

		$pFieldsCollection = new FieldsCollection();
		$pFieldObjektArt = new Field('objektart', 'estate');
		$pFieldObjektArt->setType(FieldTypes::FIELD_TYPE_SINGLESELECT);
		$pFieldObjektTyp = new Field('objekttyp', 'estate');
		$pFieldObjektTyp->setType(FieldTypes::FIELD_TYPE_SINGLESELECT);
		$pFieldsCollection->addField($pFieldObjektArt);
		$pFieldsCollection->addField($pFieldObjektTyp);

		$expectation = [
			'objektart' => [
				'name' => 'objektart',
				'type' => 'singleselect',
				'value' => 'haus',
				'label' => '',
				'default' => null,
				'length' => null,
				'permittedvalues' => [],
				'content' => '',
				'module' => 'estate',
				'rangefield' => false,
				'additionalTranslations' => [],
				'compoundFields' => [],
				'labelOnlyValues' => [],
			],
			'objekttyp' => [
				'name' => 'objekttyp',
				'type' => 'singleselect',
				'value' => 'reihenhaus',
				'label' => '',
				'default' => null,
				'length' => null,
				'permittedvalues' => [],
				'content' => '',
				'module' => 'estate',
				'rangefield' => false,
				'additionalTranslations' => [],
				'compoundFields' => [],
				'labelOnlyValues' => [],
			],
		];

		$pFieldsCollectionBuilderMock = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
				->disableOriginalConstructor()
				->getMock();
		$pFieldsCollectionBuilderMock->method('addFieldsAddressEstate')
			->willReturnCallback(function (FieldsCollection $pFieldsCollectionOut)
			use ($pFieldsCollection, $pFieldsCollectionBuilderMock): FieldsCollectionBuilderShort
			{
				$pFieldsCollectionOut->merge($pFieldsCollection);
				return $pFieldsCollectionBuilderMock;
			});

		$this->_pContainer->set(FieldsCollectionBuilderShort::class, $pFieldsCollectionBuilderMock);
		$this->assertEquals($expectation, $this->_pEstateList->getVisibleFilterableFields());
	}


	/**
	 *
	 */

	public function testGetEstateFiles()
	{
		$pClosureGetEstateFiles = Closure::bind(function() {
			return $this->getEstateFiles();
		}, $this->_pEstateList, EstateList::class);

		$this->_pEstateList->loadEstates();
		$this->_pEstateList->estateIterator();

		$this->assertInstanceOf(EstateFiles::class, $pClosureGetEstateFiles());
	}


	/**
	 *
	 */

	public function testDefaultFilterBuilder()
	{
		$this->assertInstanceOf(DefaultFilterBuilderListView::class, $this->_pEstateList->getDefaultFilterBuilder());
		$this->assertEquals($this->_pEnvironment->getDefaultFilterBuilder(), $this->_pEstateList->getDefaultFilterBuilder());

		$pNewDefaultFilterBuilder = new DefaultFilterBuilderPresetEstateIds([2]);
		$this->_pEnvironment->expects($this->once())->method('setDefaultFilterBuilder')->with($pNewDefaultFilterBuilder);
		$this->_pEstateList->setDefaultFilterBuilder($pNewDefaultFilterBuilder);
	}


	/**
	 *
	 */

	public function testGetEstateIds()
	{
		$this->_pEstateList->loadEstates();
		$this->assertEquals([15, 1051, 1082, 1193, 5448], $this->_pEstateList->getEstateIds());
	}


	/**
	 *
	 */

	public function testGetDataView()
	{
		$this->assertEquals($this->getDataView(), $this->_pEstateList->getDataView());
	}


	/**
	 *
	 */

	public function testFormatOutput()
	{
		$this->assertTrue($this->_pEstateList->getFormatOutput());
		$this->_pEstateList->setFormatOutput(false);
		$this->assertFalse($this->_pEstateList->getFormatOutput());
	}


	/**
	 *
	 */

	public function testGeoSearchBuilder()
	{
		$pNewGeoSearchBuilder = $this->getMockBuilder(GeoSearchBuilderFromInputVars::class)->getMock();
		$this->assertInstanceOf(GeoSearchBuilderEmpty::class, $this->_pEstateList->getGeoSearchBuilder());
		$this->_pEstateList->setGeoSearchBuilder($pNewGeoSearchBuilder);
		$this->assertInstanceOf(GeoSearchBuilderFromInputVars::class, $this->_pEstateList->getGeoSearchBuilder());
	}

	/**
	 *
	 */
	public function testShowReferenceStatus()
	{
		$EstateListMock = $this->getMockBuilder(EstateList::class)
			->disableOriginalConstructor()
			->setMethods(['getShowReferenceStatus'])
			->getMock();
		$EstateListMock->method('getShowReferenceStatus')->willReturn(false);
		$this->_pEstateList->loadEstates();
		$result = $this->_pEstateList->estateIterator();
		$this->assertEquals('', $result['vermarktungsstatus']);
	}


	/**
	 *
	 * @before
	 *
	 */

	public function prepareEstateList()
	{
		$this->_pSDKWrapperMocker = new SDKWrapperMocker();

		$dataReadEstateFormatted = json_decode
			(file_get_contents(__DIR__.'/resources/ApiResponseReadEstatesPublishedENG.json'), true);
		$responseReadEstate = $dataReadEstateFormatted['response'];
		$parametersReadEstate = $dataReadEstateFormatted['parameters'];
		$dataReadEstateRaw = json_decode
			(file_get_contents(__DIR__.'/resources/ApiResponseReadEstatesPublishedENGRaw.json'), true);
		$responseReadEstateRaw = $dataReadEstateRaw['response'];
		$parametersReadEstateRaw = $dataReadEstateRaw['parameters'];
		$responseGetIdsFromRelation = json_decode
			(file_get_contents(__DIR__.'/resources/ApiResponseGetIdsFromRelation.json'), true);
		$responseGetEstatePictures = json_decode
			(file_get_contents(__DIR__.'/resources/ApiResponseGetEstatePictures.json'), true);

		$this->_pSDKWrapperMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_READ, 'estate', '', $parametersReadEstate, null, $responseReadEstate);
		$this->_pSDKWrapperMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_READ, 'estate', '', $parametersReadEstateRaw, null, $responseReadEstateRaw);
		$this->_pSDKWrapperMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_GET, 'idsfromrelation', '', [
				'parentids' => [15, 1051, 1082, 1193, 1071],
				'relationtype' => 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:contactPerson'
			], null, $responseGetIdsFromRelation);

		$this->_pSDKWrapperMocker->addResponseByParameters
		(onOfficeSDK::ACTION_ID_GET, 'estatepictures', '', [
			'estateids' => [15,1051,1082,1193,5448],
			'categories' => ['Titelbild', "Foto"],
			'language' => 'ENG'
		], null, $responseGetEstatePictures);

		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();
		$this->_pContainer->set(SDKWrapper::class, $this->_pSDKWrapperMocker);
		$this->_pEnvironment = $this->getMockBuilder(EstateListEnvironmentDefault::class)
			->setConstructorArgs([$this->_pContainer])
			->setMethods([
				'getDefaultFilterBuilder',
				'getGeoSearchBuilder',
				'getEstateStatusLabel',
				'setDefaultFilterBuilder',
				'getEstateFiles',
				'getFieldnames',
				'getAddressList',
				'getEstateUnitsByName',
				'getDataDetailViewHandler',
			])
			->getMock();
		$pEstatePicturesMock = new EstateFiles;
		$this->_pEnvironment->method('getEstateFiles')
			->willReturn($pEstatePicturesMock);
		$pDataListView = $this->getDataView();
		$pFieldsCollectionBuilderShort = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
			->setConstructorArgs([$this->_pContainer])
			->getMock();
		$pDefaultFilterBuilder = new DefaultFilterBuilderListView($pDataListView, $pFieldsCollectionBuilderShort);
		$this->_pEnvironment->method('getDefaultFilterBuilder')->willReturn($pDefaultFilterBuilder);
		$this->_pEstateList = new EstateList($pDataListView, $this->_pEnvironment);

		$pGeoSearchBuilder = $this->getMockBuilder(GeoSearchBuilderEmpty::class)->setMethods(['buildParameters'])->getMock();
		$pGeoSearchBuilder->method('buildParameters')->willReturn(['radius' => 500, 'country' => 'DEU', 'zip' => '52068']);
		$this->_pEstateList->setGeoSearchBuilder($pGeoSearchBuilder);
		$this->_pEnvironment->method('getGeoSearchBuilder')->willReturn($pGeoSearchBuilder);
		$pEstateStatusLabel = $this->getMockBuilder(EstateStatusLabel::class)
			->setMethods(['getFieldsByPrio', 'getLabel'])
			->getMock();
		$pEstateStatusLabel->method('getFieldsByPrio')->willReturn([
			'referenz',
			'reserviert',
			'verkauft',
			'exclusive',
			'neu',
			'top_angebot',
			'preisreduktion',
			'courtage_frei',
			'objekt_des_tages',
		]);
		$this->_pEnvironment->method('getEstateStatusLabel')->willReturn
			($pEstateStatusLabel);
	}


	/**
	 *
	 * @return DataListView
	 *
	 */

	private function getDataView(): DataListView
	{
		$pDataView = new DataListView(1, 'test');
		$pDataView->setFields(['Id', 'objektart', 'objekttyp']);
		$pDataView->setSortby('Id');
		$pDataView->setSortorder('ASC');
		$pDataView->setFilterId(12);
		$pDataView->setPictureTypes(['Titelbild', 'Foto']);
		$pDataView->setAddressFields(['Vorname', 'Name']);
		$pDataView->setShowStatus(true);
		$pDataView->setShowReferenceStatus(false);
		$pDataView->setFilterableFields([GeoPosition::FIELD_GEO_POSITION]);
		$pDataView->setExpose('testExpose');
		return $pDataView;
	}


	/**
	 *
	 * @return DataListView
	 *
	 */

	private function getDataViewRandom(): DataListView
	{
		$pDataView = $this->getDataView();
		$pDataView->setRandom(true);
		return $pDataView;
	}
}
