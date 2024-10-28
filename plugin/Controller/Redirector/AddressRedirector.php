<?php

namespace onOffice\WPlugin\Controller\Redirector;

use onOffice\WPlugin\Controller\AddressDetailUrl;
use onOffice\WPlugin\Utility\Redirector;
use onOffice\WPlugin\WP\WPRedirectWrapper;

class AddressRedirector
{
	/** @var AddressDetailUrl */
	private $_wpAddressDetailUrl;

	/** @var WPRedirectWrapper */
	private $_wpRedirectWrapper;

    /** @var Redirector */
    private $_redirector;


	/**
	 * @param AddressDetailUrl $addressDetailUrl
	 * @param WPRedirectWrapper $redirectWrapper
	 */

	public function __construct(AddressDetailUrl $addressDetailUrl, WPRedirectWrapper $redirectWrapper)
	{
		$this->_wpAddressDetailUrl = $addressDetailUrl;
		$this->_wpRedirectWrapper = $redirectWrapper;
        $this->_redirector = new Redirector();
    }


	/**
	 * @param int $addressId
	 * @param string $addressTitle
	 * @param bool $pAddressRedirection
	 * @return bool|void
	 */

	public function redirectDetailView(int $addressId, string $addressTitle, bool $pAddressRedirection)
	{
		$matches = $this->_redirector->checkUrlIsMatchRule();
		if (empty($matches[2])) {
			return true;
		}

		$oldUrl = $this->_redirector->getCurrentLink();
		$sanitizeTitle = $this->_wpAddressDetailUrl->getSanitizeTitle($addressTitle);
		$isUrlHaveTitle = strpos($oldUrl, $sanitizeTitle) !== false;
		$newUrl = $this->_wpAddressDetailUrl->getUrlWithAddressTitle($addressId, $addressTitle, $oldUrl, $isUrlHaveTitle, $pAddressRedirection);
		if ($newUrl !== $oldUrl) {
			$isNewUrlValid = $this->_redirector->checkNewUrlIsValid(
				array_filter(explode('/', $newUrl)),
				array_filter(explode('/', $oldUrl))
			);

			if ($isNewUrlValid) {
				$this->_wpRedirectWrapper->redirect($newUrl);
			}
		}
	}
}
