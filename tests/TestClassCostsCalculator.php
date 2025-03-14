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

namespace onOffice\tests;

use DI\Container;
use WP_UnitTestCase;
use DI\ContainerBuilder;
use onOffice\WPlugin\Field\CostsCalculator;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\SDKWrapper;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2024, onOffice(R) GmbH
 *
 */

class TestClassCostsCalculator
	extends WP_UnitTestCase
{
	/** @var Container */
	private $_pContainer;

	/** @var CostsCalculator */
	private $_pCostsCalculator = null;

	/** @var string[] */
	private $_totalCostsData = [
		'kaufpreis' => [
			'raw' => 123456.56,
			'default' => '123.456,56 €'
		],
		'bundesland' => [
			'raw' => 4321,
			'default' => '4.321 €'
		],
		'aussen_courtage' => [
			'raw' => 22222,
			'default' => '22.222 €'
		],
		'notary_fees' => [
			'raw' => 1852,
			'default' => '1.852 €'
		],
		'land_register_entry' => [
			'raw' => 617,
			'default' => '617 €'
		],
		'total_costs' => [
			'raw' => 152468.56,
			'default' => '152.468,56 €'
		]
	];

	/**
	 * @before
	 */
	public function prepare()
	{
		$pSDKWrapperMocker = new SDKWrapperMocker();
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();
		$dataGetFieldCurrency = json_decode
			(file_get_contents(__DIR__.'/resources/ApiResponseGetFieldsCurrency.json'), true);
		$responseGetFieldCurrency = $dataGetFieldCurrency['response'];
		$parametersGetFieldCurrency = $dataGetFieldCurrency['parameters'];

		$pSDKWrapperMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_GET, 'fields', '', $parametersGetFieldCurrency, null, $responseGetFieldCurrency);

		$this->_pContainer->set(SDKWrapper::class, $pSDKWrapperMocker);
		$this->_pCostsCalculator = $this->_pContainer->get(CostsCalculator::class);
	}

	/**
	 *
	 */
	public function testGetShowMapConfig()
	{
		$recordRaw = [
			'kaufpreis' => '123456.56',
			'bundesland' => 'Bayern',
			'waehrung' => 'EUR'
		];
		$propertyTransferTax = [
			'Bayern' => 3.5
		];
		$externalCommission = '18';

		$totalCostsData = $this->_pCostsCalculator->getTotalCosts($recordRaw, $propertyTransferTax, $externalCommission);
		$this->assertEquals($totalCostsData, $this->_totalCostsData);
	}
}
