<?php

namespace onOffice\WPlugin\Utility;

use onOffice\WPlugin\Controller\EstateDetailUrl;
use onOffice\WPlugin\WP\WPPageWrapper;
use onOffice\WPlugin\WP\WPRedirectWrapper;

class Redirector
{
	/** @var EstateDetailUrl */
	private $_wpEstateDetailUrl;

	/** @var WPRedirectWrapper */
	private $_wpRedirectWrapper;


	/**
	 * @param  EstateDetailUrl  $estateDetailUrl
	 * @param  WPRedirectWrapper  $redirectWrapper
	 */

	public function __construct( EstateDetailUrl $estateDetailUrl, WPRedirectWrapper $redirectWrapper )
	{
		$this->_wpEstateDetailUrl = $estateDetailUrl;
		$this->_wpRedirectWrapper = $redirectWrapper;
	}


	/**
	 * @param $estateId
	 * @param $estateTitle
	 * @param $pEstateRedirection
	 * @return bool|void
	 */

	public function redirectDetailView( $estateId, $estateTitle, $pEstateRedirection )
	{
		$matches = $this->checkUrlIsMatchRule();
		if ( empty( $matches[2] ) ) {
			return true;
		}

		$oldUrl = $this->getCurrentLink();
		$sanitizeTitle = $this->_wpEstateDetailUrl->getSanitizeTitle($estateTitle);
		$isUrlHaveTitle = strpos($oldUrl, $sanitizeTitle) !== false;
		$newUrl = $this->_wpEstateDetailUrl->getUrlWithEstateTitle($estateId, $estateTitle, $oldUrl, $isUrlHaveTitle, $pEstateRedirection);

		if ( $newUrl !== $oldUrl ) {
			$isNewUrlValid = $this->checkNewUrlIsValid(
				array_filter( explode( '/', $newUrl ) ),
				array_filter( explode( '/', $oldUrl ) )
			);

			if ( $isNewUrlValid ) {
				$this->_wpRedirectWrapper->redirect( $newUrl );
			}
		}
	}


	/**
	 * @return mixed
	 */

	public function checkUrlIsMatchRule()
	{
		$uri        = $this->getUri();
		$uriToArray = explode( '/', $uri );
		array_pop( $uriToArray );
		$pagePath = implode( '/', array_filter( $uriToArray ) );

		//Check pass rule and has Unique ID
		preg_match( '/^(' . preg_quote( $pagePath,
				'/' ) . ')\/([0-9]+)(-([^$]+)?)?\/?$/', $uri, $matches );

		return $matches;
	}


	/**
	 * @param  array  $newUrlArr
	 * @param  array  $oldUrlArr
	 *
	 * @return bool
	 */

	public function checkNewUrlIsValid( array $newUrlArr, array $oldUrlArr )
	{
		if ( end( $newUrlArr ) !== end( $oldUrlArr ) ) {
			array_pop( $newUrlArr );
			array_pop( $oldUrlArr );

			return empty( array_diff( $newUrlArr, $oldUrlArr ) );
		}

		return false;
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
		return home_url($_SERVER['REQUEST_URI']);
	}
}
