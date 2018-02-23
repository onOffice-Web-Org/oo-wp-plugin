<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

namespace onOffice\WPlugin;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\SDKWrapper;

/**
 *
 */

class Impressum
{

	/** @var Impressum */
	static private $_pInstance = null;

	/** @var array */
	private $_data = array();


	/**
	 *
	 */

	private function __construct()
	{
		$language = Language::getDefault();

		$pSDKWrapper = new SDKWrapper();
		$requestParameters = array('language' => $language);
		$handle = $pSDKWrapper->addRequest(
				onOfficeSDK::ACTION_ID_READ, 'impressum', $requestParameters);

		$pSDKWrapper->sendRequests();
		$response = $pSDKWrapper->getRequestResponse( $handle );

		$this->_data = $response['data']['records'][0]['elements'];
	}


	/** @return array */
	public function getData()
	{ return $this->_data; }


	/**
	 *
	 * @param string $key
	 * @return string
	 *
	 */

	public function getDataByKey($key)
	{
		$returnValue = null;

		if (array_key_exists($key, $this->_data))
		{
			$returnValue = $this->_data[$key];
		}

		return $returnValue;
	}



	/**
	 *
	 * @return Impressum
	 *
	 */

	public static function getInstace()
	{
		if (self::$_pInstance === null)
		{
			self::$_pInstance = new Impressum();
		}

		return self::$_pInstance;
	}
}
