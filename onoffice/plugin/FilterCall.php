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

class FilterCall
{
	/** @var array */
	private $_filters = array();


	/**
	 *
	 * @param string $module
	 *
	 */

	public function __construct($module)
	{
		$pSDKWrapper = new SDKWrapper();
		$requestParameter = array
			(
				'module' => $module,
			);

		$handle = $pSDKWrapper->addRequest(
			onOfficeSDK::ACTION_ID_GET, 'filters', $requestParameter);
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
		$filters = $response['data']['records'];

		foreach ($filters as $filter)
		{
			$elements = $filter['elements'];
			$this->_filters[$filter['id']] = $elements['name'];
		}
	}



	/**
	 *
	 * @return array
	 *
	 */

	public function getFilters()
	{
		return $this->_filters;
	}


	/**
	 *
	 * @param type $id
	 * @return boolean
	 *
	 */

	public function getFilternameById($id)
	{
		if (array_key_exists($id, $this->_filters))
		{
			return $this->_filters[$id];
		}

		return null;
	}
}
