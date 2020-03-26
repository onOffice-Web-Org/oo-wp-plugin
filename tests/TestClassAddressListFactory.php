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

use DI\ContainerBuilder;
use onOffice\WPlugin\AddressList;
use onOffice\WPlugin\Controller\AddressListEnvironmentDefault;
use onOffice\WPlugin\Factory\AddressListFactory;

class TestClassAddressListFactory
{
	/**
	 * @var AddressListFactory
	 */
	private $_pFactory;

	/**
	 * @before
	 */
	public function prepare()
	{
		$pContainerBuilder = new ContainerBuilder();
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();
		$pAddressListEnvironment = $pContainer->get(AddressListEnvironment::class);

		$this->_pFactory = new AddressListFactory($pAddressListEnvironment);
	}

	/**
	 *
	 */
	public function testInstance()
	{
		$this->assertInstanceOf(AddressListFactory::class, $this->_pFactory);
	}

	/**
	 * @covers \onOffice\WPlugin\Factory\AddressListFactory::create
	 */
	public function testCreate()
	{
		$this->assertInstanceOf(AddressList::class, $this->_pFactory->create());
	}
}