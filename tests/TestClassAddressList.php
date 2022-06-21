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
use Closure;
use onOffice\SDK\onOfficeSDK;
use onOffice\tests\SDKWrapperMocker;
use onOffice\WPlugin\AddressList;
use onOffice\WPlugin\ArrayContainer;
use onOffice\WPlugin\ArrayContainerEscape;
use onOffice\WPlugin\Controller\AddressListEnvironment;
use onOffice\WPlugin\DataView\DataListViewAddress;
use onOffice\WPlugin\Field\FieldnamesEnvironment;
use onOffice\WPlugin\Field\OutputFields;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierHandler;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use WP_UnitTestCase;
use function json_decode;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 * @covers onOffice\WPlugin\AddressList
 *
 */

class TestClassAddressList
	extends WP_UnitTestCase
{
	/** @var array */
	private $_expectedRecords = [
		13 => array(
			'Name' => 'Firestone',
			'KdNr' => 9,
			'Vorname' => 'Fred',
			'phone_business' => '01234567890',
			'phone_private' => '01122334455',
			'phone' => [
				'01234567890',
				'01122334455',
			],
		),
		37 => array(
			'Name' => 'Fleißig',
			'KdNr' => 12,
			'Vorname' => 'Heinrich',
		),
	];


	/** @var AddressList */
	private $_pAddressList = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$pSDKWrapper = new SDKWrapperMocker();
		$response = $this->getResponseGetRows();
		$parameters = [
			'recordids' => [13, 37],
			'data' => ['Name', 'KdNr', 'Vorname', 'phone'],
			'outputlanguage' => Language::getDefault(),
			'formatoutput' => true,
		];

		$pSDKWrapper->addResponseByParameters
			(onOfficeSDK::ACTION_ID_READ, 'address', '', $parameters, null, $response);
		$pSDKWrapper->addResponseByParameters
			(onOfficeSDK::ACTION_ID_READ, 'address', '', [], null, $response);

		$pMockViewFieldModifierHandler = $this->getMockBuilder(ViewFieldModifierHandler::class)
			->setMethods(['processRecord', 'getAllAPIFields'])
			->disableOriginalConstructor()
			->getMock();
		$pMockViewFieldModifierHandler->method('processRecord')->will($this->returnArgument(0));
		$pMockViewFieldModifierHandler->method('getAllAPIFields')
			->will($this->returnValue(['Name', 'KdNr', 'Vorname']));

		$pMockFieldnames = $this->getMockBuilder(Fieldnames::class)
			->setMethods(['getFieldLabel', 'loadLanguage', 'getFieldInformation'])
			->setConstructorArgs([new FieldsCollection(), false,
				$this->getMockBuilder(FieldnamesEnvironment::class)->getMock()])
			->getMock();
		$module = onOfficeSDK::MODULE_ADDRESS;
		$pMockFieldnames->method('getFieldLabel')->will($this->returnValueMap([
			['KdNr', $module, 'Kundennummer'],
			['phone', $module, '<Telefon>'],
		]));

		$pMockFieldnames->method('getFieldInformation')->will($this->returnValueMap([
			['KdNr', $module, ['type' => FieldTypes::FIELD_TYPE_VARCHAR]],
			['HerkunftKontakt', $module, ['type' => FieldTypes::FIELD_TYPE_MULTISELECT]],
			['Vorname', $module, ['type' => FieldTypes::FIELD_TYPE_TEXT]],
			['Name', $module, ['type' => FieldTypes::FIELD_TYPE_TEXT]],
		]));

		$pMockFieldnames->expects($this->once())->method('loadLanguage');

		$pMockOutputFields = $this->getMockBuilder(OutputFields::class)
			->disableOriginalConstructor()
			->setMethods(['getVisibleFilterableFields'])
			->getMock();
		$pMockOutputFields->method('getVisibleFilterableFields')
			->will($this->returnValue(['KdNr' => 4, 'Vorname' => null, 'Name' => 'Stefansson']));

		$pMockConfig = $this->getMockBuilder(AddressListEnvironment::class)->getMock();
		$pMockConfig->method('getSDKWrapper')->will($this->returnValue($pSDKWrapper));
		$pMockConfig->method('getViewFieldModifierHandler')
			->will($this->returnValue($pMockViewFieldModifierHandler));
		$pMockConfig->method('getFieldnames')->will($this->returnValue($pMockFieldnames));
		$pMockConfig->method('getOutputFields')->will($this->returnValue($pMockOutputFields));

		$pFieldsCollectionBuilderMock = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
				->setConstructorArgs([new Container()])
				->getMock();

		$pFieldsCollectionNewFields = new FieldsCollection;
		$pFieldsCollectionNewFields->addField(new Field('KdNr', onOfficeSDK::MODULE_ADDRESS));
		$pFieldsCollectionNewFields->addField(new Field('Vorname', onOfficeSDK::MODULE_ADDRESS));
		$pFieldsCollectionNewFields->addField(new Field('Name', onOfficeSDK::MODULE_ADDRESS));

		$pFieldsCollectionBuilderMock->method('addFieldsAddressEstate')
			->will($this->returnCallback(function(FieldsCollection $pCollection)
			use ($pFieldsCollectionBuilderMock, $pFieldsCollectionNewFields): FieldsCollectionBuilderShort {
				$pCollection->merge($pFieldsCollectionNewFields);
				return $pFieldsCollectionBuilderMock;
			}));

		$pMockConfig->method('getFieldsCollectionBuilderShort')->willReturn($pFieldsCollectionBuilderMock);

		$this->_pAddressList = new AddressList($pMockConfig);
	}


	/**
	 *
	 * @param bool $raw
	 * @param string $containerClass
	 *
	 */

	private function runTestGetRows(bool $raw, string $containerClass)
	{
		$this->_pAddressList->loadAdressesById([13, 37], ['Name', 'KdNr', 'Vorname', 'phone']);
		$records = $this->_pAddressList->getRows($raw);
		$expectationRecords = $this->_expectedRecords;

		foreach ($expectationRecords as $recordId => $values) {
			$this->assertArrayHasKey($recordId, $records);
			$pRecord = $records[$recordId];
			$this->assertInstanceOf($containerClass, $pRecord);

			foreach ($values as $key => $value) {
				$this->assertEquals($value, $pRecord[$key]);
			}
		}
	}


	/**
	 *
	 */

	public function testGetRowsUnescaped()
	{
		$this->runTestGetRows(true, ArrayContainer::class);
	}


	/**
	 *
	 */

	public function testGetRowsEscaped()
	{
		$this->runTestGetRows(false, ArrayContainerEscape::class);
	}


	/**
	 *
	 */

	public function testGetRecordsById()
	{
		$this->_pAddressList->loadAdressesById([13, 37], ['Name', 'KdNr', 'Vorname', 'phone']);
		$this->assertEquals($this->_expectedRecords[13], $this->_pAddressList->getAddressById(13));
		$this->assertEquals($this->_expectedRecords[37], $this->_pAddressList->getAddressById(37));
	}


	/**
	 *
	 */

	public function testLoadAddresses()
	{
		$pAddressView = new DataListViewAddress(3, 'testView');
		$pAddressView->setShowPhoto(true);

		$pAddressList = $this->_pAddressList->withDataListViewAddress($pAddressView);
		$pAddressList->loadAddresses();
	}


	/**
	 *
	 */

	public function testGetFieldLabel()
	{
		$this->_pAddressList->loadAddresses();
		$this->assertEquals('Kundennummer', $this->_pAddressList->getFieldLabel('KdNr'));
		$this->assertEquals('&lt;Telefon&gt;', $this->_pAddressList->getFieldLabel('phone'));
		$this->assertEquals('<Telefon>', $this->_pAddressList->getFieldLabel('phone', true));
	}


	/**
	 *
	 */

	public function testGetFieldType()
	{
		$this->_pAddressList->loadAddresses();
		$this->assertEquals(FieldTypes::FIELD_TYPE_VARCHAR,
			$this->_pAddressList->getFieldType('KdNr'));
		$this->assertEquals(FieldTypes::FIELD_TYPE_MULTISELECT,
			$this->_pAddressList->getFieldType('HerkunftKontakt'));
	}


	/**
	 *
	 */

	public function testGetVisibleFilterableFields()
	{
		$this->_pAddressList->loadAddresses();
		$expectedResult = [
			'KdNr' => [
				'type' => 'varchar',
				'value' => 4,
				'label' => '',
				'default' => null,
				'length' => null,
				'permittedvalues' => Array (),
				'content' => '',
				'module' => 'address',
				'rangefield' => false,
				'additionalTranslations' => Array (),
				'compoundFields' => Array (),
				'labelOnlyValues' => Array (),
				'name' => 'KdNr',
				'tablename' => ''
			],
			'Vorname' => [
				'type' => 'varchar',
				'value' => null,
				'label' => '',
				'default' => null,
				'length' => null,
				'permittedvalues' => Array (),
				'content' => '',
				'module' => 'address',
				'rangefield' => false,
				'additionalTranslations' => Array (),
				'compoundFields' => Array (),
				'labelOnlyValues' => Array (),
				'name' => 'Vorname',
				'tablename' => ''
			],
			'Name' => [
				'type' => 'varchar',
				'value' => 'Stefansson',
				'label' => '',
				'default' => null,
				'length' => null,
				'permittedvalues' => Array (),
				'content' => '',
				'module' => 'address',
				'rangefield' => false,
				'additionalTranslations' => Array (),
				'compoundFields' => Array (),
				'labelOnlyValues' => Array (),
				'name' => 'Name',
				'tablename' => ''
			]
		];

		$this->assertEquals($expectedResult, $this->_pAddressList->getVisibleFilterableFields());
	}


	/**
	 *
	 */

	public function testWithDataListViewAddress()
	{
		$pClosureGetListViewAddress = Closure::bind(function() {
			return $this->_pDataViewAddress;
		}, $this->_pAddressList, AddressList::class);
		$this->assertEquals(new DataListViewAddress(0, 'default'), $pClosureGetListViewAddress());

		$pNewDataListView = new DataListViewAddress(15, 'testList');
		$pNewDataListView->setFields(['test1', 'test2', 'test3']);

		$pNewAddressList = $this->_pAddressList->withDataListViewAddress($pNewDataListView);
		$pClosureGetListViewAddressNew = $pClosureGetListViewAddress->bindTo($pNewAddressList, AddressList::class);
		$this->assertEquals($pNewDataListView, $pClosureGetListViewAddressNew());

		$pNewAddressList->loadAddresses();
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function getResponseGetRows()
	{
		$responseStr = '
		{
			"actionid": "urn:onoffice-de-ns:smart:2.5:smartml:action:read",
			"resourceid": "",
			"resourcetype": "address",
			"cacheable": true,
			"identifier": "",
			"data": {
				"meta": {
					"cntabsolute": null
				},
				"records": [
					{
						"id": 13,
						"type": "address",
						"elements": {
							"id": 13,
							"Name": "Firestone",
							"KdNr": 9,
							"Vorname": "Fred",
							"phone_business": "01234567890",
							"phone_private": "01122334455"
						}
					},
					{
						"id": 37,
						"type": "address",
						"elements": {
							"id": 37,
							"Name": "Fleißig",
							"KdNr": 12,
							"Vorname": "Heinrich"
						}
					}
				]
			},
			"status": {
				"errorcode": 0,
				"message": "OK"
			}
		}';

		return json_decode($responseStr, true);
	}
}
