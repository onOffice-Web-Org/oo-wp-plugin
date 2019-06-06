<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

declare (strict_types=1);

namespace onOffice\WPlugin\Controller\ContentFilter;

use onOffice\WPlugin\AddressList;
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeAddressEnvironment;
use onOffice\WPlugin\DataView\DataListViewFactoryAddress;
use onOffice\WPlugin\Impressum;
use onOffice\WPlugin\Template;
use onOffice\WPlugin\Utility\Logger;
use onOffice\WPlugin\WP\WPQueryWrapper;


/**
 *
 * default environment for ContentFilterShortCodeAddress
 *
 */

class ContentFilterShortCodeAddressEnvironmentDefault
	implements ContentFilterShortCodeAddressEnvironment
{
	/** @var DataListViewFactoryAddress */
	private $_pDataListFactory = null;

	/** @var Logger */
	private $_pLogger = null;


	/**
	 *
	 */

	public function __construct()
	{
		$this->_pDataListFactory = new DataListViewFactoryAddress();
		$this->_pLogger = new Logger();
	}


	/**
	 *
	 * @return DataListViewFactoryAddress
	 *
	 */

	public function getDataListFactory(): DataListViewFactoryAddress
	{
		return $this->_pDataListFactory;
	}


	/**
	 *
	 * @return AddressList
	 *
	 */

	public function createAddressList(): AddressList
	{
		return new AddressList();
	}


	/**
	 *
	 * @return Template
	 *
	 */

	public function getTemplate(): Template
	{
		$pTemplate = new Template('');
		return $pTemplate->setImpressum(new Impressum);
	}


	/**
	 *
	 * @return Logger
	 *
	 */

	public function getLogger(): Logger
	{
		return $this->_pLogger;
	}


	/**
	 *
	 * @return WPQueryWrapper
	 *
	 */

	public function getWPQueryWrapper(): WPQueryWrapper
	{
		return new WPQueryWrapper();
	}
}