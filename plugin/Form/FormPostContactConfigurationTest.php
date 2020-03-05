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
use onOffice\WPlugin\WP\WPWrapper;

/**
 *
 */

class FormPostContactConfigurationTest
	implements FormPostContactConfiguration
{
	/** @var SDKWrapper */
	private $_pSDKWrapper;

	/** @var bool */
	private $_newsletterAccepted = false;

	/** @var WPQueryWrapper */
	private $_pWPQueryWrapper;

	/** @var FormAddressCreator */
	private $_pFormAddressCreator;

	/** @var WPWrapper */
	private $_pWPWrapper;


	/**
	 *
	 * @param SDKWrapper $pSDKWrapper
	 * @param WPQueryWrapper $pWPQueryWrapper
	 * @param FormAddressCreator $pFormAddressCreator
	 * @param WPWrapper $pWPWrapper
	 *
	 */

	public function __construct(
		SDKWrapper $pSDKWrapper,
		WPQueryWrapper $pWPQueryWrapper,
		FormAddressCreator $pFormAddressCreator,
		WPWrapper $pWPWrapper)
	{
		$this->_pSDKWrapper = $pSDKWrapper;
		$this->_pWPQueryWrapper = $pWPQueryWrapper;
		$this->_pFormAddressCreator = $pFormAddressCreator;
		$this->_pWPWrapper = $pWPWrapper;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getReferrer(): string
	{
		return '/test/page';
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


	/**
	 * @return WPWrapper
	 */

	public function getWPWrapper(): WPWrapper
	{
		return $this->_pWPWrapper;
	}
}
