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
	private $_templateType = '';

	/** @var string */
	private $_folderName = '';

	/** @var string */
	private $_templateFileContent = '';

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
	public function processWPPageTemplatesFile()
	{
		$templateTypes = [RecordManagerFactory::TYPE_ADDRESS, RecordManagerFactory::TYPE_ESTATE, RecordManagerFactory::TYPE_FORM];

		foreach ($templateTypes as $templateType) {
			$this->_templateType = $templateType;
			$templateInformation = $this->readTemplatePaths($this->_templateType);
			if (empty($templateInformation)) {
				continue;
			}
			foreach ($templateInformation as $info) {
				$this->_folderName = $info['folder'];
				$this->processPageTemplateFiles($info);
			}
		}
	}

	/**
	 * @param array $info
	 */
	private function processPageTemplateFiles(array $info)
	{
		foreach ($info['path'] as $path => $fileName) {
			$fullPath = $this->getFullTemplatePath($path, $this->_templateType);
			if (!is_file($fullPath) || !is_readable($fullPath) || !is_writable($fullPath)) {
				continue;
			}
			$this->_templateFileContent = file_get_contents($fullPath);
			$fileNameHaveNoExtension = basename($fileName, ".php");
			if (!preg_match('|Template Name:\s*(.*)$|mi', $this->_templateFileContent, $header)) {
				$this->addStatesTemplateNameToTopPHPComment($fullPath, $fileNameHaveNoExtension);
				continue;
			}
			$this->updateTemplateName($fullPath, $header[1], $fileNameHaveNoExtension, $fileName);
		}
	}

	/**
	 * @param string $fullPath
	 * @param string $templateName
	 * @param string $fileNameHaveNoExtension
	 * @param string $fileName
	 */
	private function updateTemplateName(string $fullPath, string $templateName, string $fileNameHaveNoExtension, string $fileName)
	{
		if ($templateName !== $fileNameHaveNoExtension) {
			$this->updateName($fileName, $templateName, $fullPath);
		}
	}

	/**
	 * @param string $fileName
	 * @param string $templateName
	 * @param string $fullPath
	 */
	private function updateName(string $fileName, string $templateName, string $fullPath)
	{
		$templateUrl = $this->_folderName . '/' . $templateName . '.php';
		$customTemplateUrl = $this->_folderName . '/' . $fileName;

		$this->_pRecordManagerUpdateTemplateColumn->setTemplateUrl($templateUrl);
		$this->_pRecordManagerUpdateTemplateColumn->setCustomTemplateUrl($customTemplateUrl);
		$this->_pRecordManagerUpdateTemplateColumn->setMainTable($this->_templateType);

		$this->updateTemplateConfigInDetailViewOption($templateUrl, $customTemplateUrl);
		$this->updateTemplateConfigInSimilarEstatesViewOption($templateUrl, $customTemplateUrl);

		if ($this->_pRecordManagerUpdateTemplateColumn->updateDataTemplateColumn()) {
			$newTemplateFileContent = str_replace('Template Name: ' . $templateName, 'Template Name: ' . basename($fileName, ".php"), $this->_templateFileContent);
			file_put_contents($fullPath, $newTemplateFileContent);
		}
	}

	/**
	 * @param string $templateUrl
	 * @param string $customTemplateUrl
	 */
	private function updateTemplateConfigInDetailViewOption(string $templateUrl, string $customTemplateUrl)
	{
		$pDataDetailViewOptions = get_option('onoffice-default-view');
		if (!empty($pDataDetailViewOptions) && $pDataDetailViewOptions->getTemplate() === $templateUrl) {
			$pDataDetailViewOptions->setTemplate($customTemplateUrl);
			update_option('onoffice-default-view', $pDataDetailViewOptions);
		}
	}

	/**
	 * @param string $templateUrl
	 * @param string $customTemplateUrl
	 */
	private function updateTemplateConfigInSimilarEstatesViewOption(string $templateUrl, string $customTemplateUrl)
	{
		$pDataViewSimilarEstates = get_option('onoffice-similar-estates-settings-view');
		if (!empty($pDataViewSimilarEstates) && $pDataViewSimilarEstates->getDataViewSimilarEstates()->getTemplate() === $templateUrl) {
			$pDataViewSimilarEstates->getDataViewSimilarEstates()->setTemplate($customTemplateUrl);
			update_option('onoffice-similar-estates-settings-view', $pDataViewSimilarEstates);
		}
	}

	/**
	 * @param string $fullPath
	 * @param string $templateName
	 */
	private function addStatesTemplateNameToTopPHPComment(string $fullPath, string $templateName)
	{
		$templateComment = "\n/**\n * Template Name: $templateName\n * Template Default: $templateName\n */\n";
		$this->_templateFileContent = preg_replace("/^<\?php/", "<?php" . $templateComment, $this->_templateFileContent, 1);

		file_put_contents($fullPath, $this->_templateFileContent);
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
	 * @param string $partPath
	 * @param string $directory
	 * @return string
	 */
	private function getFullTemplatePath(string $partPath, string $directory): string 
	{
		$templatePaths = $this->getTemplates($directory);

		foreach ($templatePaths as $templatePath) {
			if (empty($templatePath)) {
				continue;
			}
			foreach ($templatePath as $fullPath) {
				if (strpos($fullPath, $partPath) !== false) {
					return $fullPath;
				}
			}
		}

		return '';
	}

	/**
	 * @param string $directory
	 * @return array
	 */
	public function getTemplates(string $directory, string $pattern = '*'): array 
	{
		$templatesAll[ TemplateCall::TEMPLATE_FOLDER_PLUGIN ] = glob( plugin_dir_path( ONOFFICE_PLUGIN_DIR )
										. 'onoffice-personalized/templates/' . $directory . '/' . $pattern . '.php' );
		$templatesAll[ TemplateCall::TEMPLATE_FOLDER_THEME ] = glob( get_stylesheet_directory()
										. '/onoffice-theme/templates/' . $directory . '/' . $pattern . '.php' );

		return $templatesAll;
	}

	/**
	 * @param string $partPath
	 * @param string $directory
	 * @return string
	 */
	public function getDefaultNameOfTemplate(string $partPath, string $directory): string 
	{
		$fullPath = $this->getFullTemplatePath($partPath, $directory);
		if (!is_file($fullPath) || !is_readable($fullPath) || empty($fullPath)) {
			return '';
		}
		$templateContent = file_get_contents($fullPath);
		preg_match('/\* Template Default: (.*)/', $templateContent, $matches);

		return isset($matches[1]) ? $matches[1] . '.php' : '';
	}
}
