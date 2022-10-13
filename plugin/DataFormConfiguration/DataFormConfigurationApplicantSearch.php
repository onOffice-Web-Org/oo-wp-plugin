<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class DataFormConfigurationApplicantSearch
	extends DataFormConfiguration
{
	/** @var int */
	private $_limitResults = 100;

	/**
	 *
	 */

	public function setDefaultFields()
	{
		$this->setInputs([
			'vermarktungsart' => onOfficeSDK::MODULE_ADDRESS,
			'objekttyp' => onOfficeSDK::MODULE_ADDRESS,
			'kaufpreis' => onOfficeSDK::MODULE_ADDRESS,
			'kaltmiete' => onOfficeSDK::MODULE_ADDRESS,
			'wohnflaeche' => onOfficeSDK::MODULE_ADDRESS,
			'anzahl_zimmer' => onOfficeSDK::MODULE_ADDRESS,
		]);
	}

	/** @return int */
	public function getLimitResults()
		{ return $this->_limitResults; }

	/** @param int $limitResults */
	public function setLimitResults($limitResults)
		{ $this->_limitResults = $limitResults; }
}
