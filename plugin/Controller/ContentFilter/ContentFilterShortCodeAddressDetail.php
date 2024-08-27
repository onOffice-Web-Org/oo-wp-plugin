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

namespace onOffice\WPlugin\Controller\ContentFilter;

use onOffice\WPlugin\DataView\DataAddressDetailViewHandler;
use onOffice\WPlugin\Template;

class ContentFilterShortCodeAddressDetail {

    /** @var DataAddressDetailViewHandler */
    private $_pDataAddressDetailViewHandler;

    /** @var Template */
    private $_pTemplate;

    public function __construct(DataAddressDetailViewHandler $dataAddressDetailViewHandler, Template $template) {
        $this->_pDataAddressDetailViewHandler = $dataAddressDetailViewHandler;
        $this->_pTemplate = $template;
    }

    public function render(): string {
        $addressDetailView =  $this->_pDataAddressDetailViewHandler->getAddressDetailView();
        $template = $this->_pTemplate->withTemplateName($addressDetailView->getTemplate());
        return $template->render();
    }

    /**
     * @return string
     */
    public function getViewName(): string
    {
        return $this->_pDataAddressDetailViewHandler->getAddressDetailView()->getName();
    }
}