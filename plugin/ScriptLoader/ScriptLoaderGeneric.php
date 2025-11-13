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

namespace onOffice\WPlugin\ScriptLoader;

use Generator;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_register_script;
use function wp_register_style;

/**
 *
 */

class ScriptLoaderGeneric
	implements ScriptLoader
{
	/** @var ScriptLoaderGenericConfiguration */
	private $_pConfiguration = null;


	/**
	 *
	 * @param ScriptLoaderGenericConfiguration $pConfiguration
	 *
	 */

	public function __construct(ScriptLoaderGenericConfiguration $pConfiguration)
	{
		$this->_pConfiguration = $pConfiguration;
	}


	/**
	 *
	 */

	public function enqueue()
	{
		/* @var $pIncludeModel IncludeFileModel */
		foreach ($this->getModelByType(IncludeFileModel::TYPE_SCRIPT) as $pIncludeModel) {
			if ($pIncludeModel->getLoadBeforeRenderingTemplate()){
                wp_enqueue_script($pIncludeModel->getIdentifier());
			}
		}
		foreach ($this->getModelByType(IncludeFileModel::TYPE_STYLE) as $pIncludeModel) {
			if ($pIncludeModel->getLoadBeforeRenderingTemplate()){
                wp_enqueue_style($pIncludeModel->getIdentifier());
			}
		}
	}


	/**
	 *
	 */

	public function register()
	{
		/* @var $pIncludeModel IncludeFileModel */
		foreach ($this->getModelByType(IncludeFileModel::TYPE_SCRIPT) as $pIncludeModel) {
			$version = $this->getFileVersion($pIncludeModel->getFilePath());
			wp_register_script($pIncludeModel->getIdentifier(), $pIncludeModel->getFilePath(),
				$pIncludeModel->getDependencies(), $version, array('strategy' => $pIncludeModel->getLoadAsynchronous(), 'in_footer' => $pIncludeModel->getLoadInFooter()));
			$this->_pConfiguration->localizeScript($pIncludeModel->getIdentifier());
		}
		foreach ($this->getModelByType(IncludeFileModel::TYPE_STYLE) as $pIncludeModel) {
			$version = $this->getFileVersion($pIncludeModel->getFilePath());
			wp_register_style($pIncludeModel->getIdentifier(), $pIncludeModel->getFilePath(),
				$pIncludeModel->getDependencies(), $version, $pIncludeModel->getLoadInFooter());
		}
	}

	/**
	 * Get file version based on modification time
	 *
	 * @param string $fileUrl
	 * @return string|bool
	 */
	private function getFileVersion(string $fileUrl)
	{
		$pluginUrl = plugin_dir_url(ONOFFICE_PLUGIN_DIR . '/index.php');
		$relativePath = str_replace($pluginUrl, '', $fileUrl);
		$localPath = ONOFFICE_PLUGIN_DIR . '/' . $relativePath;
		
		return file_exists($localPath) ? filemtime($localPath) : '1.0.0';
	}


	/**
	 *
	 * @param string $type
	 * @return Generator
	 *
	 */

	private function getModelByType(string $type): Generator
	{
		/* @var $pIncludeModel IncludeFileModel */
		foreach ($this->_pConfiguration->getScriptLoaderGenericConfiguration() as $pIncludeModel) {
			if ($pIncludeModel->getType() === $type) {
				yield $pIncludeModel;
			}
		}
	}
}
