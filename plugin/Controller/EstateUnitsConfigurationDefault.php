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

namespace onOffice\WPlugin\Controller;

use DI\ContainerBuilder;
use onOffice\WPlugin\DataView\DataView;
use onOffice\WPlugin\EstateList;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Template;
use const ONOFFICE_DI_CONFIG_PATH;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class EstateUnitsConfigurationDefault
	implements EstateUnitsConfigurationBase
{
	/** @var EstateList */
	private $_pEstateList = null;

	/** @var Container */
	private $_pContainer;


	/**
	 *
	 */

	public function __construct(DataView $pDataView)
	{
		$pDIContainerBuilder = new ContainerBuilder;
		$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pDIContainerBuilder->build();
		$this->_pEstateList = new EstateList($pDataView);
	}


	/**
	 *
	 * @return EstateListBase
	 *
	 */

	public function getEstateList(): EstateListBase
	{
		return $this->_pEstateList;
	}


	/**
	 *
	 * @return SDKWrapper
	 *
	 */

	public function getSDKWrapper(): SDKWrapper
	{
		return $this->_pContainer->get(SDKWrapper::class);
	}


	/**
	 *
	 * @param string $templateName
	 * @return Template
	 *
	 */

	public function getTemplate(string $templateName): Template
	{
		return $this->_pContainer->get(Template::class)->withTemplateName($templateName);
	}
}
