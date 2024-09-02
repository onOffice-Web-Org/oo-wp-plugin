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

use onOffice\WPlugin\DataView\DataAddressDetailViewHandler;
use onOffice\WPlugin\Template;
use onOffice\WPlugin\WP\WPQueryWrapper;
use onOffice\WPlugin\Factory\AddressListFactory;

class ContentFilterShortCodeAddressDetail {

    /** @var DataAddressDetailViewHandler */
    private $_pDataAddressDetailViewHandler;

    /** @var Template */
    private $_pTemplate;

		/** @var AddressListFactory */
		private $_pAddressDetailFactory;

		/** @var WPQueryWrapper */
		private $_pWPQueryWrapper;

    public function __construct(DataAddressDetailViewHandler $dataAddressDetailViewHandler,
			Template $template,
			AddressListFactory $pAddressDetailFactory,
			WPQueryWrapper $pWPQueryWrapper) {
        $this->_pDataAddressDetailViewHandler = $dataAddressDetailViewHandler;
        $this->_pTemplate = $template;
				$this->_pAddressDetailFactory = $pAddressDetailFactory;
				$this->_pWPQueryWrapper = $pWPQueryWrapper;
    }
		/**
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function render(): string {
        $addressDetailView =  $this->_pDataAddressDetailViewHandler->getAddressDetailView();
        $template = $this->_pTemplate->withTemplateName($addressDetailView->getTemplate());
				$addressId = $this->_pWPQueryWrapper->getWPQuery()->query_vars['address_id'] ?? 0;
				$pAddressList = $this->_pAddressDetailFactory->createAddressDetail((int)$addressId);
				$pAddressList->loadSingleAddress($addressId);
        return $template
					->withAddressList($pAddressList)
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
