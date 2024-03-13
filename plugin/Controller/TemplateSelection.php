<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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

namespace onOffice\WPlugin\Controller;

use onOffice\WPlugin\Template\TemplateCall;
use onOffice\WPlugin\Record\RecordManagerFactory;
use onOffice\WPlugin\Record\RecordManagerUpdateTemplateColumn;

class TemplateSelection
{
	/** @var string */
	private $_type = '';

	/** @var string */
	private $_folderName = '';

	/** @var string */
	private $_content = '';

	/** @var RecordManagerUpdateTemplateColumn */
	private $_pRecordManagerUpdateTemplateColumn = null;

	/**
	 * @param RecordManagerUpdateTemplateColumn $pRecordManagerUpdateTemplateColumn
	 */
	public function __construct(RecordManagerUpdateTemplateColumn $pRecordManagerUpdateTemplateColumn = null)
	{
		$this->_pRecordManagerUpdateTemplateColumn = $pRecordManagerUpdateTemplateColumn ?? new RecordManagerUpdateTemplateColumn();
	}

	/**
	 *
	 */
	public function processWPPageTemplatesFile(): void
	{
		$templateTypes = [RecordManagerFactory::TYPE_ADDRESS, RecordManagerFactory::TYPE_ESTATE, RecordManagerFactory::TYPE_FORM];

		foreach ($templateTypes as $type) {
			$this->_type = $type;
			$templateInformation = $this->readTemplatePaths($this->_type);
			foreach ($templateInformation as $info) {
				$this->_folderName = $info['folder'];
				$this->processPageTemplateFiles($info);
			}
		}
	}

	/**
	 * @param array $info
	 */
	private function processPageTemplateFiles(array $info): void
	{
		foreach ($info['path'] as $path => $fileName) {
			$fullPath = $this->getFullTemplatePath($path, $this->_type);
			if (!is_file($fullPath) || !is_readable($fullPath)) {
				continue;
			}
			$this->_content = file_get_contents($fullPath);
			$templateNameFromFile = basename($fileName, ".php");
			if (!preg_match('|Template Name:\s*(.*)$|mi', $this->_content, $header)) {
				$this->addTemplateNameToTopPHPComment($fullPath, $templateNameFromFile);
				continue;
			}
			$this->updateTemplateName($fullPath, $header[1], $templateNameFromFile, $fileName);
		}
	}

	/**
	 * @param string $fullPath
	 * @param string $templateName
	 * @param string $templateNameFromFile
	 * @param string $fileName
	 */
	private function updateTemplateName(string $fullPath, string $templateName, string $templateNameFromFile, string $fileName): void
	{
		if ($templateName !== $templateNameFromFile) {
			$this->renameTemplate($fileName, $templateName, $fullPath);
		}
	}

	/**
	 * @param string $fileName
	 * @param string $templateName
	 * @param string $fullPath
	 */
	private function renameTemplate(string $fileName, string $templateName, string $fullPath): void
	{
		$templateNameFile = $this->_folderName . '/' . $templateName . '.php';
		$templateChangeNameFile = $this->_folderName . '/' . $fileName;

		$this->_pRecordManagerUpdateTemplateColumn->setTemplateName($templateNameFile);
		$this->_pRecordManagerUpdateTemplateColumn->setTemplateChangeName($templateChangeNameFile);
		$this->_pRecordManagerUpdateTemplateColumn->setMainTable($this->_type);

		$pDataDetailViewOptions = get_option('onoffice-default-view');
		if (!empty($pDataDetailViewOptions) && get_option('onoffice-default-view')->getTemplate() === $templateNameFile) {
			$pDataDetailViewOptions->setTemplate($templateChangeNameFile);
			update_option('onoffice-default-view', $pDataDetailViewOptions);
		}

		$pDataViewSimilarEstates = get_option('onoffice-similar-estates-settings-view');
		if (!empty($pDataViewSimilarEstates) && get_option('onoffice-similar-estates-settings-view')->getDataViewSimilarEstates()->getTemplate() === $templateNameFile) {
			$pDataViewSimilarEstates->getDataViewSimilarEstates()->setTemplate($templateChangeNameFile);
			update_option('onoffice-similar-estates-settings-view', $pDataViewSimilarEstates);
		}

		if ($this->_pRecordManagerUpdateTemplateColumn->updateDataTemplateColumn()) {
			$newContent = str_replace('Template Name: ' . $templateName, 'Template Name: ' . basename($fileName, ".php"), $this->_content);
			file_put_contents($fullPath, $newContent);
		}
	}

	/**
	 * @param string $fullPath
	 * @param string $templateName
	 */
	private function addTemplateNameToTopPHPComment(string $fullPath, string $templateName): void
	{
		$newContent = "<?php\n/**\n * Template Name: $templateName\n * Template Default: $templateName\n */\n?>\n" . $this->_content;
		file_put_contents($fullPath, $newContent);
	}

	/**
	* @param string $directory
	* @return array
	*/
	private function readTemplatePaths(string $directory): array 
	{
		$templatesAll = $this->getTemplates($directory);

		return (new TemplateCall())->formatTemplatesData(array_filter($templatesAll), $directory);
	}

	/**
	 * @param string $relativePath
	 * @param string $directory
	 * @return string
	 */
	private function getFullTemplatePath(string $relativePath, string $directory): string 
	{
		foreach ($this->getTemplates($directory) as $paths) {
			foreach ($paths as $fullPath) {
				if (strpos($fullPath, $relativePath) !== false) {
					return $fullPath;
				}
			}
		}

		return "";
	}

	/**
	 * @param string $directory
	 * @return array
	 */
	private function getTemplates(string $directory, string $pattern = '*'): array 
	{
		$templatesAll[ TemplateCall::TEMPLATE_FOLDER_PLUGIN ] = glob( plugin_dir_path( ONOFFICE_PLUGIN_DIR )
										. 'onoffice-personalized/templates/' . $directory . '/' . $pattern . '.php' );
		$templatesAll[ TemplateCall::TEMPLATE_FOLDER_THEME ] = glob( get_stylesheet_directory()
										. '/onoffice-theme/templates/' . $directory . '/' . $pattern . '.php' );

		return $templatesAll;
	}

	/**
	 * @param string $relativePath
	 * @param string $directory
	 * @return string
	 */
	public function getTemplateDefault(string $relativePath, string $directory): string 
	{
		$fullPath = $this->getFullTemplatePath($relativePath, $directory);
		if (!is_file($fullPath) || !is_readable($fullPath) || empty($fullPath)) {
			return '';
		}
		$templateContent = file_get_contents($fullPath);
		preg_match('/\* Template Default: (.*)/', $templateContent, $matches);

		return isset($matches[1]) ? $matches[1] . '.php' : '';
	}
}
