<?php

namespace onOffice\WPlugin\Utility;

use DI\ContainerBuilder;
use onOffice\WPlugin\Controller\EstateDetailUrl;
use onOffice\WPlugin\WP\WPPageWrapper;
use onOffice\WPlugin\WP\WPRedirectWrapper;

class Redirector
{
	/** @var EstateDetailUrl */
	private $_pLanguageSwitcher;
	private $_wpPageWrapper;
	private $_wpRedirectWrapper;

	public function __construct(EstateDetailUrl $estateDetailUrl, WPPageWrapper $pageWrapper, WPRedirectWrapper $redirectWrapper)
	{
		$this->_pLanguageSwitcher = $estateDetailUrl;
		$this->_wpPageWrapper = $pageWrapper;
		$this->_wpRedirectWrapper = $redirectWrapper;
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
}
