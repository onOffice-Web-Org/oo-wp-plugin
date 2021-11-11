<?php

namespace onOffice\WPlugin\Utility;

use DI\ContainerBuilder;
use onOffice\WPlugin\Controller\EstateDetailUrl;
use onOffice\WPlugin\WP\WPPageWrapper;

class RedirectIfOldUrl
{
	/** @var EstateDetailUrl */
	private $_pLanguageSwitcher;

	public function __construct()
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();
		$this->_pLanguageSwitcher = $pContainer->get(EstateDetailUrl::class);
	}

	public function redirectDetailView($pageId, $estateId, $estateTitle)
	{
		$currentLink = $this->getCurrentLink();
		$pageWrapper  = new WPPageWrapper();
		$pageName = $pageWrapper->getPageUriByPageId($pageId);
		$url = get_page_link($pageId);
		$fullLink = $this->_pLanguageSwitcher->createEstateDetailLink($url, $estateId, $estateTitle);
		$uri = $this->getUri();
		preg_match('/^(' . preg_quote($pageName) .')\/([0-9]+)(-([^$]+))?\/?$/', $uri, $matches);
		if (empty($matches[2])) { //Check pass rule and has Unique ID
			return true;
		}
		if ($this->_pLanguageSwitcher->isOptionShowTitleUrl() && $fullLink != $currentLink) {
			wp_redirect($fullLink, 301);
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
}
