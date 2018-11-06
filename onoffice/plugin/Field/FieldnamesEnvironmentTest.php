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

namespace onOffice\WPlugin\Field;

use onOffice\tests\SDKWrapperMocker;
use onOffice\WPlugin\SDKWrapper;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class FieldnamesEnvironmentTest
	implements FieldnamesEnvironment
{
	/** @var string */
	private $_language = 'ENG';

	/** @var SDKWrapperMocker */
	private $_pSDKWrapper = null;


	/**
	 *
	 */

	public function __construct()
	{
		$this->_pSDKWrapper = new SDKWrapperMocker();
	}


	/** @return string */
	public function getLanguage(): string
		{ return $this->_language; }

	/** @param string $language */
	public function setLanguage(string $language)
		{ $this->_language = $language; }

	/** @return SDKWrapperMocker */
	public function getSDKWrapper(): SDKWrapper
		{ return $this->_pSDKWrapper; }
}
