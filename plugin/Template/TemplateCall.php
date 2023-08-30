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

	public function getTemplatesFolderInfo()
	{
		return [
			'theme' => [
				'title' => __('Personalized (Theme)', 'onoffice-for-wp-websites'),
				'folder' => 'onoffice-theme/templates/',
				'order' => 1
			],
			'plugin' => [
				'title' => __('Personalized (Plugin)', 'onoffice-for-wp-websites'),
				'folder' => 'onoffice-personalized/templates/',
				'order' => 2
			],
			'included' => [
				'title' => __('Included', 'onoffice-for-wp-websites'),
				'folder' => '/templates.dist/',
				'order' => 3
			],
		];
	}
	
	const TEMPLATE_FOLDER_INCLUDED = 'included';

	const TEMPLATE_FOLDER_PLUGIN = 'plugin';

	const TEMPLATE_FOLDER_THEME = 'theme';

	const TEMPLATE_FOLDER_PARENT_THEME = 'parent_theme';

	/** @var array */
	private $_templates = [];

	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var string */
	private $_templateType = '';

	const ORDER_OF_TEMPLATES_FOLDER = [
		"Personalized (Theme)" => 1,
		"Personalized (Plugin)" => 2,
		"Default" => 3,
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
	 * @param $templatesAll
	 * @param $directory
	 *
	 * @return array
	 */

	public function formatTemplatesData( $templatesAll, $directory ): array {
		$templateFormatResult = [];
		$pluginDirName = basename(ONOFFICE_PLUGIN_DIR);
		
		foreach ( $templatesAll as $key => $templatesFolder ) {
			$templateInfo           = self::getTemplatesFolderInfo()[$key];
			$templateInfo['folder'] .= $directory;
			if ( $key === self::TEMPLATE_FOLDER_INCLUDED ) {
				$templateInfo['folder'] = basename( plugin_dir_path( ONOFFICE_PLUGIN_DIR . '/index.php' ) ) . $templateInfo['folder'];
			} elseif ( array_key_exists( 'included', $templatesAll ) && count( $templatesAll ) === 2 ) {
				// check and update Personalized templates title
				$templateInfo['title'] = strtok( $templateInfo['title'], " " );
			}
			foreach ( $templatesFolder as $path ) {
				if ( $key === self::TEMPLATE_FOLDER_THEME ) {
					$formattedPath = __String::getNew( $path )->replace( get_stylesheet_directory() . '/', '' );
				} else if ($key === self::TEMPLATE_FOLDER_PLUGIN) {
					$formattedPath = __String::getNew( $path )->replace( plugin_dir_path( ONOFFICE_PLUGIN_DIR ), '' );
				} else {
					$formattedPath = substr( $path, strpos( $path, $pluginDirName ) );
				}
				$templateInfo['path'][ $formattedPath ] = substr( strrchr( $path, "/" ), 1 );
			}
			$templateFormatResult[ $templateInfo['order'] ] = $templateInfo;
		}
		ksort( $templateFormatResult );

		return $templateFormatResult;
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
