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

namespace onOffice\WPlugin\Template;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Utility\__String;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) Software GmbH
 *
 */
class TemplateCall
{
	/** */
	const TEMPLATE_TYPE_EXPOSE = 'pdf';

	/** */
	const TEMPLATE_TYPE_MAIL = 'mail';


	/** @var array */
	private $_templates = [];

	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var string */
	private $_templateType = '';

	const ORDER_OF_TEMPLATES_FOLDER = [
		"Personalized (Theme)" => 1,
		"Personalized (Plugin)" => 2,
		"Included" => 3,
	];


	/**
	 *
	 * @param string $templateType
	 * @param SDKWrapper $pSDKWrapper
	 *
	 */

	public function __construct(
		string $templateType = self::TEMPLATE_TYPE_EXPOSE,
		SDKWrapper $pSDKWrapper = null)
	{
		$this->_pSDKWrapper = $pSDKWrapper ?? new SDKWrapper();
		$this->_templateType = $templateType;
	}


	/**
	 *
	 */

	public function loadTemplates()
	{
		$pApiClientAction = new APIClientActionGeneric
			($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'templates');
		$pApiClientAction->setParameters(['type' => $this->_templateType]);
		$pApiClientAction->addRequestToQueue()->sendRequests();

		foreach ($pApiClientAction->getResultRecords() as $template) {
			$elements = $template['elements'];
			$this->_templates[$elements['identifier']] = $elements['title'];
		}
	}


	/**
	 *
	 */

	public function readTemplates($templatesAll, $directory): array
	{
		$templateFolderData = array();

		$plugin_name = basename(plugin_dir_path(ONOFFICE_PLUGIN_DIR . '/index.php'));
		foreach ($templatesAll as $filePath) {
			$fileName = substr(strrchr($filePath, "/"), 1);
			if (strpos($filePath, 'themes') !== false) {
				$filePath = __String::getNew($filePath)->replace(get_template_directory() . '/', '');
				$templateTitle = 'Personalized (Theme)';
				$shortPath = '/onoffice-theme/templates/' . $directory . '/';
			} else {
				$filePath = __String::getNew($filePath)->replace(plugin_dir_path(ONOFFICE_PLUGIN_DIR), '');
				if (strpos($filePath, 'onoffice-personalized') !== false) {
					$templateTitle = 'Personalized (Plugin)';
					$shortPath = 'onoffice-personalized/templates/' . $directory . '/';
				} else {
					$templateTitle = 'Included';
					$shortPath = $plugin_name . '/' . 'templates.dist/' . $directory . '/';
				}
			}
			$folderOrder = self::ORDER_OF_TEMPLATES_FOLDER[$templateTitle];
			$templatePathGroupByFolder[$templateTitle][$filePath] = $fileName;

			$templateFolderData[$folderOrder]['path'] = $templatePathGroupByFolder[$templateTitle];
			$templateFolderData[$folderOrder]['title'] = $templateTitle;
			$templateFolderData[$folderOrder]['folder'] = $shortPath;
		}

		ksort($templateFolderData);
		return $templateFolderData;
	}

	/** @return array */
	public function getTemplates(): array
		{ return $this->_templates; }

	/** @return string */
	public function getTemplateType(): string
		{ return $this->_templateType; }

	/** @return SDKWrapper */
	public function getSDKWrapper(): SDKWrapper
		{ return $this->_pSDKWrapper; }
}
