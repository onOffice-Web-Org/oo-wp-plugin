<?php

namespace onOffice\WPlugin\Controller\Redirector;

use onOffice\WPlugin\Controller\EstateDetailUrl;
use onOffice\WPlugin\Utility\Redirector;
use onOffice\WPlugin\WP\WPRedirectWrapper;

class EstateRedirector
{
	/** @var EstateDetailUrl */
	private $_wpEstateDetailUrl;

	/** @var WPRedirectWrapper */
	private $_wpRedirectWrapper;

	/** @var Redirector */
	private $_redirector;


    /**
     * @param EstateDetailUrl $estateDetailUrl
     * @param WPRedirectWrapper $redirectWrapper
     */

	public function __construct( EstateDetailUrl $estateDetailUrl, WPRedirectWrapper $redirectWrapper)
	{
		$this->_wpEstateDetailUrl = $estateDetailUrl;
		$this->_wpRedirectWrapper = $redirectWrapper;
        $this->_redirector = new Redirector();
	}


	/**
	 * @param $estateId
	 * @param $estateTitle
	 * @param $pEstateRedirection
	 * @return bool|void
	 */

	public function redirectDetailView( $estateId, $estateTitle, $pEstateRedirection )
	{
		$matches = $this->_redirector->checkUrlIsMatchRule();
		if ( empty( $matches[2] ) ) {
			return true;
		}

		$oldUrl = $this->_redirector->getCurrentLink();
		$sanitizeTitle = $this->_wpEstateDetailUrl->getSanitizeTitle($estateTitle);
		$isUrlHaveTitle = strpos($oldUrl, $sanitizeTitle) !== false;
		$newUrl = $this->_wpEstateDetailUrl->getUrlWithEstateTitle($estateId, $estateTitle, $oldUrl, $isUrlHaveTitle, $pEstateRedirection);

		if ( $newUrl !== $oldUrl ) {
			$isNewUrlValid = $this->_redirector->checkNewUrlIsValid(
				array_filter( explode( '/', $newUrl ) ),
				array_filter( explode( '/', $oldUrl ) )
			);

			if ( $isNewUrlValid ) {
				$this->_wpRedirectWrapper->redirect( $newUrl );
			}
		}
	}
}
