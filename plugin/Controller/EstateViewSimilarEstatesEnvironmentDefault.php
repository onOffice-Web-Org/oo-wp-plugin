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

use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use onOffice\WPlugin\DataView\DataView;
use onOffice\WPlugin\EstateList;
use onOffice\WPlugin\Template;

use const ONOFFICE_DI_CONFIG_PATH;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class EstateViewSimilarEstatesEnvironmentDefault
	implements EstateViewSimilarEstatesEnvironment
{
	/** @var EstateList */
	private $_pEstateList;

	/** @var Container */
	private $_pContainer;

	/**
	 * @param DataView $pDataView
	 * @param EstateListBase $pEstateList
	 * @throws Exception
	 */
	public function __construct(DataView $pDataView, EstateListBase $pEstateList = null)
	{
		$this->_pEstateList = $pEstateList ?? new EstateList($pDataView);
		$this->_pEstateList->setFormatOutput(false);
		$pDIContainerBuilder = new ContainerBuilder;
		$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pDIContainerBuilder->build();
	}

	/**
	 * @return EstateListBase
	 */
	public function getEstateList(): EstateListBase
	{
		return $this->_pEstateList;
	}

	/**
	 * @return Template
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function getTemplate(): Template
	{
		return $this->_pContainer->get(Template::class);
	}
}
