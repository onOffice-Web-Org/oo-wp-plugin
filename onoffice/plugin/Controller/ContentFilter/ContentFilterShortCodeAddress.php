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

namespace onOffice\WPlugin\Controller\ContentFilter;

use Exception;
use onOffice\WPlugin\AddressList;
use onOffice\WPlugin\ContentFilter;
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCode;
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeAddressEnvironment;
use onOffice\WPlugin\DataView\DataListViewAddress;
use onOffice\WPlugin\DataView\DataListViewFactoryAddress;
use onOffice\WPlugin\Impressum;
use onOffice\WPlugin\Template;

/**
 *
 * render address short codes
 *
 */

class ContentFilterShortCodeAddress
	extends ContentFilter
		implements ContentFilterShortCode
{

	/** @var ContentFilterShortCodeAddressEnvironment */
	private $_pEnvironment = null;

	/** @var DataListViewFactoryAddress */
	private $_pDataListFactory = null;

	/** @var AddressList */
	private $_pAddressList = null;

	/** @var Impressum */
	private $_pImpressum = null;



	/**
	 *
	 * @param ContentFilterShortCodeAddressEnvironment $pEnvironment
	 *
	 */

	public function __construct(ContentFilterShortCodeAddressEnvironment $pEnvironment = null)
	{
		if ($pEnvironment === null)
		{
			$this->_pEnvironment = new ContentFilterShortCodeAddressEnvironmentDefault();
		}
		else
		{
			$this->_pEnvironment = $pEnvironment;
		}

		$this->_pDataListFactory = $this->_pEnvironment->getDataListFactory();
		$this->_pImpressum = $this->_pEnvironment->getImpressum();
	}


	/**
	 *
	 * @global WP_Query $wp_query
	 * @param array $attributesInput
	 * @return string
	 *
	 */

	public function replaceShortCodes(array $attributesInput): string
	{
		$page = $this->_pEnvironment->getPage();

		$attributes = shortcode_atts([
			'view' => null,
		], $attributesInput);
		$addressListName = $attributes['view'];

		try {
			$pTemplate = $this->createTemplate($addressListName, $page);
			return $pTemplate->render();
		} catch (Exception $pException) {
			return $this->logErrorAndDisplayMessage($pException);
		}
	}


	/**
	 *
	 * @param string $addressListName
	 * @param int $page
	 *
	 */

	private function createTemplate(string $addressListName, int $page = 1): Template
	{
		$templateName = $this->getTemplateName($addressListName, $page);

		$pTemplate = $this->_pEnvironment->getTemplate($templateName);
		$pTemplate->setAddressList($this->_pAddressList);
		$pTemplate->setImpressum($this->_pImpressum);

		return $pTemplate;
	}


	/**
	 *
	 * @param string $addressListName
	 * @param int $page
	 * @return string
	 *
	 */

	private function getTemplateName(string $addressListName, int $page = 1): string
	{
		$pAddressListView = $this->_pDataListFactory->getListViewByName($addressListName);
		$this->buildAddressList($pAddressListView, $page);
		$templateName = $pAddressListView->getTemplate();

		return $templateName;
	}


	/**
	 *
	 * @param type $pAddressListView
	 * @param type $page
	 *
	 */

	private function buildAddressList(DataListViewAddress $pAddressListView, int $page)
	{
		$this->_pAddressList = $this->_pEnvironment->createAddressList($pAddressListView);
		$this->_pAddressList->loadAddresses($page);
	}
}