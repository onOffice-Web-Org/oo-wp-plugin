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

use DI\ContainerBuilder;
use onOffice\WPlugin\Language;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Controller\EstateDetailUrl;
use onOffice\WPlugin\Controller\EstateListEnvironmentDefault;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\Factory\EstateListFactory;
use onOffice\WPlugin\Template;
use onOffice\WPlugin\WP\WPQueryWrapper;
use function is_user_logged_in;

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
		if($estateId === 0){
			return $this->renderHtmlHelperUserIfEmptyEstateId();
		}
		$pEstateDetailList = $this->_pEstateDetailFactory->createEstateDetail((int)$estateId);
		$pEstateDetailList->setUnitsViewName($attributes['units'] ?? null);
		$pEstateDetailList->loadSingleEstate($estateId);
		return $pTemplate
			->withEstateList($pEstateDetailList)
			->render();
	}

	public function renderHtmlHelperUserIfEmptyEstateId()
	{
		$pDataEstateDetail = $this->getRandomEstateDetail();
		$estateTitle       = __( "estate list documentation", 'onoffice-for-wp-websites' );
		$linkEstateDetail  = __( "https://wp-plugin.onoffice.com/en/first-steps/estate-lists/",
			'onoffice-for-wp-websites' );
		$linkEstateDetail  = '<a href=' . $linkEstateDetail . '>' . $estateTitle . '</a>';
		$description       = sprintf( __( "The plugin couldn't find any estates. Please make sure that you have published some estates, as described in the %s",
			'onoffice-for-wp-websites' ), $linkEstateDetail );
		if ( ! empty( $pDataEstateDetail ) ) {
			$titleDefault     = __( 'Example estate', 'onoffice-for-wp-websites' );
			$estateTitle      = $pDataEstateDetail['elements']["objekttitel"] !== '' ? $pDataEstateDetail['elements']["objekttitel"] : $titleDefault;
			$linkEstateDetail = $this->getEstateLink( $pDataEstateDetail );
			$linkEstateDetail = '<br><a href=' . $linkEstateDetail . '>' . $estateTitle . '</a>';
			$description      = sprintf( __( 'Since you are logged in, here is a link to a random estate so that you can preview the detail page: %s',
				'onoffice-for-wp-websites' ), $linkEstateDetail );
		}
		$html = '<div>';
		$html .= '<p>' . __( 'You have opened the detail page, but we do not know which estate to show you, because there is no estate ID in the URL. Please go to an estate list and open an estate from there.',
				'onoffice-for-wp-websites' ) . '</p>';

		if ( is_user_logged_in() ) {
			$html .= '<p>' . $description . '</p>';
		}
		$html .= '</div>';

		return $html;
	}

	public function getRandomEstateDetail()
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions( ONOFFICE_DI_CONFIG_PATH );
		$pContainer                     = $pContainerBuilder->build();
		$pEnvironment                   = new EstateListEnvironmentDefault( $pContainer );
		$pSDKWrapper                    = $pEnvironment->getSDKWrapper();
		$language = Language::getDefault();
		$pApiClientAction               = new APIClientActionGeneric
		( $pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'estate' );
		$estateParametersRaw['data']    = $pEnvironment->getEstateStatusLabel()->getFieldsByPrio();
		$estateParametersRaw['data'] [] = 'veroeffentlichen';
		$estateParametersRaw['data'] [] = 'objekttitel';
		$estateParametersRaw['addMainLangId'] = true;
		$estateParametersRaw['estatelanguage'] = $language;
		$estateParametersRaw['outputlanguage'] = $language;

		$pApiClientAction->setParameters( $estateParametersRaw );
		$pApiClientAction->addRequestToQueue()->sendRequests();
		$pEstateList   = $pApiClientAction->getResultRecords();
		$pListEstateDetail = [];

		foreach ( $pEstateList as $pEstateListDetails ) {
			$referenz = $pEstateListDetails['elements']['referenz'];
			$publish  = $pEstateListDetails['elements']['veroeffentlichen'];
			if ( $referenz === '0' && $publish === '1' ) {
				$pListEstateDetail[] = $pEstateListDetails;
			};
		}

		if ( ! empty( $pListEstateDetail ) ) {
			$randomIdDetail = array_rand( $pListEstateDetail, 1 );
			return $pListEstateDetail[ $randomIdDetail ];
		}

		return [];
	}

	/**
	 * @return string
	 */
	public function getViewName(): string
	{
		return $this->_pDataDetailViewHandler->getDetailView()->getName();
	}

	/**
	 * @return string
	 */
	public function getViewNameReplace(): string
	{
		return $this->_pDataDetailViewHandler->getDetailView()->getViewNameReplace();
	}

	/**
	 * @return string
	 */
	public function getPageLink(): string
	{
		return get_page_link();
	}


	/**
	 * @return string
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function getEstateLink( $pEstateListDetail ): string
	{
		$pLanguageSwitcher = new EstateDetailUrl;
		$pageId            = $pEstateListDetail['elements']['mainLangId'] ?? $pEstateListDetail['id'];
		$fullLink          = '#';

		if ( $pageId !== 0 ) {
			$estate           = $pEstateListDetail['elements']['mainLangId'] ?? $pEstateListDetail['id'];
			$title            = $pEstateListDetail['elements']['objekttitel'] ?? '';
			$url              = $this->getPageLink();
			$fullLink         = $pLanguageSwitcher->createEstateDetailLink( $url, (int) $estate, $title );
			$fullLinkElements = parse_url( $fullLink );
			if ( empty( $fullLinkElements['query'] ) ) {
				$fullLink .= '/';
			}
		}

		return $fullLink;
	}
	
}