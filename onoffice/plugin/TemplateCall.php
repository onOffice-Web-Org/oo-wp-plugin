<?php

/**
 *
 *    Copyright (C) 2016 onOffice Software AG
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


namespace onOffice\WPlugin;

use onOffice\WPlugin\SDKWrapper;
use onOffice\SDK\onOfficeSDK;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software GmbH
 *
 */
class TemplateCall
{
	/** */
	const TEMPLATE_TYPE_EXPOSE = 'pdf';

	/** */
	const TEMPLATE_TYPE_MAIL = 'mail';


	/** @var array */
	private $_templates = array();


	/**
	 *
	 */

	public function __construct($templateType)
	{
		if ($templateType == null)
		{
			$templateType = self::TEMPLATE_TYPE_EXPOSE;
		}

		$pSDKWrapper = new SDKWrapper();
		$requestParameter = array
			(
				'type' => $templateType,
			);

		$handle = $pSDKWrapper->addRequest(
			onOfficeSDK::ACTION_ID_GET, 'templates', $requestParameter);
		$pSDKWrapper->sendRequests();

		$response = $pSDKWrapper->getRequestResponse( $handle );

		$this->extractResponse($response);
	}


	/**
	 *
	 * @param array $response
	 *
	 */

	private function extractResponse($response)
	{
		$templates = $response['data']['records'];

		foreach ($templates as $template)
		{
			$elements = $template['elements'];
			$this->_templates[$elements['identifier']] = $elements['title'];
		}
	}


	/** @return array */
	public function getTemplates()
		{ return $this->_templates; }



	/**
	 *
	 * @param string $identifier
	 * @return string
	 *
	 */

	public function getTemplateByKey($identifier)
	{
		$returnValue = null;

		if (array_key_exists($identifier, $this->_templates))
		{
			$returnValue = $this->_templates[$identifier];
		}

		return $returnValue;
	}
}
