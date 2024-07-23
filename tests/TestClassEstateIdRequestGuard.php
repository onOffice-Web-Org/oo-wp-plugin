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
use Generator;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\ArrayContainerEscape;
use onOffice\WPlugin\Controller\EstateDetailUrl;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\EstateDetail;
use onOffice\WPlugin\Factory\EstateListFactory;
use onOffice\WPlugin\Record\EstateIdRequestGuard;
use onOffice\WPlugin\SDKWrapper;
use WP_UnitTestCase;

/**
 *
 */

class TestClassEstateIdRequestGuard
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
		(onOfficeSDK::ACTION_ID_READ, 'estate', '3', [
			'data' => ['objekttitel'],
			'estatelanguage' => $switchLocale,
			'outputlanguage' => $switchLocale
		], null, $this->prepareMockerForEstateDetailOne());

		$pSDKWrapperMocker->addResponseByParameters
		(onOfficeSDK::ACTION_ID_READ, 'estate', '9', [
			'data' => ['objekttitel'],
			'estatelanguage' => $switchLocale,
			'outputlanguage' => $switchLocale
		], null, $this->prepareMockerForEstateDetailTwo());

		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();
		$this->_pContainer->set(SDKWrapper::class, $pSDKWrapperMocker);
	}

	/**
	 *
	 * @dataProvider dataProvider
	 *
	 * @param int $estateId
	 * @param bool|ArrayContainerEscape $iterator
	 * @param bool $result
	 *
	 */

	public function testIsValid(int $estateId, $iterator, bool $result)
	{
		$pEstateDetailFactory = $this->getMockBuilder(EstateListFactory::class)
			->disableOriginalConstructor()
			->getMock();
		$pEstateDetail = $this->getMockBuilder(EstateDetail::class)
			->setConstructorArgs([new DataDetailView()])
			->setMethods(['loadEstates', 'estateIterator'])
			->getMock();
		$pEstateDetail
			->expects($this->once())->method('estateIterator')
			->will($this->returnValue($iterator));
		$pEstateDetailFactory->method('createEstateDetail')->will($this->returnValue($pEstateDetail));
		$pSubject = new EstateIdRequestGuard($pEstateDetailFactory);
		$this->assertEquals($result, $pSubject->isValid($estateId));
	}


	/**
	 *
	 * @dataProvider dataProvider
	 *
	 * @param int $estateId
	 * @param bool|ArrayContainerEscape $iterator
	 * @param bool $result
	 *
	 */
	public function testCreateEstateDetailLinkForSwitchLanguageWPML(int $estateId, $iterator, bool $result)
	{
		add_option('onoffice-detail-view-showTitleUrl', true);
		$this->run_activate_plugin_for_test( 'sitepress-multilingual-cms/sitepress.php' );
		add_filter('wpml_active_languages', function () {
			return [
				'en' => ['default_locale' => 'en_US'],
				'de' => ['default_locale' => 'de_DE'],
			];
		});

		$url = 'https://www.onoffice.de/detail/';
		$title = $iterator ? '-' . $iterator['objekttitel'] : '';
		$oldUrl = 'https://www.onoffice.de/detail/' . $estateId . $title . '/';
		$expectedUrl = 'https://www.onoffice.de/detail/' . $estateId . $title . '/';

		$pEstateDetailFactory = $this->getMockBuilder(EstateListFactory::class)
			->disableOriginalConstructor()
			->getMock();
		$pEstateDetail = $this->getMockBuilder(EstateDetail::class)
			->setConstructorArgs([new DataDetailView()])
			->setMethods(['loadEstates', 'estateIterator'])
			->getMock();
		$pEstateDetail->expects($this->once())->method('estateIterator')
			->will($this->returnValue($iterator));

		$pEstateDetailFactory->method('createEstateDetail')->will($this->returnValue($pEstateDetail));
		$pSubject = new EstateIdRequestGuard($pEstateDetailFactory, $this->_pContainer);
		$pSubject->isValid($estateId);
		$pEstateDetailUrl = new EstateDetailUrl();

		$result = $pSubject->createEstateDetailLinkForSwitchLanguageWPML($url, $estateId, $pEstateDetailUrl, $oldUrl, 'en_US');
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
			[3, new ArrayContainerEscape(['Id' => 3, 'objekttitel' => 'title-3']), true],
			[5, false, false],
			[7, false, false],
			[9, new ArrayContainerEscape(['Id' => 9, 'objekttitel' => 'title-9']), true],
		];
	}

	/**
	 * @param $plugin
	 *
	 * @return null
	 */
	private function run_activate_plugin_for_test( $plugin ) {
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
	private function prepareMockerForEstateDetailOne()
	{
		$response = [
			'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:read',
			'resourceid' => '3',
			'resourcetype' => 'estate',
			'identifier' => '',
			'data' => [
				'meta' => [
					'cntabsolute' => 1,
				],
				'records' => [
					0 => [
						'id' => 3,
						'type' => 'estate',
						'elements' => [
							"objekttitel" => "title-3"
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
	private function prepareMockerForEstateDetailTwo()
	{
		$response = [
			'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:read',
			'resourceid' => '9',
			'resourcetype' => 'estate',
			'identifier' => '',
			'data' => [
				'meta' => [
					'cntabsolute' => 1,
				],
				'records' => [
					0 => [
						'id' => 9,
						'type' => 'estate',
						'elements' => [
							"objekttitel" => "title-9"
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