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

use DI\Container;
use Exception;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\AddressList;
use onOffice\WPlugin\DataView\DataListViewFactoryAddress;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Filter\SearchParameters\SearchParameters;
use onOffice\WPlugin\Filter\SearchParameters\SearchParametersModelBuilder;
use onOffice\WPlugin\Template;
use onOffice\WPlugin\Utility\Logger;
use onOffice\WPlugin\WP\WPQueryWrapper;
use function shortcode_atts;

/**
 *
 * render address short codes
 *
 */

class ContentFilterShortCodeAddress
	implements ContentFilterShortCode
{
	/** @var FieldsCollectionBuilderShort */
	private $_pBuilderShort;

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

	/** @var mixed|AddressList */
	private $_pAddressList;

	/**
	 * ContentFilterShortCodeAddress constructor.
	 *
	 * @param Container $pContainer
	 * @param FieldsCollectionBuilderShort $pBuilderShort
	 * @param SearchParametersModelBuilder $pSearchParametersModelBuilder
	 * @param AddressList $pAddressList
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function __construct(
		Container $pContainer,
		FieldsCollectionBuilderShort $pBuilderShort,
		SearchParametersModelBuilder $pSearchParametersModelBuilder,
		AddressList $pAddressList)
	{
		$this->_pBuilderShort = $pBuilderShort;
		$this->_pSearchParametersModelBuilder = $pSearchParametersModelBuilder;

		$this->_pLogger = $pContainer->get(Logger::class);
		$this->_pDataListFactory = $pContainer->get(DataListViewFactoryAddress::class);
		$this->_pAddressList = $pAddressList;
		$this->_pTemplate = $pContainer->get(Template::class);
		$this->_pWPQueryWrapper = $pContainer-> get(WPQueryWrapper::class);
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
			return $this->_pLogger->logErrorAndDisplayMessage($pException);
		}
	}

	/**
	 * @param string $addressListName
	 * @return Template
	 * @throws \onOffice\WPlugin\DataView\UnknownViewException
	 */
	private function createTemplate(string $addressListName): Template
	{
		$page = $this->_pWPQueryWrapper->getWPQuery()->get('page', 1);
		$pAddressListView = $this->_pDataListFactory->getListViewByName($addressListName);
		$pAddressList = $this->_pAddressList->withDataListViewAddress($pAddressListView);
		$pAddressList->loadAddresses($page);
		$this->populateWpLinkPagesArgs($pAddressListView->getFilterableFields());
		$templateName = $pAddressListView->getTemplate(); // name
		$pTemplate = $this->_pTemplate->withTemplateName($templateName);
		$pTemplate->setAddressList($pAddressList);

		return $pTemplate;
	}

	/**
	 * @param array $filterableFields
	 */
	private function populateWpLinkPagesArgs(array $filterableFields)
	{
		$pModel = $this->_pSearchParametersModelBuilder->build(
			$filterableFields, onOfficeSDK::MODULE_ADDRESS, $this->_pBuilderShort);

		add_filter('wp_link_pages_link', function(string $link, int $i) use ($pModel): string {
			$pSearchParameters = new SearchParameters();
			return $pSearchParameters->linkPagesLink($link, $i, $pModel);
		}, 10, 2);
		add_filter('wp_link_pages_args', [$pModel, 'populateDefaultLinkParams']);
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