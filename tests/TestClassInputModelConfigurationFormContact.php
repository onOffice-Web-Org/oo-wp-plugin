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

use onOffice\WPlugin\Model\InputModel\InputModelConfiguration;
use onOffice\WPlugin\Model\InputModel\InputModelConfigurationFormContact;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigForm;
use ReflectionClass;
use WP_UnitTestCase;


/**
 *
 */

class TestClassInputModelConfigurationFormContact
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGetConfig()
	{
		$pInputModelConfigurationFormContact = new InputModelConfigurationFormContact();
		$config = $pInputModelConfigurationFormContact->getConfig();
		$this->assertInstanceOf(InputModelConfiguration::class, $pInputModelConfigurationFormContact);
		$this->assertIsArray($config);

		$pReflection = new ReflectionClass(InputModelDBFactoryConfigForm::class);
		$factoryConfigContants = $pReflection->getConstants();

		foreach ($config as $field => $fieldConfig) {
			$this->assertContains($field, $factoryConfigContants);
			$this->assertArrayHasKey(InputModelConfiguration::KEY_HTMLTYPE, $fieldConfig);
			$this->assertArrayHasKey(InputModelConfiguration::KEY_LABEL, $fieldConfig);
		}
	}
}
