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

use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataView\DataListViewFactoryAddress;
use onOffice\WPlugin\DataView\UnknownViewException;
use onOffice\WPlugin\Factory\AddressListFactory;
use onOffice\WPlugin\Filter\SearchParameters\SearchParameters;
use onOffice\WPlugin\Filter\SearchParameters\SearchParametersModelBuilder;
use onOffice\WPlugin\Template;
use onOffice\WPlugin\Utility\Logger;
use onOffice\WPlugin\WP\WPQueryWrapper;


class ContentFilterShortCodeAddress implements ContentFilterShortCode
{
	/** @var SearchParametersModelBuilder */
	private $_pSearchParametersModelBuilder;

	/** @var Logger */
	private $_pLogger;

	/** @var DataListViewFactoryAddress */
	private $_pDataListFactory;

	/** @var Template */
	private $_pTemplate;

	/** @var WPQueryWrapper */
	private $_pWPQueryWrapper;

	/** @var AddressListFactory */
	private $_pAddressListFactory;

	/**  @var ContentFilterShortCodeAddressDetail */
	private ContentFilterShortCodeAddressDetail $_pContentFilterShortCodeAddressDetail;

    /**
	 * ContentFilterShortCodeAddress constructor.
	 *
	 * @param SearchParametersModelBuilder $pSearchParametersModelBuilder
	 * @param AddressListFactory $pAddressListFactory
	 * @param Logger $pLogger
	 * @param DataListViewFactoryAddress $pDataListFactory
	 * @param Template $pTemplate
	 * @param WPQueryWrapper $pWPQueryWrapper,
	 * @param ContentFilterShortCodeAddressDetail $pContentFilterShortCodeAddressDetail
	 */
	public function __construct(
		SearchParametersModelBuilder $pSearchParametersModelBuilder,
		AddressListFactory $pAddressListFactory,
		Logger $pLogger,
		DataListViewFactoryAddress $pDataListFactory,
		Template $pTemplate,
		WPQueryWrapper $pWPQueryWrapper,
    ContentFilterShortCodeAddressDetail $pContentFilterShortCodeAddressDetail)
	{
		$this->_pSearchParametersModelBuilder = $pSearchParametersModelBuilder;

		$this->_pLogger = $pLogger;
		$this->_pDataListFactory = $pDataListFactory;
		$this->_pAddressListFactory = $pAddressListFactory;
		$this->_pTemplate = $pTemplate;
		$this->_pWPQueryWrapper = $pWPQueryWrapper;
		$this->_pContentFilterShortCodeAddressDetail = $pContentFilterShortCodeAddressDetail;
	}

	/**
	 * @param array $attributesInput
	 * @return string
	 */
	public function replaceShortCodes(array $attributesInput): string
	{
		$attributes = shortcode_atts([
			'view' => null,
		], $attributesInput);
		$addressListName = $attributes['view'];

		try {
			if ($attributes['view'] === $this->_pContentFilterShortCodeAddressDetail->getViewName()) {
					return $this->_pContentFilterShortCodeAddressDetail->render();
			} else {
					$pTemplate = $this->createTemplate($addressListName);
					return $pTemplate->render();
			}
		} catch (Exception $pException) {
			return $this->_pLogger->logErrorAndDisplayMessage($pException);
		}
	}

	/**
	 * @param string $addressListName
	 * @return Template
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownViewException
	 */
	private function createTemplate(string $addressListName): Template
	{
		$page = $this->_pWPQueryWrapper->getWPQuery()->get('paged', 1);
		$pAddressListView = $this->_pDataListFactory->getListViewByName($addressListName);
		$pAddressList = $this->_pAddressListFactory->create($pAddressListView)->withDataListViewAddress($pAddressListView);
		$pAddressList->loadAddresses($page);
		$this->populateWpLinkPagesArgs($pAddressListView->getFilterableFields());
		$templateName = $pAddressListView->getTemplate(); // name
		return $this->_pTemplate
			->withTemplateName($templateName)
			->withAddressList($pAddressList);
	}

	/**
	 * @param array $filterableFields
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function populateWpLinkPagesArgs(array $filterableFields)
	{
		$pModel = $this->_pSearchParametersModelBuilder->build
			($filterableFields, onOfficeSDK::MODULE_ADDRESS);

		add_filter('wp_link_pages_link', function(string $link, int $i) use ($pModel): string {
			$pSearchParameters = new SearchParameters();
			return $pSearchParameters->linkPagesLink($link, $i, $pModel);
		}, 10, 2);
		add_filter('wp_link_pages_args', [$pModel, 'populateDefaultLinkParams']);
	}

	/**
	 * @return string
	 */
	public function getTag(): string
	{
		return 'oo_address';
	}
}
