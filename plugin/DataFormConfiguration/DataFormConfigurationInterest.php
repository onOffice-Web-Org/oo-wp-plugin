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

namespace onOffice\WPlugin\DataFormConfiguration;

use onOffice\SDK\onOfficeSDK;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class DataFormConfigurationInterest
	extends DataFormConfiguration
{
	/** @var bool */
	private $_createInterest = true;


	/**
	 *
	 */

	public function setDefaultFields()
	{
		$this->setInputs(array(
			'Vorname' => onOfficeSDK::MODULE_ADDRESS,
			'Name' => onOfficeSDK::MODULE_ADDRESS,
			'Strasse' => onOfficeSDK::MODULE_ADDRESS,
			'Plz' => onOfficeSDK::MODULE_ADDRESS,
			'Ort' => onOfficeSDK::MODULE_ADDRESS,
			'Telefon1' => onOfficeSDK::MODULE_ADDRESS,
			'Email' => onOfficeSDK::MODULE_ADDRESS,
		));
	}


	/** @var bool */
	private $_checkDuplicateOnCreateAddress = false;

	/** @var string */
	private $_subject = null;

	/** @var string */
	private $_recipient = null;

	/** @var bool */
	private $_defaultRecipient = false;

	/** @return bool */
	public function getCheckDuplicateOnCreateAddress()
		{ return $this->_checkDuplicateOnCreateAddress; }

	/** @return string */
	public function getSubject()
		{ return $this->_subject; }

	/** @return bool */
	public function getCreateInterest(): bool
	{ return $this->_createInterest; }

	/** @param bool $createInterest */
	public function setCreateInterest(bool $createInterest)
	{ $this->_createInterest = $createInterest; }

	/** @param bool $checkDuplicateOnCreateAddress */
	public function setCheckDuplicateOnCreateAddress($checkDuplicateOnCreateAddress)
		{ $this->_checkDuplicateOnCreateAddress = (bool)$checkDuplicateOnCreateAddress; }

	/** @param string $subject */
	public function setSubject($subject)
		{ $this->_subject = $subject; }
}
