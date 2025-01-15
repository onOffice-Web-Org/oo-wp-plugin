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

class FormPostContactConfigurationDefault
	implements FormPostContactConfiguration
{
	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var WPQueryWrapper */
	private $_pWPQueryWrapper = null;

	/** @var FormAddressCreator */
	private $_pFormAddressCreator = null;

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
	 * @return bool
	 */
	public function getNewsletterAccepted(): bool
	{
		return filter_var($_POST['newsletter'] ?? null, FILTER_SANITIZE_FULL_SPECIAL_CHARS) === 'y';
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
