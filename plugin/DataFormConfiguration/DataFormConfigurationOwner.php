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

class DataFormConfigurationOwner
	extends DataFormConfiguration
{
	/** @var bool */
	private $_createOwner = true;


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
			'vermarktungsart' => onOfficeSDK::MODULE_ESTATE,
			'objektart' => onOfficeSDK::MODULE_ESTATE,
			'objekttyp' => onOfficeSDK::MODULE_ESTATE,
			'wohnflaeche' => onOfficeSDK::MODULE_ESTATE,
			'plz' => onOfficeSDK::MODULE_ESTATE,
			'ort' => onOfficeSDK::MODULE_ESTATE,
			'message' => null,
		));

		$this->setRequiredFields(['Email', 'Name', 'vermarktungsart']);
	}


	/** @var bool */
	private $_checkDuplicateOnCreateAddress = false;

	/** @var int */
	private $_pages = 1;

	/** @var string */
	private $_subject = null;

	/** @var string */
	private $_recipient = null;


	/** @return bool */
	public function getCheckDuplicateOnCreateAddress()
		{ return $this->_checkDuplicateOnCreateAddress; }

	/** @return bool */
	public function getPages()
		{ return $this->_pages; }

	/** @return bool */
	public function getCreateOwner(): bool
	{ return $this->_createOwner; }

	/** @param bool $createOwner */
	public function setCreateOwner(bool $createOwner)
	{ $this->_createOwner = $createOwner; }

	/** @param bool $checkDuplicateOnCreateAddress */
	public function setCheckDuplicateOnCreateAddress($checkDuplicateOnCreateAddress)
		{ $this->_checkDuplicateOnCreateAddress = (bool)$checkDuplicateOnCreateAddress; }

	/** @param bool $pages */
	public function setPages($pages)
		{ $this->_pages = $pages; }

	/** @return string */
	public function getSubject()
		{ return $this->_subject; }

	/** @return string */
	public function getRecipient()
		{ return $this->_recipient; }

	/** @param string $subject */
	public function setSubject($subject)
		{ $this->_subject = $subject; }

	/** @param string $recipient */
	public function setRecipient($recipient)
		{ $this->_recipient = $recipient; }
}
