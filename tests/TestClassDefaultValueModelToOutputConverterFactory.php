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

use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\Field\DefaultValue\ModelToOutputConverter\DefaultValueModelToOutputConverterFactory;
use onOffice\WPlugin\Field\DefaultValue\ModelToOutputConverter\DefaultValueModelToOutputConverterSingleselect;
use onOffice\WPlugin\Field\DefaultValue\ModelToOutputConverter\DefaultValueModelToOutputConverterText;

class TestClassDefaultValueModelToOutputConverterFactory extends \WP_UnitTestCase
{
	/** @var DefaultValueModelToOutputConverterFactory */
	private $_pSubject = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$pDIContainerBuilder = new ContainerBuilder();
		$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pDIContainerBuilder->build();
		$this->_pSubject = $pContainer->get(DefaultValueModelToOutputConverterFactory::class);
	}


	/**
	 *
	 * @throws DependencyException
	 * @throws NotFoundException
	 *
	 */

	public function testCreateForText()
	{
		$pConverter = $this->_pSubject->createForText();
		$this->assertInstanceOf(DefaultValueModelToOutputConverterText::class, $pConverter);
	}

	/**
	 *
	 * @throws DependencyException
	 * @throws NotFoundException
	 *
	 */

	public function testCreateForSingleSelect()
	{
		$pConverter = $this->_pSubject->createForSingleSelect();
		$this->assertInstanceOf(DefaultValueModelToOutputConverterSingleselect::class, $pConverter);
	}
}
