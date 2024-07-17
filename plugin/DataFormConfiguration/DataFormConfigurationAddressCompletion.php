<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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
 * @copyright 2003-2024, onOffice(R) GmbH
 *
 */

class DataFormConfigurationAddressCompletion
	extends DataFormConfiguration
{
	/**
	 *
	 */

	public function setDefaultFields()
	{
		$this->setInputs(array(
			'Vorname' => onOfficeSDK::MODULE_ADDRESS,
			'Name' => onOfficeSDK::MODULE_ADDRESS,
			'Telefon1' => onOfficeSDK::MODULE_ADDRESS,
			'Email' => onOfficeSDK::MODULE_ADDRESS,
		));
	}

	/** @var string */
	private $_subject = null;

	/** @return string */
	public function getSubject()
		{ return $this->_subject; }

	/** @param string $subject */
	public function setSubject($subject)
		{ $this->_subject = $subject; }
}
