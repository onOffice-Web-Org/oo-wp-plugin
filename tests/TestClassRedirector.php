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
use onOffice\WPlugin\Utility\Redirector;

class TestClassRedirector
	extends \WP_UnitTestCase
{
	/**
	 * @var Redirector
	 */
	private $_pRedirector;

	/** @var Container */
	private $_pContainer;

	/**
	 * @before
	 */
	public function prepare()
	{
		global $wp;
		$wp->request       = 'address-detail-view/123';
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions( ONOFFICE_DI_CONFIG_PATH );
		$this->_pContainer        = $pContainerBuilder->build();
		$this->_pRedirector = $this->_pContainer->get( Redirector::class );
		$_SERVER['REQUEST_URI'] = '/address-detail-view/123';
	}

	/**
	 *
	 */
	public function stestInstance()
	{
		$this->assertInstanceOf( Redirector::class, $this->_pRedirector );
	}

	/**
	 * @covers \onOffice\WPlugin\Utility\Redirector::getCurrentLink
	 */
	public function testGetCurrentLink()
	{
		$this->assertEquals( 'http://example.org/address-detail-view/123', $this->_pRedirector->getCurrentLink() );
	}

	/**
	 * @covers \onOffice\WPlugin\Utility\Redirector::getUri
	 */
	public function testGetUri()
	{
		$this->assertEquals( 'address-detail-view/123', $this->_pRedirector->getUri() );
	}
}
