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

use DI\ContainerBuilder;
use onOffice\WPlugin\Controller\RewriteRuleBuilder;

/**
 *
 * @preserveGlobalState disabled
 *
 */

class TestClassRewriteRuleBuilder
	extends \WP_UnitTestCase
{
	/** @var RewriteRuleBuilder */
	private $_pSubject;

	/**
	 * @before
	 */
	public function prepare()
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();
		$this->_pSubject = $pContainer->get(RewriteRuleBuilder::class);
	}

	/**
	 * @preserveGlobalState disabled
	 * @runInSeparateProcess
	 */
	public function testAddCustomRewriteTags()
	{
		global $wp_rewrite;
		$wp_rewrite->rewritecode = [];
		$wp_rewrite->rewritereplace = [];
		$this->_pSubject->addCustomRewriteTags();
		$this->assertSame('%estate_id%', $wp_rewrite->rewritecode[0]);
		$this->assertSame('([^&]+)', $wp_rewrite->rewritereplace[0]);

		$this->assertSame('%view%', $wp_rewrite->rewritecode[1]);
		$this->assertSame('([^&]+)', $wp_rewrite->rewritereplace[1]);
	}
}