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

namespace onOffice\tests;

use RuntimeException;
use onOffice\WPlugin\Controller\TemplateSelection;
use onOffice\WPlugin\Template\TemplateCall;
use onOffice\WPlugin\Record\RecordManagerUpdateTemplateColumn;

class TestClassTemplateSelection extends \WP_UnitTestCase
{
	/**
	 * @param string $templatePath
	 */
	private function prepareTemplateEnvironment(string $templatePath)
	{
		if (!is_dir($templatePath . 'templates/')) {
			if (!mkdir($templatePath . 'templates/', 755, true)) {
				throw new RuntimeException(sprintf('Directory "%s" was not created', $templatePath . 'templates/'));
			}
		}
		if (!is_dir($templatePath . 'templates/estate/')) {
			if (!mkdir($templatePath . 'templates/estate/', 755, true)) {
				throw new RuntimeException(sprintf('Directory "%s" was not created', $templatePath . 'templates/estate/'));
			}
		}

		copy(__DIR__.'/resources/templates/default_detail.php', $templatePath.'/templates/estate/default_detail.php');
	}

	/**
	 *
	 */
	public function testProcessWPPageTemplatesFile()
	{
		$pRecordManagerUpdateTemplateColumn = new RecordManagerUpdateTemplateColumn();
		$templatePath = ABSPATH.'/wp-content/plugins/onoffice-personalized/';
		$this->prepareTemplateEnvironment($templatePath);

		$templatesAll[TemplateCall::TEMPLATE_FOLDER_PLUGIN] = [
			$templatePath . "templates/estate/default_detail.php"
		];

		$pInstance = $this->getMockBuilder(TemplateSelection::class)
			->setMethods(['getTemplates'])
			->getMock();
		$pInstance->method('getTemplates')->willReturn($templatesAll);
		$pInstance->processWPPageTemplatesFile($pRecordManagerUpdateTemplateColumn);

		rename($templatePath.'/templates/estate/default_detail.php', $templatePath.'/templates/estate/default_detail_1.php');

		$templatesAll[TemplateCall::TEMPLATE_FOLDER_PLUGIN] = [
			$templatePath . "templates/estate/default_detail_1.php"
		];

		$pInstance = $this->getMockBuilder(TemplateSelection::class)
			->setMethods(['getTemplates'])
			->getMock();
		$pInstance->method('getTemplates')->willReturn($templatesAll);
		$pInstance->processWPPageTemplatesFile($pRecordManagerUpdateTemplateColumn);

		$templateContent = file_get_contents($templatePath.'/templates/estate/default_detail_1.php');
		preg_match('|Template Name:\s*(.*)$|mi', $templateContent, $header);
		$defaultNameOfTemplate = $pInstance->getDefaultNameOfTemplate('/templates/estate/default_detail_1.php', 'estate');

		$this->assertEquals($defaultNameOfTemplate, 'default_detail.php');
		$this->assertEquals($header[1], 'default_detail_1');
	}
}