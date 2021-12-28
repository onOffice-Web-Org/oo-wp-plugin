<?php

namespace onOffice\WPlugin\Utility;

use DI\ContainerBuilder;
use onOffice\WPlugin\Controller\EstateDetailUrl;
use onOffice\WPlugin\WP\WPPageWrapper;
use onOffice\WPlugin\WP\WPRedirectWrapper;

class RedirectIfOldUrl
{
	/** @var EstateDetailUrl */
	private $_pLanguageSwitcher;
	private $_wpPageWrapper;
	private $_wpRedirectWrapper;

	public function __construct()
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();
		$this->_pLanguageSwitcher = $pContainer->get(EstateDetailUrl::class);
		$this->_wpPageWrapper = $pContainer->get(WPPageWrapper::class);
		$this->_wpRedirectWrapper = $pContainer->get(WPRedirectWrapper::class);
	}

	public function redirectDetailView($pageId, $estateId, $estateTitle)
	{
		$currentLink = $this->getCurrentLink();
		$url =  $this->_wpPageWrapper->getPageLinkByPageId($pageId);
		$fullLink = $this->_pLanguageSwitcher->createEstateDetailLink($url, $estateId, $estateTitle);
		$uri = $this->getUri();
		$pageName = $this->_wpPageWrapper->getPageUriByPageId($pageId);
		preg_match('/^(' . preg_quote($pageName) .')\/([0-9]+)(-([^$]+))?\/?$/', $uri, $matches);
		if (empty($matches[2])) { //Check pass rule and has Unique ID
			return true;
		}
		if ($fullLink != $currentLink) {
			$this->_wpRedirectWrapper->redirect($fullLink);
			exit;
		}

		return true;
	}

	public function getUri()
	{
		global $wp;
		return $wp->request;
	}

	public function getCurrentLink(): string
	{
		global $wp;
		return home_url(add_query_arg(array(), $wp->request));
	}

	/**
	 * @param EstateDetailUrl $pLanguageSwitcher
	 */
	public function setPLanguageSwitcher($pLanguageSwitcher)
	{
		$this->_pLanguageSwitcher = $pLanguageSwitcher;
	}

	/**
	 * @param mixed|WPRedirectWrapper $wpRedirectWrapper
	 */
	public function setWpRedirectWrapper($wpRedirectWrapper)
	{
		$this->_wpRedirectWrapper = $wpRedirectWrapper;
	}

	/**
	 * @param mixed|WPPageWrapper $wpPageWrapper
	 */
	public function setWpPageWrapper($wpPageWrapper)
	{
		$this->_wpPageWrapper = $wpPageWrapper;
	}
}
