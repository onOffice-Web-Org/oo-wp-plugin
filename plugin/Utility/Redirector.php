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
	 * @param $pageId
	 * @param $estateId
	 * @param $estateTitle
	 *
	 * @return bool|void
	 */

	public function redirectDetailView( $pageId, $estateId, $estateTitle )
	{
		$currentLink = $this->getCurrentLink();
		$url         = $this->_wpPageWrapper->getPageLinkByPageId( $pageId );
		$fullLink    = $this->_wpEstateDetailUrl->createEstateDetailLink( $url, $estateId, $estateTitle, $currentLink );

		$uri      = $this->getUri();
		$pageName = $this->_wpPageWrapper->getPageUriByPageId( $pageId );
		preg_match( '/^(' . preg_quote( $pageName, '/' ) . ')\/([0-9]+)(-([^$]+)?)?\/?$/', $uri, $matches );
		if ( empty( $matches[2] ) ) { //Check pass rule and has Unique ID
			return true;
		}

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
