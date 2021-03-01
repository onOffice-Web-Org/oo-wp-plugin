<?php

/**
 *
 *    Copyright (C) 2021 onOffice GmbH
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
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\Field\CustomLabel\ModelToOutputConverter\CustomLabelModelToOutputConverterFactory;
use onOffice\WPlugin\Field\CustomLabel\ModelToOutputConverter\CustomLabelModelToOutputConverterField;
use WP_UnitTestCase;

class TestClassCustomLabelModelToOutputConverterFactory
	extends WP_UnitTestCase
{

	/** @var CustomLabelModelToOutputConverterFactory */
	private $_pSubject = null;


	/**
	 * @before
	 */
	public function prepare()
	{
		$pDIContainerBuilder = new ContainerBuilder();
		$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pDIContainerBuilder->build();
		$this->_pSubject = $pContainer->get(CustomLabelModelToOutputConverterFactory::class);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function testCreateForField()
	{
		$pConverter = $this->_pSubject->createForField();
		$this->assertInstanceOf(CustomLabelModelToOutputConverterField::class, $pConverter);
	}
}
