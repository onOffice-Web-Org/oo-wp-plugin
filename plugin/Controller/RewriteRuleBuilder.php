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

class RewriteRuleBuilder
{
	/** @var DataDetailViewHandler */
	private $_pDataDetailViewHandler;

	/** @var WPPageWrapper */
	private $_pWPPageWrapper;

	/** @var DataAddressDetailViewHandler */
	private $_pDataAddressDetailViewHandler;

	/**
	 * @param DataDetailViewHandler $pDataDetailViewHandler
	 * @param WPPageWrapper $pWPPageWrapper
	 * @param DataAddressDetailViewHandler $pDataAddressDetailViewHandler
	 */
	public function __construct(
		DataDetailViewHandler $pDataDetailViewHandler,
		WPPageWrapper $pWPPageWrapper,
		DataAddressDetailViewHandler $pDataAddressDetailViewHandler)
	{
		$this->_pDataDetailViewHandler = $pDataDetailViewHandler;
		$this->_pWPPageWrapper = $pWPPageWrapper;
		$this->_pDataAddressDetailViewHandler = $pDataAddressDetailViewHandler;
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
		foreach ( $detailPageIds as $detailPageId ) {
			$pageName = $this->_pWPPageWrapper->getPageUriByPageId( $detailPageId );
			add_rewrite_rule( '^(' . preg_quote( $pageName ) . ')/([0-9]+)(-([^$]+)?)?/?$',
				'index.php?pagename=' . urlencode( $pageName ) . '&view=$matches[1]&estate_id=$matches[2]', 'top' );
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