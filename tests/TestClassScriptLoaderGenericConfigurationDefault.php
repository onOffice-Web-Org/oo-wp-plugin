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

use DI\Container;
use DI\ContainerBuilder;
use onOffice\WPlugin\ScriptLoader\IncludeFileModel;
use onOffice\WPlugin\ScriptLoader\ScriptLoaderGenericConfigurationDefault;
use onOffice\WPlugin\Template;
use onOffice\WPlugin\Template\TemplateCall;
use WP_UnitTestCase;

/**
 *
 */

class TestClassScriptLoaderGenericConfigurationDefault
	extends WP_UnitTestCase
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
		$pTemplate = new TemplateMocker(__DIR__.'/resources/templates');
		$this->_pContainer->set(Template::class, $pTemplate);
	}
	/**
	 *
	 */

	public function testGetScriptLoaderGenericConfiguration()
	{
		add_option('onoffice-favorization-enableFav', true);

		$pScriptLoaderGenericConfigurationDefault = new ScriptLoaderGenericConfigurationDefault();
		$config = $pScriptLoaderGenericConfigurationDefault->getScriptLoaderGenericConfiguration();
		$this->assertCount(9, $config);
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

	/**
	 * @covers onOffice\WPlugin\ScriptLoader\ScriptLoaderGenericConfigurationDefault::getStyleUriByVersion
	 */
	public function testGetOnOfficeStyleVersion()
	{
		$pluginPath = ONOFFICE_PLUGIN_DIR.'/index.php';
		$pScriptLoaderGenericConfigurationDefault = new ScriptLoaderGenericConfigurationDefault();
		$onofficeCssStyleFilePath = $pScriptLoaderGenericConfigurationDefault->getStyleUriByVersion('onoffice_defaultview');
		$this->assertEquals($onofficeCssStyleFilePath, plugins_url('css/onoffice_defaultview.css', $pluginPath));
		$this->assertNotEmpty($onofficeCssStyleFilePath);
	}

	/**
	 * @covers onOffice\WPlugin\ScriptLoader\ScriptLoaderGenericConfigurationDefault::getOnOfficeStyleVersion
	 */
	public function testGetStyleUriByVersion()
	{
		$pScriptLoaderGenericConfigurationDefault = new ScriptLoaderGenericConfigurationDefault();
		$onofficeCssStyleVersion = $pScriptLoaderGenericConfigurationDefault->getOnOfficeStyleVersion();
		$this->assertEquals($onofficeCssStyleVersion, 'onoffice_style');
		$this->assertNotEmpty($onofficeCssStyleVersion);
	}
}
