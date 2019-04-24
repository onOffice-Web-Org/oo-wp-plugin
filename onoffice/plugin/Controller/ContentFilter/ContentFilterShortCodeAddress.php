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
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCode;
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeAddressEnvironment;
use onOffice\WPlugin\Template;
use function shortcode_atts;

/**
 *
 * render address short codes
 *
 */

class ContentFilterShortCodeAddress
	implements ContentFilterShortCode
{
	/** @var ContentFilterShortCodeAddressEnvironment */
	private $_pEnvironment = null;


	/**
	 *
	 * @param ContentFilterShortCodeAddressEnvironment $pEnvironment
	 *
	 */

	public function __construct(ContentFilterShortCodeAddressEnvironment $pEnvironment = null)
	{
		$this->_pEnvironment = $pEnvironment ?? new ContentFilterShortCodeAddressEnvironmentDefault();
	}


	/**
	 *
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
			return $this->_pEnvironment->getLogger()->logErrorAndDisplayMessage($pException);
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
		$pAddressListView = $this->_pEnvironment->getDataListFactory()->getListViewByName($addressListName);
		$pAddressList = $this->_pEnvironment->createAddressList($pAddressListView);
		$pAddressList->loadAddresses($page);
		$templateName = $pAddressListView->getTemplate();

		$pTemplate = $this->_pEnvironment->getTemplate($templateName);
		$pTemplate->setAddressList($pAddressList);
		$pTemplate->setImpressum($this->_pEnvironment->getImpressum());

		return $pTemplate;
	}
}