<?php

namespace onOffice\WPlugin\Utility;

use onOffice\WPlugin\Controller\AddressDetailUrl;
use onOffice\WPlugin\WP\WPPageWrapper;
use onOffice\WPlugin\WP\WPRedirectWrapper;

class AddressRedirector
{
	/** @var AddressDetailUrl */
	private $_wpAddressDetailUrl;

	/** @var WPRedirectWrapper */
	private $_wpRedirectWrapper;


	/**
	 * @param  AddressDetailUrl  $addressDetailUrl
	 * @param  WPRedirectWrapper  $redirectWrapper
	 */

	public function __construct( AddressDetailUrl $addressDetailUrl, WPRedirectWrapper $redirectWrapper )
	{
		$this->_wpAddressDetailUrl = $addressDetailUrl;
		$this->_wpRedirectWrapper = $redirectWrapper;
	}


	/**
	 * @param $addressId
	 * @param $addressTitle
	 * @param $pAddressRedirection
	 * @return bool|void
	 */

	public function redirectDetailView( $addressId, $addressTitle, $pAddressRedirection )
	{
		$matches = $this->checkUrlIsMatchRule();
		if ( empty( $matches[2] ) ) {
			return true;
		}

		$oldUrl = $this->getCurrentLink();
		$sanitizeTitle = $this->_wpAddressDetailUrl->getSanitizeTitle($addressTitle);
		$isUrlHaveTitle = strpos($oldUrl, $sanitizeTitle) !== false;
		$newUrl = $this->_wpAddressDetailUrl->getUrlWithAddressTitle($addressId, $addressTitle, $oldUrl, $isUrlHaveTitle, $pAddressRedirection);
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
		global $wp;
		return home_url(add_query_arg($_GET, $wp->request));
	}
}
