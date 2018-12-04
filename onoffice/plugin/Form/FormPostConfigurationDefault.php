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
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Types\FieldsCollection;
use function get_option;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class FormPostConfigurationDefault
	implements FormPostConfiguration
{
	/** @var Fieldnames */
	private $_pFieldNames = null;


	/**
	 *
	 */

	public function __construct()
	{
		$this->_pFieldNames = new Fieldnames(new FieldsCollection());
		$this->_pFieldNames->loadLanguage();
	}


	/**
	 *
	 * @return SDKWrapper
	 *
	 */

	public function getSDKWrapper(): SDKWrapper
	{
		return new SDKWrapper();
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getPostVars(): array
	{
		return $_POST;
	}


	/**
	 *
	 * @param string $input
	 * @param string $module
	 * @return string
	 *
	 */

	public function getTypeForInput(string $input, string $module): string
	{
		return $this->_pFieldNames->getType($input, $module);
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getPostvarCaptchaToken(): string
	{
		return filter_input(INPUT_POST, CaptchaHandler::RECAPTCHA_RESPONSE_PARAM) ?? '';
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getCaptchaSecret(): string
	{
		return get_option('onoffice-settings-captcha-secretkey', '');
	}


	/**
	 *
	 * @return bool
	 *
	 */

	public function isCaptchaSetup(): bool
	{
		return get_option('onoffice-settings-captcha-sitekey', '') !== '';
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getSearchCriteriaFields(): array
	{
		$pFieldnames = new Fieldnames(new FieldsCollection());
		$pFieldnames->loadLanguage();
		return $pFieldnames->getFieldList(onOfficeSDK::MODULE_SEARCHCRITERIA);
	}


	/**
	 *
	 * @param string $logString
	 *
	 */

	public function log(string $logString)
	{
		error_log('[onOffice-Plugin]: Form error: '.$logString);
	}
}
