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

use onOffice\SDK\onOfficeSDK;
use onOffice\tests\SDKWrapperMocker;
use onOffice\WPlugin\AddressList;
use onOffice\WPlugin\ArrayContainerEscape;
use onOffice\WPlugin\Controller\EstateListEnvironment;
use onOffice\WPlugin\Controller\EstateListEnvironmentDefault;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\EstateFiles;
use onOffice\WPlugin\EstateList;
use onOffice\WPlugin\EstateUnits;
use onOffice\WPlugin\Field\OutputFields;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListView;
use onOffice\WPlugin\Filter\DefaultFilterBuilderPresetEstateIds;
use onOffice\WPlugin\Filter\GeoSearchBuilderEmpty;
use onOffice\WPlugin\Filter\GeoSearchBuilderFromInputVars;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\Types\EstateStatusLabel;
use onOffice\WPlugin\Types\FieldsCollection;

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

	/** @var PHPUnit_Framework_MockObject_MockObject */
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
		$pFieldnamesMock = $this->getMock(Fieldnames::class, ['getFieldLabel'], [$pFields]);
		$pFieldnamesMock->method('getFieldLabel')->with(
				$this->equalTo('testfield'), $this->equalTo(onOfficeSDK::MODULE_ESTATE))
			->will($this->returnValue('Test Field'));
		$this->_pEnvironment->method('getFieldnames')->will($this->returnValue($pFieldnamesMock));
		$this->assertEquals('Test Field', $this->_pEstateList->getFieldLabel('testfield'));
	}


	/**
	 *
	 */

	public function testGetEstateLink()
	{
		global $wp_rewrite;
		$this->_pEstateList->loadEstates();
		$this->_pEstateList->estateIterator(); // jump to the first estate
		$pDataDetailView = new DataDetailView();
		$pDataDetailView->setPageId(0);
		$this->_pEnvironment->method('getDataDetailView')->will($this->returnValue($pDataDetailView));
		$this->assertEquals('#', $this->_pEstateList->getEstateLink());

		$wp_rewrite = new WP_Rewrite();
		$wp_rewrite->permalink_structure = '/%postname%/';
		$pWPPost = self::factory()->post->create_and_get([
			'post_author' => 1,
			'post_content' => '[oo_estate view="detail"]',
			'post_title' => 'Details',
			'post_type' => 'page',
		]);
		$pDataDetailView->setPageId($pWPPost->ID);


		// slash missing at the end, which WP inserts in production
		$this->assertEquals('http://example.org/details15', $this->_pEstateList->getEstateLink());
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
		$pEstatePicturesMock = $this->getMock(EstateFiles::class, ['registerRequest', 'parseRequest', 'getEstatePictures'], [$this->getDataView()->getPictureTypes()]);
		$pEstatePicturesMock->method('getEstatePictures')->with(15)->willReturn($this->_estatePicturesByEstateId[15]);
		$this->_pEnvironment->method('getEstateFiles')->with($this->getDataView()->getPictureTypes())->will($this->returnValue($pEstatePicturesMock));

		$this->_pEstateList->loadEstates();
		$this->_pEstateList->estateIterator();
		$pEstateFilesResult = Closure::bind(function() { return $this->_pEstateFiles; }, $this->_pEstateList, EstateList::class)();

		$this->assertEquals($pEstatePicturesMock, $pEstateFilesResult);

		$this->assertEqualSets([2, 3, 4], $this->_pEstateList->getEstatePictures());
		$this->assertEquals([2], $this->_pEstateList->getEstatePictures(['Titelbild']));
	}


	/**
	 *
	 */

	public function testGetEstatePictureUrl()
	{
		$pEstatePicturesMock = $this->getMock(EstateFiles::class,
			['registerRequest', 'parseRequest', 'getEstatePictures', 'getEstateFileUrl'], [$this->getDataView()->getPictureTypes()]);
		$pEstatePicturesMock
			->expects($this->at(2))
			->method('getEstateFileUrl')
			->with(2, 15, null)
			->willReturn($this->_estatePicturesByEstateId[15][2]['url']);
		$pEstatePicturesMock
			->expects($this->at(3))
			->method('getEstateFileUrl')
			->with(3, 15, ['width' => 200, 'height' => 300])
			->willReturn($this->_estatePicturesByEstateId[15][3]['url'].'@200x300');
		$this->_pEnvironment->method('getEstateFiles')
			->with($this->getDataView()
			->getPictureTypes())
			->willReturn($pEstatePicturesMock);

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
	 *
	 */

	private function doTestGetEstatePictureMethodGeneric(string $methodName, array $expectedResults)
	{
		$pEstatePicturesMock = $this->getMock(EstateFiles::class,
			['registerRequest', 'parseRequest', 'getEstatePictures', $methodName], [$this->getDataView()->getPictureTypes()]);
		$pEstatePicturesMock
			->expects($this->at(2))
			->method($methodName)
			->with(2, 15)
			->willReturn($expectedResults[0]);
		$pEstatePicturesMock
			->expects($this->at(3))
			->method($methodName)
			->with(3, 15)
			->willReturn($expectedResults[1]);
		$this->_pEnvironment->method('getEstateFiles')
			->with($this->getDataView()
			->getPictureTypes())
			->willReturn($pEstatePicturesMock);

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
		$pAddressDataMock = $this->getMock(AddressList::class, ['__construct', 'getAddressById', 'loadAdressesById']);
		$pAddressDataMock->expects($this->once())->method('loadAdressesById')->with([50, 52], ['Vorname', 'Name']);
		$pAddressDataMock->method('getAddressById')->will($this->returnValueMap($valueMap));
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
		$pEstateUnitsMock = $this->getMock(EstateUnits::class, [
			'getEstateUnitsByName',
			'loadByMainEstates',
			'getSubEstateCount',
			'generateHtmlOutput',
		], [$pDataListView]);
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

		// escaped url. The path is kind of broken in test environment.
		$expectationEscapedEstate15 = '/onoffice/document.php?estateid=15&#038;language=ENG&#038;configindex=test';
		$this->assertStringStartsWith('http://example.org/', $this->_pEstateList->getDocument());
		$this->assertStringEndsWith($expectationEscapedEstate15, $this->_pEstateList->getDocument());

		$this->_pEstateList->estateIterator();
		$this->assertStringStartsWith('http://example.org/', $this->_pEstateList->getDocument());
		$expectationEscapedEstate1051 = '/onoffice/document.php?estateid=1051&#038;language=ENG&#038;configindex=test';
		$this->assertStringEndsWith($expectationEscapedEstate1051, $this->_pEstateList->getDocument());
	}


	/**
	 *
	 */

	public function testGetVisibleFilterableFields()
	{
		$pMockOutputFields = $this->getMock(OutputFields::class, ['getVisibleFilterableFields'], [], '', false);
		$pMockOutputFields->method('getVisibleFilterableFields')->willReturn(['objektart' => 'haus', 'objekttyp' => 'reihenhaus']);
		$this->_pEnvironment->method('getOutputFields')->willReturn($pMockOutputFields);

		$valueMap = [
			['objektart', 'estate', ['name' => 'objektart', 'type' => 'singleselect']],
			['objekttyp', 'estate', ['name' => 'objekttyp', 'type' => 'singleselect']],
		];

		$pFieldsCollection = new FieldsCollection();
		$pMockFieldnames = $this->getMock(Fieldnames::class, ['getFieldInformation'], [$pFieldsCollection]);
		$pMockFieldnames->method('getFieldInformation')->will($this->returnValueMap($valueMap));
		$this->_pEnvironment->method('getFieldnames')->willReturn($pMockFieldnames);

		$expectation = [
			'objektart' => [
				'name' => 'objektart',
				'type' => 'singleselect',
				'value' => 'haus',
			],
			'objekttyp' => [
				'name' => 'objekttyp',
				'type' => 'singleselect',
				'value' => 'reihenhaus',
			],
		];
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
		$pNewGeoSearchBuilder = $this->getMock(GeoSearchBuilderFromInputVars::class);
		$this->assertInstanceOf(GeoSearchBuilderEmpty::class, $this->_pEstateList->getGeoSearchBuilder());
		$this->_pEnvironment->expects($this->once())->method('setGeoSearchBuilder')->with($pNewGeoSearchBuilder);
		$this->_pEstateList->setGeoSearchBuilder($pNewGeoSearchBuilder);
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

		$this->_pSDKWrapperMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_READ, 'estate', '', $parametersReadEstate, null, $responseReadEstate);
		$this->_pSDKWrapperMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_READ, 'estate', '', $parametersReadEstateRaw, null, $responseReadEstateRaw);
		$this->_pSDKWrapperMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_GET, 'idsfromrelation', '', [
				'parentids' => [15, 1051, 1082, 1193, 1071],
				'relationtype' => 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:contactPerson'
			], null, $responseGetIdsFromRelation);
		$this->_pEnvironment = $this->getMock(EstateListEnvironment::class);
		$this->_pEnvironment->method('getSDKWrapper')->willReturn($this->_pSDKWrapperMocker);
		$pDataListView = $this->getDataView();
		$pDefaultFilterBuilder = new DefaultFilterBuilderListView($pDataListView);
		$this->_pEnvironment->method('getDefaultFilterBuilder')->willReturn($pDefaultFilterBuilder);
		$this->_pEstateList = new EstateList($pDataListView, $this->_pEnvironment);

		$pGeoSearchBuilder = $this->getMock(GeoSearchBuilderEmpty::class, ['buildParameters']);
		$pGeoSearchBuilder->method('buildParameters')->willReturn(['radius' => 500, 'country' => 'DEU', 'zip' => '52068']);
		$this->_pEnvironment->method('getGeoSearchBuilder')->willReturn($pGeoSearchBuilder);
		$this->_pEnvironment->method('getEstateStatusLabel')->willReturn
			($this->getMock(EstateStatusLabel::class, ['getFieldsByPrio', 'getLabel']));
		$this->_pEnvironment->getEstateStatusLabel()->method('getFieldsByPrio')->willReturn([
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
		$pDataView->setFilterableFields([GeoPosition::FIELD_GEO_POSITION]);
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
