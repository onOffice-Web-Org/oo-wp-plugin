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

class TemplateSelection
{
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
				return '';
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
	private function getTemplates(string $directory, string $pattern = '*'): array 
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
	public function getLabelOfTemplate(string $partPath, string $directory): string 
	{
		$fullPath = $this->getFullTemplatePath($partPath, $directory);
		if (!is_file($fullPath) || !is_readable($fullPath) || empty($fullPath)) {
			return '';
		}
		$templateFileContent = file_get_contents($fullPath);
		preg_match('|Template Name:\s*(.*)$|mi', $templateFileContent, $matches);

		return isset($matches[1]) ? $matches[1] : '';
	}
}
