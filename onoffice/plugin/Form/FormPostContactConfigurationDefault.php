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

namespace onOffice\WPlugin\Form;

use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\WP\WPQueryWrapper;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class FormPostContactConfigurationDefault
	implements FormPostContactConfiguration
{
	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var WPQueryWrapper */
	private $_pWPQueryWrapper = null;


	/**
	 *
	 */

	public function __construct()
	{
		$this->_pSDKWrapper = new SDKWrapper();
		$this->_pWPQueryWrapper = new WPQueryWrapper();
	}


	/**
	 *
	 * @return SDKWrapper
	 *
	 */

	public function getSDKWrapper(): SDKWrapper
	{
		return $this->_pSDKWrapper;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getReferrer(): string
	{
		return filter_input(INPUT_SERVER, 'REQUEST_URI') ?? '';
	}


	/**
	 *
	 * @return bool
	 *
	 */

	public function getNewsletterAccepted(): bool
	{
		return filter_input(INPUT_POST, 'newsletter', FILTER_VALIDATE_BOOLEAN) ?? false;
	}


	/**
	 *
	 * @return WPQueryWrapper
	 *
	 */

	public function getWPQueryWrapper(): WPQueryWrapper
	{
		return $this->_pWPQueryWrapper;
	}
}
