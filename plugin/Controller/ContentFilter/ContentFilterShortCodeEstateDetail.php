<?php

/**
 *
 *    Copyright (C) 2020 onOffice GmbH
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

use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\Factory\EstateListFactory;
use onOffice\WPlugin\Template;
use onOffice\WPlugin\WP\WPQueryWrapper;

class ContentFilterShortCodeEstateDetail
{
	/** @var DataDetailViewHandler */
	private $_pDataDetailViewHandler;

	/** @var Template */
	private $_pTemplate;

	/** @var EstateListFactory */
	private $_pEstateDetailFactory;

	/** @var WPQueryWrapper */
	private $_pWPQueryWrapper;

	/**
	 * @param DataDetailViewHandler $pDataDetailViewHandler
	 * @param Template $pTemplate
	 * @param EstateListFactory $pEstateDetailFactory
	 * @param WPQueryWrapper $pWPQueryWrapper
	 */
	public function __construct(
		DataDetailViewHandler $pDataDetailViewHandler,
		Template $pTemplate,
		EstateListFactory $pEstateDetailFactory,
		WPQueryWrapper $pWPQueryWrapper)
	{
		$this->_pTemplate = $pTemplate;
		$this->_pDataDetailViewHandler = $pDataDetailViewHandler;
		$this->_pEstateDetailFactory = $pEstateDetailFactory;
		$this->_pWPQueryWrapper = $pWPQueryWrapper;
	}

	/**
	 * @param array $attributes
	 * @return string
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function render(array $attributes): string
	{
		$pDetailView = $this->_pDataDetailViewHandler->getDetailView();
		$pTemplate = $this->_pTemplate->withTemplateName($pDetailView->getTemplate());
		$estateId = $this->_pWPQueryWrapper->getWPQuery()->query_vars['estate_id'] ?? 0;
		$pEstateDetailList = $this->_pEstateDetailFactory->createEstateDetail($estateId);
		$pEstateDetailList->setUnitsViewName($attributes['units'] ?? null);
		$pEstateDetailList->loadSingleEstate($estateId);
		return $pTemplate
			->withEstateList($pEstateDetailList)
			->render();
	}

	/**
	 * @return string
	 */
	public function getViewName(): string
	{
		return $this->_pDataDetailViewHandler->getDetailView()->getName();
	}
}