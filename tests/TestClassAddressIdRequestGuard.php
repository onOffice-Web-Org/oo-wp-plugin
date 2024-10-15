<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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
use Generator;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\ArrayContainerEscape;
use onOffice\WPlugin\Controller\AddressDetailUrl;
use onOffice\WPlugin\DataView\DataAddressDetailView;
use onOffice\WPlugin\AddressDetail;
use onOffice\WPlugin\Factory\AddressListFactory;
use onOffice\WPlugin\Record\AddressIdRequestGuard;
use onOffice\WPlugin\SDKWrapper;
use WP_UnitTestCase;

/**
 *
 */

class TestClassAddressIdRequestGuard
	extends WP_UnitTestCase
{

	/** @var Container */
	private $_pContainer = null;

	/**
	 * @before
	 */
	public function prepare()
	{
		$switchLocale = 'DEU';
		$pSDKWrapperMocker = new SDKWrapperMocker();
		$pSDKWrapperMocker->addResponseByParameters
		(onOfficeSDK::ACTION_ID_READ, 'address', '3', [
			'data' => ['Vorname', 'Name', 'Zusatz1'],
			'outputlanguage' => $switchLocale
		], null, $this->prepareMockerForAddressDetailOne());

		$pSDKWrapperMocker->addResponseByParameters
		(onOfficeSDK::ACTION_ID_READ, 'address', '9', [
			'data' => ['Vorname', 'Name', 'Zusatz1'],
			'outputlanguage' => $switchLocale
		], null, $this->prepareMockerForAddressDetailTwo());

		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();
		$this->_pContainer->set(SDKWrapper::class, $pSDKWrapperMocker);
	}

	/**
	 *
	 * @dataProvider dataProvider
	 *
	 * @param int $addressId
	 * @param bool|array $iterator
	 * @param bool $result
	 *
	 */

	public function testIsValid(int $addressId, $iterator, bool $result)
	{
		$pAddressDetailFactory = $this->getMockBuilder(AddressListFactory::class)
			->disableOriginalConstructor()
			->getMock();
		$pAddressDetail = $this->getMockBuilder(AddressDetail::class)
			->setConstructorArgs([new DataAddressDetailView()])
			->setMethods(['loadSingleAddress', 'getRows'])
			->getMock();
		$pAddressDetail
			->expects($this->once())->method('getRows')
			->will($this->returnValue($iterator));
		$pAddressDetailFactory->method('createAddressDetail')->will($this->returnValue($pAddressDetail));
		$pSubject = new AddressIdRequestGuard($pAddressDetailFactory);
		$this->assertEquals($result, $pSubject->isValid($addressId));
	}


	/**
	 *
	 * @dataProvider dataProvider
	 *
	 * @param int $addressId
	 * @param bool|array $iterator
	 * @param bool $result
	 *
	 */
	public function testCreateAddressDetailLinkForSwitchLanguageWPML(int $addressId, $iterator, bool $result)
	{
		add_option('onoffice-address-detail-view-showInfoUserUrl', true);
		$this->run_activate_plugin_for_test( 'sitepress-multilingual-cms/sitepress.php' );
		add_filter('wpml_active_languages', function () {
			return [
				'en' => ['default_locale' => 'en_US'],
				'de' => ['default_locale' => 'de_DE'],
			];
		});
		$data = $iterator[$addressId];

		$url = 'https://www.onoffice.de/detail/';
		$title = $data ? '-' . $data['Vorname'] .  '-' . $data['Name'] .  '-' . $data['Zusatz1'] : '';
		$oldUrl = 'https://www.onoffice.de/detail/' . $addressId . $title . '/';
		$expectedUrl = 'https://www.onoffice.de/detail/' . $addressId . $title . '/';

		$pAddressDetailFactory = $this->getMockBuilder(AddressListFactory::class)
			->disableOriginalConstructor()
			->getMock();
		$pAddressDetail = $this->getMockBuilder(AddressDetail::class)
			->setConstructorArgs([new DataAddressDetailView()])
			->setMethods(['loadSingleAddress', 'getRows'])
			->getMock();
		$pAddressDetail->expects($this->once())->method('getRows')
			->will($this->returnValue($iterator));

		$pAddressDetailFactory->method('createAddressDetail')->will($this->returnValue($pAddressDetail));
		$pSubject = new AddressIdRequestGuard($pAddressDetailFactory, $this->_pContainer);
		$pSubject->isValid($addressId);

		$pAddressDetailUrl = new AddressDetailUrl();

		$result = $pSubject->createAddressDetailLinkForSwitchLanguageWPML($url, $addressId, $pAddressDetailUrl, $oldUrl, 'en_US');
		
		$this->assertEquals($expectedUrl, $result);
	}

	/**
	 *
	 * @return Generator
	 *
	 */

	public function dataProvider(): Generator
	{
		yield from [
			[3, [3 => new ArrayContainerEscape(['Id' => 3, 'Vorname' => 'testvorname-3', 'Name' => 'testname-3', 'Zusatz1' => 'company-3'])], true],
			[9, [9 => new ArrayContainerEscape(['Id' => 9, 'Vorname' => 'testvorname-9', 'Name' => 'testname-9', 'Zusatz1' => 'company-9'])], true],
		];
	}

	/**
	 * @param $plugin
	 * @return null
	 */
	private function run_activate_plugin_for_test($plugin) {
		$current = get_option('active_plugins');
		$plugin = plugin_basename(trim($plugin));

		if (!in_array($plugin, $current)) {
			$current[] = $plugin;
			sort($current);
			do_action('activate_plugin', trim($plugin));
			update_option('active_plugins', $current);
			do_action('activate_' . trim($plugin));
			do_action('activated_plugin', trim($plugin));
		}

		return null;
	}

	/**
	 *
	 */
	private function prepareMockerForAddressDetailOne()
	{
		$response = [
			'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:read',
			'resourceid' => '3',
			'resourcetype' => 'address',
			'identifier' => '',
			'data' => [
				'meta' => [
					'cntabsolute' => 1,
				],
				'records' => [
					0 => [
						'id' => 3,
						'type' => 'address',
						'elements' => [
							"Vorname" => "testvorname-3",
							"Name" => "testname-3",
							"Zusatz1" => "company-3"
						],
					],
				],
			],
			'status' => [
				'errorcode' => 0,
				'message' => 'OK',
			],
		];

		return $response;
	}

	/**
	 *
	 */
	private function prepareMockerForAddressDetailTwo()
	{
		$response = [
			'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:read',
			'resourceid' => '9',
			'resourcetype' => 'address',
			'identifier' => '',
			'data' => [
				'meta' => [
					'cntabsolute' => 1,
				],
				'records' => [
					0 => [
						'id' => 9,
						'type' => 'address',
						'elements' => [
							"Vorname" => "testvorname-9",
							"Name" => "testname-9",
							"Zusatz1" => "company-9"
						],
					],
				],
			],
			'status' => [
				'errorcode' => 0,
				'message' => 'OK',
			],
		];

		return $response;
	}
}