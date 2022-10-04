<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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

declare(strict_types=1);

namespace onOffice\WPlugin\Filter;
use function is_user_logged_in;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Controller\EstateListEnvironmentDefault;
use onOffice\WPlugin\Controller\EstateDetailUrl;
use DI\ContainerBuilder;

use Exception;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class DefaultFilterBuilderDetailView
	implements DefaultFilterBuilder
{
	/** @var int */
	private $_estateId = 0;


	/**
	 * @return string
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function getEstateLink($pEstateListDetail): string
	{
		$pLanguageSwitcher =  new EstateDetailUrl;
		$pageId = $pEstateListDetail['id'];
		$fullLink = '#';

		if ( $pageId !== 0 ) {
			$estate   = $pEstateListDetail['id'];
			$url      = get_page_link( $pageId );
			$fullLink = $pLanguageSwitcher->createEstateDetailLink( $url, (int)$estate);
			$fullLinkElements = parse_url( $fullLink );
			if ( empty( $fullLinkElements['query'] ) ) {
				$fullLink .= '/';
			}
		}

		return $fullLink;
	}

	/**
	 *
	 * @return array
	 *
	 */

	public function buildFilter(): array
	{
		if ($this->_estateId === 0) {
			$pContainerBuilder = new ContainerBuilder;
			$pContainerBuilder->addDefinitions( ONOFFICE_DI_CONFIG_PATH );
			$pContainer                     = $pContainerBuilder->build();
			$pEnvironment                   = new EstateListEnvironmentDefault( $pContainer );
			$pSDKWrapper                    = $pEnvironment->getSDKWrapper();
			$pApiClientAction               = new APIClientActionGeneric
			( $pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'estate' );
			$estateParametersRaw['data']    = $pEnvironment->getEstateStatusLabel()->getFieldsByPrio();
			$estateParametersRaw['data'] [] = 'vermarktungsart';
			$pApiClientAction->setParameters( $estateParametersRaw );
			$pApiClientAction->addRequestToQueue()->sendRequests();
			$pEstateList = $pApiClientAction->getResultRecords();

			$pEstateDetail = [];
			foreach ( $pEstateList as $pEstateListDetails ) {
				$referenz      = $pEstateListDetails['elements']['referenz'];
				$marketingType = $pEstateListDetails['elements']['vermarktungsart'];
				if ( $referenz === '0' && $marketingType != '' ) {
					$pEstateDetail[] = $pEstateListDetails;
				};
			}
			$randomIdDetail = array_rand( $pEstateDetail, 1 );
			$url     = $this->getEstateLink( $pEstateDetail[ $randomIdDetail ] );

			echo '<div>';
			echo '<div>' . esc_html_e( 'You have opened the detail page, but we do not know which estate to show you, because there is no estate ID in the URL. Please go to an estate list and open an estate from there.',
					'onoffice-for-wp-websites' ) . '</div>';
			if ( is_user_logged_in() ) {
				echo '<div>' . esc_html_e( 'Since you are logged in, here is a link to a random estate so that you can preview the detail page:',
						'onoffice-for-wp-websites' ) . '</div>';
				echo '<a href=' . $url . '>' . esc_html( __('Beautiful home with great view', 'onoffice-for-wp-websites') ) . '</a>';
			}
			echo '</div>';
			die();
		}

		return [
			'veroeffentlichen' => [
				['op' => '=', 'val' => 1],
			],
			'Id' => [
				['op' => '=', 'val' => $this->_estateId],
			],
		];
	}

	/** @param int $estateId */
	public function setEstateId(int $estateId)
		{ $this->_estateId = $estateId; }

	/** @return int $estateId */
	public function getEstateId(): int
		{ return $this->_estateId; }
}
