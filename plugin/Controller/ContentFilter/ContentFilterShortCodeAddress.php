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

	public function __construct(ContentFilterShortCodeAddressEnvironment $pEnvironment)
	{
		$this->_pEnvironment = $pEnvironment;
	}


	/**
	 *
	 * @param array $attributesInput
	 * @return string
	 *
	 */

	public function replaceShortCodes(array $attributesInput): string
	{
		$attributes = shortcode_atts([
			'view' => null,
		], $attributesInput);
		$addressListName = $attributes['view'];

		try {
			$pTemplate = $this->createTemplate($addressListName);
			return $pTemplate->render();
		} catch (Exception $pException) {
			return $this->_pEnvironment->getLogger()->logErrorAndDisplayMessage($pException);
		}
	}


	/**
	 *
	 * @param string $addressListName
	 * @return Template
	 *
	 */

	private function createTemplate(string $addressListName): Template
	{
		$page = $this->_pEnvironment->getWPQueryWrapper()->getWPQuery()->get('page', 1);
		$pAddressListView = $this->_pEnvironment->getDataListFactory()->getListViewByName($addressListName);
		$pAddressList = $this->_pEnvironment->createAddressList()->withDataListViewAddress($pAddressListView);
		$pAddressList->loadAddresses($page);
		$templateName = $pAddressListView->getTemplate(); // name
		$pTemplate = $this->_pEnvironment->getTemplate()->withTemplateName($templateName);
		$pTemplate->setAddressList($pAddressList);

		return $pTemplate;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getTag(): string
	{
		return 'oo_address';
	}
}