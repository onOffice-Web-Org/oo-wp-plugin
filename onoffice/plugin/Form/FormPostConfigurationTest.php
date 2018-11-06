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

use Exception;
use onOffice\WPlugin\SDKWrapper;


/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class FormPostConfigurationTest
	implements FormPostConfiguration
{
	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var array */
	private $_postVariables = [];

	/** @var array */
	private $_fieldTypes = [];

	/** @var string */
	private $_captchaSecret = '';

	/** @var string */
	private $_postvarCaptchaToken = '';


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
	 * @return array
	 *
	 */

	public function getPostVars(): array
	{
		return $this->_postVariables;
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
	 * @param array $postVariables
	 *
	 */

	public function setPostVariables(array $postVariables)
	{
		$this->_postVariables = $postVariables;
	}


	/**
	 *
	 * @param string $input
	 * @param string $module
	 * @return string
	 * @throws Exception
	 *
	 */

	public function getTypeForInput(string $input, string $module): string
	{
		if (isset($this->_fieldTypes[$module][$input])) {
			return $this->_fieldTypes[$module][$input];
		}

		throw new Exception('Type for field '.$input.' in module '.$module.' was not set');
	}


	/**
	 *
	 * @param string $module
	 * @param string $input
	 * @param string $type
	 *
	 */

	public function addInputType(string $module, string $input, string $type)
	{
		$this->_fieldTypes[$module][$input] = $type;
	}


	/**
	 *
	 * @param string $key
	 * @param string $value
	 *
	 */

	public function addPostVariableString(string $key, string $value)
	{
		$this->_postVariables[$key] = $value;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getCaptchaSecret(): string
	{
		return $this->_captchaSecret;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getPostvarCaptchaToken(): string
	{
		return $this->_postvarCaptchaToken;
	}


	/**
	 *
	 * @param string $captchaSecret
	 *
	 */

	public function setCaptchaSecret(string $captchaSecret)
	{
		$this->_captchaSecret = $captchaSecret;
	}


	/**
	 *
	 * @param string $postvarCaptchaToken
	 *
	 */

	public function setPostvarCaptchaToken(string $postvarCaptchaToken)
	{
		$this->_postvarCaptchaToken = $postvarCaptchaToken;
	}


	/**
	 *
	 * @return bool
	 *
	 */

	public function isCaptchaSetup(): bool
	{
		return true;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getSearchCriteriaFields(): array
	{
		$jsonFile = ONOFFICE_PLUGIN_DIR.'/tests/resources/FormPostSearchCriteriaFields.json';
		$jsonString = file_get_contents($jsonFile);
		return json_decode($jsonString, true);
	}
}
