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

use onOffice\WPlugin\ScriptLoader\IncludeFileModel;
use onOffice\WPlugin\ScriptLoader\ScriptLoaderGenericConfigurationDefault;
use WP_UnitTestCase;

/**
 *
 */

class TestClassScriptLoaderGenericConfigurationDefault
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGetScriptLoaderGenericConfiguration()
	{
		add_option('onoffice-favorization-enableFav', true);

		$pScriptLoaderGenericConfigurationDefault = new ScriptLoaderGenericConfigurationDefault();
		$config = $pScriptLoaderGenericConfigurationDefault->getScriptLoaderGenericConfiguration();
		$this->assertCount(12, $config);
		/* @var $pFileModel IncludeFileModel */
		foreach ($config as $pFileModel) {
			$this->assertInstanceOf(IncludeFileModel::class, $pFileModel);
			$this->assertThat($pFileModel->getFilePath(), $this->logicalOr
				($this->stringStartsWith('https://'),
					$this->stringStartsWith('http://example.org/wp-content/plugins/')));
			$this->assertThat($pFileModel->getFilePath(), $this->logicalOr
				($this->stringEndsWith('.js'),
					$this->stringEndsWith('.css')));
			$this->assertNotEmpty($pFileModel->getIdentifier());
			$this->assertNotEmpty($pFileModel->getType());
		}
	}
}
