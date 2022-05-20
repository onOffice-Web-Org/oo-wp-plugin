<?php

namespace onOffice\WPlugin\Utility;

use onOffice\WPlugin\Controller\EstateDetailUrl;
use onOffice\WPlugin\WP\WPPageWrapper;
use onOffice\WPlugin\WP\WPRedirectWrapper;

class Redirector
{
	/** @var EstateDetailUrl */
	private $_wpEstateDetailUrl;

	/** @var WPPageWrapper */
	private $_wpPageWrapper;

	/** @var WPRedirectWrapper */
	private $_wpRedirectWrapper;


	/**
	 * @param EstateDetailUrl $estateDetailUrl
	 * @param WPPageWrapper $pageWrapper
	 * @param WPRedirectWrapper $redirectWrapper
	 */

	public function __construct(EstateDetailUrl $estateDetailUrl, WPPageWrapper $pageWrapper, WPRedirectWrapper $redirectWrapper)
	{
		$this->_wpEstateDetailUrl = $estateDetailUrl;
		$this->_wpPageWrapper = $pageWrapper;
		$this->_wpRedirectWrapper = $redirectWrapper;
	}


	/**
	 * @param $estateId
	 * @param $estateTitle
	 *
	 * @return void
	 */

	public function redirectDetailView( $estateId, $estateTitle )
	{
		$currentLink = $this->getCurrentLink();
		$fullLink    = $this->_wpEstateDetailUrl->getUrlWithEstateTitle( $estateId, $estateTitle, $currentLink );

		if ( $fullLink !== $currentLink ) {
			$this->_wpRedirectWrapper->redirect( $fullLink );
		}
	}


	/**
	 * @return mixed
	 */

	public function getUri()
	{
		global $wp;

		return $wp->request;
	}


	/**
	 * @return string
	 */

	public function getCurrentLink(): string
	{
		global $wp;

		return home_url( add_query_arg( array(), $wp->request ) );
	}
}
