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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\WP\WPOptionWrapperBase;
use onOffice\WPlugin\WP\WPOptionWrapperDefault;


/**
 *
 */

class SDKWrapperAddCurlOption extends SDKWrapper
{
	/** @var onOfficeSDK */
	private $_pSDK = null;

	/** @var WPOptionWrapperDefault */
	private $_pWPOptionWrapper = null;


	/**
	 *
	 * @param onOfficeSDK $pSDK
	 * @param WPOptionWrapperBase $pWPOptionWrapper
	 *
	 */

	public function __construct(onOfficeSDK $pSDK = null,
		WPOptionWrapperBase $pWPOptionWrapper = null)
	{
		$this->_pSDK = $pSDK ?? new onOfficeSDK();
		$this->_pWPOptionWrapper = $pWPOptionWrapper ?? new WPOptionWrapperDefault();
		parent::__construct();
		$config = [
			'curl_options' => [
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
				CURLOPT_CONNECTTIMEOUT => 5,
			],
		];
		$this->_pSDK->setApiCurlOptions($config['curl_options']);
	}


}