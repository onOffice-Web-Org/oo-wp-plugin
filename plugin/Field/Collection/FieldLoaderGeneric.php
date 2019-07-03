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

namespace onOffice\WPlugin\Field\Collection;

use Generator;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\SDKWrapper;

/**
 *
 */

class FieldLoaderGeneric
	implements FieldLoader
{
	/** @var SDKWrapper */
	private $_pSDKWrapper = null;


	/**
	 *
	 * @param SDKWrapper $pSDKWrapper
	 *
	 */

	public function __construct(SDKWrapper $pSDKWrapper)
	{
		$this->_pSDKWrapper = $pSDKWrapper;
	}


	/**
	 *
	 * @return Generator
	 *
	 */

	public function load(): Generator
	{
		$result = $this->sendRequest();

		foreach ($result as $moduleProperties) {
			$module = $moduleProperties['id'];
			$fieldArray = $moduleProperties['elements'];

			if (isset($fieldArray['label'])) {
				unset($fieldArray['label']);
			}

			foreach ($fieldArray as $fieldName => $fieldProperties) {
				$fieldProperties['module'] = $module;
				yield $fieldName => $fieldProperties;
			}
		}
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function sendRequest(): array
	{
		$parametersGetFieldList = [
			'labels' => true,
			'showContent' => true,
			'showTable' => true,
			'language' => Language::getDefault(),
			'modules' => [onOfficeSDK::MODULE_ADDRESS, onOfficeSDK::MODULE_ESTATE],
		];

		$pApiClientActionFields = new APIClientActionGeneric
			($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'fields');
		$pApiClientActionFields->setParameters($parametersGetFieldList);
		$pApiClientActionFields->addRequestToQueue()->sendRequests();

		return $pApiClientActionFields->getResultRecords();
	}
}
