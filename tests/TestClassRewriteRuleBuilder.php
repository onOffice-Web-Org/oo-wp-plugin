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

declare(strict_types=1);

namespace onOffice\tests;

use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\Controller\RewriteRuleBuilder;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\WP\WPPageWrapper;
use onOffice\WPlugin\DataView\DataAddressDetailViewHandler;
use onOffice\WPlugin\DataView\DataAddressDetailView;

/**
 *
 * @preserveGlobalState disabled
 *
 */

class TestClassRewriteRuleBuilder
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
	}

	public function testAddCustomRewriteTags()
	{
		global $wp_rewrite;
		$wp_rewrite->rewritecode = [];
		$wp_rewrite->rewritereplace = [];
		$pSubject = $this->_pContainer->get(RewriteRuleBuilder::class);
		$pSubject->addCustomRewriteTags();
		$this->assertSame('%estate_id%', $wp_rewrite->rewritecode[0]);
		$this->assertSame('([^&]+)', $wp_rewrite->rewritereplace[0]);

		$this->assertSame('%view%', $wp_rewrite->rewritecode[1]);
		$this->assertSame('([^&]+)', $wp_rewrite->rewritereplace[1]);
	}

	public function testAddStaticRewriteRules()
	{
		global $wp_rewrite;
		$wp_rewrite->extra_rules_top = [];
		$pSubject = $this->_pContainer->get(RewriteRuleBuilder::class);
		$pSubject->addStaticRewriteRules();
		$this->assertSame([
			'^onoffice-estate-types.json$' => 'index.php?onoffice_estate_type_json=1',
			'^onoffice-estate-preview/?$' => 'index.php?onoffice_estate_preview=1',
			'^onoffice-applicant-search-preview/?$' => 'index.php?onoffice_applicant_search_preview=1',
			'^document-pdf/([^\/]+)/([0-9]+)/?$' =>
				'index.php?document_pdf=1&view=$matches[1]&estate_id=$matches[2]',
		], $wp_rewrite->extra_rules_top);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testAddDynamicRewriteRules()
	{
		global $wp_rewrite;
		$wp_rewrite->extra_rules_top = [];

		$pDataDetailViewHandler = $this->getMockBuilder(DataDetailViewHandler::class)
			->disableOriginalConstructor()
			->setMethods(['getDetailView'])
			->getMock();

		$pDataDetailView = new DataDetailView;
		$pDataDetailView->addToPageIdsHaveDetailShortCode(13);
		$pDataDetailView->setPageId(13);
		$pDataDetailViewHandler->expects($this->once())
			->method('getDetailView')
			->willReturn($pDataDetailView);

		$pWPPageWrapper = $this->getMockBuilder(WPPageWrapper::class)
			->setMethods(['getPageUriByPageId'])
			->getMock();
		$pWPPageWrapper->method('getPageUriByPageId')
			->with(13)
			->willReturn('test_parent/test-post');

		$this->_pContainer->set(DataDetailViewHandler::class, $pDataDetailViewHandler);
		$this->_pContainer->set(WPPageWrapper::class, $pWPPageWrapper);

		$pSubject = $this->_pContainer->get(RewriteRuleBuilder::class);
		$pSubject->addDynamicRewriteRules();
		$this->assertSame([
			'^(test_parent/test\-post)/([0-9]+)(-([^$]+)?)?/?$' =>
				'index.php?pagename=test_parent%2Ftest-post&view=$matches[1]&estate_id=$matches[2]'
		], $wp_rewrite->extra_rules_top);
	}

	public function testAddCustomRewriteTagsForAddressDetail()
	{
		global $wp_rewrite;
		$wp_rewrite->rewritecode = [];
		$wp_rewrite->rewritereplace = [];
		$pSubject = $this->_pContainer->get(RewriteRuleBuilder::class);
		$pSubject->addCustomRewriteTagsForAddressDetail();
		$this->assertSame('%address_id%', $wp_rewrite->rewritecode[0]);
		$this->assertSame('([^&]+)', $wp_rewrite->rewritereplace[0]);

		$this->assertSame('%view%', $wp_rewrite->rewritecode[1]);
		$this->assertSame('([^&]+)', $wp_rewrite->rewritereplace[1]);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testAddDynamicRewriteRulesForAddressDetail()
	{
		global $wp_rewrite;
		$wp_rewrite->extra_rules_top = [];

		$pDataAddressDetailViewHandler = $this->getMockBuilder(DataAddressDetailViewHandler::class)
			->disableOriginalConstructor()
			->setMethods(['getAddressDetailView'])
			->getMock();

		$pDataAdressDetailView = new DataAddressDetailView;
		$pDataAdressDetailView->addToPageIdsHaveDetailShortCode(13);
		$pDataAdressDetailView->setPageId(13);
		$pDataAddressDetailViewHandler->expects($this->once())
			->method('getAddressDetailView')
			->willReturn($pDataAdressDetailView);

		$pWPPageWrapper = $this->getMockBuilder(WPPageWrapper::class)
			->setMethods(['getPageUriByPageId'])
			->getMock();
		$pWPPageWrapper->method('getPageUriByPageId')
			->with(13)
			->willReturn('test_parent/test-post');

		$this->_pContainer->set(DataAddressDetailViewHandler::class, $pDataAddressDetailViewHandler);
		$this->_pContainer->set(WPPageWrapper::class, $pWPPageWrapper);

		$pSubject = $this->_pContainer->get(RewriteRuleBuilder::class);
		$pSubject->addDynamicRewriteRulesForAddressDetail();
		$this->assertSame([
			'^(test_parent/test\-post)/([0-9]+)(-([^$]+)?)?/?$' =>
				'index.php?pagename=test_parent%2Ftest-post&view=$matches[1]&address_id=$matches[2]'
		], $wp_rewrite->extra_rules_top);
	}
}