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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\InputVariableReader;
use onOffice\WPlugin\Controller\InputVariableReaderConfigTest;
use onOffice\WPlugin\SDKWrapper;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class FormPostOwnerConfigurationTest
	implements FormPostOwnerConfiguration
{
	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var string */
	private $_referrer = '';

	/** @var InputVariableReaderConfigTest */
	private $_pInputVariableReaderConfigTest = null;


	/**
	 *
	 */

	public function __construct()
	{
		$this->_pInputVariableReaderConfigTest =
			new InputVariableReaderConfigTest();
	}


	/**
	 *
	 * @return InputVariableReader
	 *
	 */

	public function getEstateListInputVariableReader(): InputVariableReader
	{
		return new InputVariableReader
			(onOfficeSDK::MODULE_ESTATE, $this->_pInputVariableReaderConfigTest);
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
	 * @param SDKWrapper $pSDKWrapper
	 *
	 */

	public function setSDKWrapper(SDKWrapper $pSDKWrapper)
	{
		$this->_pSDKWrapper = $pSDKWrapper;
	}


	/**
	 *
	 * @return InputVariableReaderConfigTest
	 *
	 */

	public function getEstateListInputVariableReaderConfigTest(): InputVariableReaderConfigTest
	{
		return $this->_pInputVariableReaderConfigTest;
	}
}
