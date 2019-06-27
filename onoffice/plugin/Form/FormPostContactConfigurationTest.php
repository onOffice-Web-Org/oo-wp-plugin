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

class FormPostContactConfigurationTest
	implements FormPostContactConfiguration
{
	/** @var string */
	private $_referrer = '';

	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var bool */
	private $_newsletterAccepted = false;

	/** @var WPQueryWrapper */
	private $_pWPQueryWrapper = null;

	/** @var FormAddressCreator */
	private $_pFormAddressCreator = null;


	/**
	 *
	 * @param SDKWrapper $pSDKWrapper
	 * @param WPQueryWrapper $pWPQueryWrapper
	 * @param FormAddressCreator $pFormAddressCreator
	 *
	 */

	public function __construct(
		SDKWrapper $pSDKWrapper,
		WPQueryWrapper $pWPQueryWrapper,
		FormAddressCreator $pFormAddressCreator)
	{
		$this->_pSDKWrapper = $pSDKWrapper;
		$this->_pWPQueryWrapper = $pWPQueryWrapper;
		$this->_pFormAddressCreator = $pFormAddressCreator;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getReferrer(): string
	{
		return $this->_referrer;
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
	 * @param string $referrer
	 *
	 */

	public function setReferrer(string $referrer)
	{
		$this->_referrer = $referrer;
	}


	/**
	 *
	 * @return bool
	 *
	 */

	public function getNewsletterAccepted(): bool
	{
		return $this->_newsletterAccepted;
	}


	/**
	 *
	 * @param bool $newsletterAccepted
	 *
	 */

	public function setNewsletterAccepted(bool $newsletterAccepted)
	{
		$this->_newsletterAccepted = $newsletterAccepted;
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


	/**
	 *
	 * @return FormAddressCreator
	 *
	 */

	public function getFormAddressCreator(): FormAddressCreator
	{
		return $this->_pFormAddressCreator;
	}
}
