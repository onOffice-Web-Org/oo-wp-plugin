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

use DI\Container;
use DI\ContainerBuilder;
use onOffice\WPlugin\Controller\EstateTitleBuilder;
use onOffice\WPlugin\Controller\EstateViewDocumentTitleBuilder;
use onOffice\WPlugin\WP\WPQueryWrapper;

class TestClassEstateViewDocumentTitleBuilder
	extends \WP_UnitTestCase
{
	/** @var Container */
	private $_pContainer;

	/**
	 * @before
	 */
	public function prepare()
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();
		$pEstateTitleBuilder = $this->getMockBuilder(EstateTitleBuilder::class)
			->disableOriginalConstructor()
			->setMethods(['buildTitle','buildCustomFieldTitle'])
			->getMock();
		$pWPQuery = $this->getMockBuilder(\WP_Query::class)->disableOriginalConstructor()->getMock();
		$pWPQueryWrapper = $this->getMockBuilder(WPQueryWrapper::class)
			->setMethods(['getWPQuery'])
			->getMock();
		$pWPQueryWrapper->method('getWPQuery')->will($this->returnValue($pWPQuery));
		$this->_pContainer->set(EstateTitleBuilder::class, $pEstateTitleBuilder);
		$this->_pContainer->set(WPQueryWrapper::class, $pWPQueryWrapper);
	}

	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function testBuildDocumentTitleFieldForEmptyEstateId()
	{
		$this->_pContainer->get(WPQueryWrapper::class)->getWPQuery()
			->method('get')
			->will($this->returnValue(0));
		$pSubject = $this->_pContainer->get(EstateViewDocumentTitleBuilder::class);
		$input = 'title';
		$output = $pSubject->buildDocumentTitleField($input);
		$this->assertEmpty($output);
	}
	
	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function testBuildDocumentTitleFieldForShortTitle()
	{
		$this->_pContainer->get(WPQueryWrapper::class)->getWPQuery()
			->method('get')
			->will($this->returnValue(13));
		$this->_pContainer->get(EstateTitleBuilder::class)->expects($this->once())
			->method('buildCustomFieldTitle')
			->with(13, 'title')
			->will($this->returnValue('Nice Mansion in Takatukaland'));
		$pSubject = $this->_pContainer->get(EstateViewDocumentTitleBuilder::class);
		$input = 'title';
		$output = $pSubject->buildDocumentTitleField($input);
		$this->assertEquals('Nice Mansion in Takatukaland', $output);
	}
}