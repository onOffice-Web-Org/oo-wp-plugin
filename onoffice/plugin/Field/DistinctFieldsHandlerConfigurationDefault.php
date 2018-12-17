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

use onOffice\WPlugin\Field\DistinctFieldsHandlerConfiguration;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Types\FieldsCollection;


/**
 *
 * default configuration for DistinctFieldsHandler
 *
 */

class DistinctFieldsHandlerConfigurationDefault
	implements DistinctFieldsHandlerConfiguration
{

	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var fieldnames */
	private $_pFieldnames = null;


	/**
	 *
	 */

	public function __construct()
	{
		$this->_pSDKWrapper = new SDKWrapper();
		$this->_pFieldnames = new Fieldnames(new FieldsCollection());
		$this->_pFieldnames->loadLanguage();
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
	 * @return Fieldnames
	 *
	 */

	public function getFieldnames(): Fieldnames
	{
		return $this->_pFieldnames;
	}

}