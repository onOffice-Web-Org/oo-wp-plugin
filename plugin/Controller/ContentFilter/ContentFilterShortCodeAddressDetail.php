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

declare (strict_types=1);

namespace onOffice\WPlugin\Controller\ContentFilter;

use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\DataView\DataAddressDetailViewHandler;
use onOffice\WPlugin\Template;
use onOffice\WPlugin\WP\WPQueryWrapper;
use onOffice\WPlugin\Factory\AddressListFactory;

class ContentFilterShortCodeAddressDetail
{
	/** @var DataAddressDetailViewHandler */
	private $_pDataAddressDetailViewHandler;

	/** @var Template */
	private $_pTemplate;

	/** @var AddressListFactory */
	private $_pAddressListFactory;

	/** @var WPQueryWrapper */
	private $_pWPQueryWrapper;

	/**
	 * @param DataAddressDetailViewHandler $pDataAddressDetailViewHandler
	 * @param Template $pTemplate
	 * @param AddressListFactory $pAddressListFactory
	 * @param WPQueryWrapper $pWPQueryWrapper
	 */
	public function __construct(
		DataAddressDetailViewHandler $pDataAddressDetailViewHandler,
		Template $pTemplate,
		AddressListFactory $pAddressListFactory,
		WPQueryWrapper $pWPQueryWrapper)
	{
		$this->_pDataAddressDetailViewHandler = $pDataAddressDetailViewHandler;
		$this->_pTemplate = $pTemplate;
		$this->_pAddressListFactory = $pAddressListFactory;
		$this->_pWPQueryWrapper = $pWPQueryWrapper;
	}

	/**
	 * @return string
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws \onOffice\WPlugin\API\ApiClientException
	 */
	public function render(): string
	{
		$pDetailView = $this->_pDataAddressDetailViewHandler->getAddressDetailView();
		$pTemplate = $this->_pTemplate->withTemplateName($pDetailView->getTemplate());
		$addressId = $this->_pWPQueryWrapper->getWPQuery()->query_vars['address_id'] ?? 0;
		$pEstateDetailList = $this->_pAddressListFactory->createAddressDetail((int)$addressId);
		$pEstateDetailList->loadAddressDetailView((int)$addressId);

		return $pTemplate
			->withAddressList($pEstateDetailList)
			->render();
	}

	/**
	 * @return string
	 */
	public function getViewName(): string
	{
		return $this->_pDataAddressDetailViewHandler->getAddressDetailView()->getName();
	}
}