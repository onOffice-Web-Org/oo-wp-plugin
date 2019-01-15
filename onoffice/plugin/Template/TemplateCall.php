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
