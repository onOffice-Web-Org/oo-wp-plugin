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

namespace onOffice\WPlugin;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Form;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class GeoPositionFormSettings
{
	/** @var array */
	private $_settings = [];

	/** @var string */
	private $_formType = null;



	/**
	 *
	 * @param string $formType
	 *
	 */

	public function __construct($formType)
	{
		$this->_formType = $formType;

		$pGeoPosition = new GeoPosition();

		switch ($formType)
		{
			case Form::TYPE_APPLICANT_SEARCH:
				$this->_settings = $pGeoPosition->getSettingsGeoPositionFieldsWithoutRadius();
				break;

			default:
				$this->_settings = $pGeoPosition->getSettingsGeoPositionFields(onOfficeSDK::MODULE_SEARCHCRITERIA);
				break;
		}
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getSettings()
	{ return $this->_settings; }
}
