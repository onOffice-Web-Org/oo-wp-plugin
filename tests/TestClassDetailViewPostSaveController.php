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

namespace onOffice\tests;

use onOffice\WPlugin\Controller\RewriteRuleBuilder;
use onOffice\WPlugin\Record\RecordManagerPostMeta;
use DI\ContainerBuilder;
use onOffice\WPlugin\Controller\DetailViewPostSaveController;
use WP_UnitTestCase;
use wpdb;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassDetailViewPostSaveController
	extends WP_UnitTestCase
{
	/**
	 * @var object
	 */
	private $_pRecordManagerPostMeta;
	
	/** @var wpdb */
	private $_pWPDB = null;
	
	public function testConstruct()
	{
		$pDIContainerBuilder = new ContainerBuilder;
		$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pDIContainerBuilder->build();
		$pDetailViewPostSaveController = $pContainer->get(DetailViewPostSaveController::class);
		$this->assertInstanceOf(RecordManagerPostMeta::class, $pDetailViewPostSaveController->getRecordPostMeta());
		$this->assertInstanceOf(RewriteRuleBuilder::class, $pDetailViewPostSaveController->getRewriteRuleBuilder());
	}
}
