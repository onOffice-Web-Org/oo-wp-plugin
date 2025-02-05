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

use onOffice\WPlugin\AddressList;
use onOffice\WPlugin\DataView\DataAddressDetailViewHandler;
use onOffice\WPlugin\Template;
use onOffice\WPlugin\WP\WPQueryWrapper;
use onOffice\WPlugin\Factory\AddressListFactory;
use onOffice\WPlugin\Controller\AddressDetailUrl;
use onOffice\WPlugin\Controller\AddressListEnvironmentDefault;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Language;

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
        if ($addressId === 0) {
          return $this->renderHtmlHelperUserIfEmptyAddressId();
        }
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

	/**
	 * @return string
	 */
	private function renderHtmlHelperUserIfEmptyAddressId(): string
	{
		$pDataDetail = $this->getRandomAddressDetail();
		$firstName = $pDataDetail['elements']['Vorname'] ?? '';
		$lastName = $pDataDetail['elements']['Name'] ?? '';
		$company = $pDataDetail['elements']['Zusatz1'] ?? '';

		$itemTitle = AddressList::createAddressTitle($firstName, $lastName, $company);
		$type = __('address', 'onoffice-for-wp-websites');
		$documentLink = __('https://wp-plugin.onoffice.com/', 'onoffice-for-wp-websites');
		$linkDetail = '<a class="oo-detailview-helper-link" href=' . $this->getAddressLink($pDataDetail, $itemTitle) . '>' . (!empty($itemTitle) ? esc_html($itemTitle) : esc_html(__('Example address', 'onoffice-for-wp-websites'))) . '</a>';

		return RenderHtmlHelperUsers::renderHtmlHelperUserIfEmptyId($type, $documentLink, $linkDetail, $pDataDetail);
	}


	/**
	 * @return array
	 */
	private function getRandomAddressDetail(): array
	{
		$pEnvironment = new AddressListEnvironmentDefault();
		$pSDKWrapper = $pEnvironment->getSDKWrapper();
		$language = Language::getDefault();

		$requestParams = [
			'data' => ['Vorname', 'Name', 'Zusatz1'],
			'outputlanguage' => $language
		];
		$requestParams['filter']['homepage_veroeffentlichen'][] = ['op' => '=', 'val' => 1];

		$pApiClientAction = new APIClientActionGeneric
		($pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'address');
		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->addRequestToQueue()->sendRequests();
		$pAddressList = $pApiClientAction->getResultRecords();

		if (!empty($pAddressList)) {
			$randomIdDetail = array_rand($pAddressList, 1);
			return $pAddressList[ $randomIdDetail ];
		}

		return [];
	}

	/**
	 * @param array $pAddressListDetail
	 * @param string $addressTitle
	 * @return string
	 */
	private function getAddressLink(array $pAddressListDetail, string $addressTitle): string
	{
		$pLanguageSwitcher = new AddressDetailUrl;
		if (empty($pAddressListDetail)) {
			return '';
		}

		$addressId = $pAddressListDetail['elements']['id'];
		$fullLink = '#';

		$url = get_page_link();
		$fullLink = $pLanguageSwitcher->createAddressDetailLink($url, $addressId, $addressTitle);
		$fullLinkElements = parse_url($fullLink);
		if (empty($fullLinkElements['query'])) {
			$fullLink .= '/';
		}

		return $fullLink;
	}
}
