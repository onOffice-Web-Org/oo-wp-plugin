<?php

/**
 *
 *    Copyright (C) 2020 onOffice GmbH
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

namespace onOffice\WPlugin\Controller;

use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\WP\WPPageWrapper;
use onOffice\WPlugin\DataView\DataAddressDetailViewHandler;
use onOffice\WPlugin\WP\WPPluginChecker;

class RewriteRuleBuilder
{
	/** @var DataDetailViewHandler */
	private $_pDataDetailViewHandler;

	/** @var WPPageWrapper */
	private $_pWPPageWrapper;

	/** @var DataAddressDetailViewHandler */
	private $_pDataAddressDetailViewHandler;

	/** @var WPPluginChecker */
	private $_pWPPluginChecker;

	/**
	 * @param DataDetailViewHandler $pDataDetailViewHandler
	 * @param WPPageWrapper $pWPPageWrapper
	 * @param DataAddressDetailViewHandler $pDataAddressDetailViewHandler
	 * @param WPPluginChecker $pWPPluginChecker
	 */
	public function __construct(
		DataDetailViewHandler $pDataDetailViewHandler,
		WPPageWrapper $pWPPageWrapper,
		DataAddressDetailViewHandler $pDataAddressDetailViewHandler,
		WPPluginChecker $pWPPluginChecker = null)
	{
		$this->_pDataDetailViewHandler = $pDataDetailViewHandler;
		$this->_pWPPageWrapper = $pWPPageWrapper;
		$this->_pDataAddressDetailViewHandler = $pDataAddressDetailViewHandler;
		$this->_pWPPluginChecker = $pWPPluginChecker ?? new WPPluginChecker();
	}

	public function addCustomRewriteTags()
	{
		add_rewrite_tag('%estate_id%', '([^&]+)');
		add_rewrite_tag('%view%', '([^&]+)');
	}

	public function addStaticRewriteRules()
	{
		add_rewrite_rule('^onoffice-estate-types.json$', 'index.php?onoffice_estate_type_json=1', 'top');
		add_rewrite_rule('^onoffice-estate-preview/?$', 'index.php?onoffice_estate_preview=1', 'top');
		add_rewrite_rule('^onoffice-applicant-search-preview/?$', 'index.php?onoffice_applicant_search_preview=1', 'top');
		add_rewrite_rule('^document-pdf/([^\/]+)/([0-9]+)/?$',
			'index.php?document_pdf=1&view=$matches[1]&estate_id=$matches[2]', 'top');
	}

	public function addDynamicRewriteRules()
	{
		$detailPageIds = $this->_pDataDetailViewHandler->getDetailView()->getPageIdsHaveDetailShortCode();
		$this->setCanonicalUrlFromRequest($detailPageIds);
		foreach ( $detailPageIds as $detailPageId ) {
			$pageName = $this->_pWPPageWrapper->getPageUriByPageId( $detailPageId );
			add_rewrite_rule( '^(' . preg_quote( $pageName ) . ')/([0-9]+)(-([^$]+)?)?/?$',
				'index.php?pagename=' . urlencode( $pageName ) . '&view=$matches[1]&estate_id=$matches[2]', 'top' );
		}
	}

	private function setCanonicalUrlFromRequest(array $pageIds)
	{
		$canonicalFilters = [
			'get_canonical_url',                    // WordPress default
			'wpseo_canonical',                      // Yoast SEO
			'rank_math/frontend/canonical',         // Rank Math SEO
			'aioseo_canonical_url',                 // All in One SEO
			'seopress_titles_canonical',            // SEOPress
			'the_seo_framework_canonical_url',      // The SEO Framework
		];

		foreach ($canonicalFilters as $filter) {
			add_filter($filter, function($url) use ($pageIds) {
				if (in_array(get_the_ID(), $pageIds) && isset($_SERVER['REQUEST_URI'])) {
					return home_url($_SERVER['REQUEST_URI']);
				}
				return $url;
			}, 20, 1);
		}
	}

	public function addCustomRewriteTagsForAddressDetail()
	{
		add_rewrite_tag('%address_id%', '([^&]+)');
		add_rewrite_tag('%view%', '([^&]+)');
	}

	public function addDynamicRewriteRulesForAddressDetail()
	{
		$detailPageIds = $this->_pDataAddressDetailViewHandler->getAddressDetailView()->getPageIdsHaveDetailShortCode();
		foreach ( $detailPageIds as $detailPageId ) {
			$pageName = $this->_pWPPageWrapper->getPageUriByPageId( $detailPageId );
			add_rewrite_rule( '^(' . preg_quote( $pageName ) . ')/([0-9]+)(-([^$]+)?)?/?$',
				'index.php?pagename=' . urlencode( $pageName ) . '&view=$matches[1]&address_id=$matches[2]', 'top' );
		}
	}
}