<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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


namespace onOffice\WPlugin\Filter\SearchParameters;

use onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel;

use function add_query_arg;
use function esc_url;
use function get_permalink;
use function trailingslashit;
use function user_trailingslashit;

/**
 *
 * class that holds submitted get parameters for the pagination
 *
 */

class SearchParameters
{
	/** @var array */
	private $_parameters = array();

	/** @var array */
	private $_defaultLinkParams = array();

	/** @var array */
	private $_allowedGetParameters = array();

	/** @var bool */
	private $_filter = true;



	/**
	 *
	 * @param SearchParametersModel $pModel
	 *
	 */

	public function __construct(SearchParametersModel $pModel)
	{
		$this->_parameters  = $pModel->getParameters();
		$this->_allowedGetParameters = $pModel->getAllowedGetParameters();
		$this->_filter = $pModel->getFilter();
	}

	/**
	 *
	 * @param array $params
	 * @return array
	 *
	 */

	public function populateDefaultLinkParams($params): array
	{
		$this->_defaultLinkParams = $params;
		return $params;
	}


	/** @return array */
	public function getDefaultLinkParameters(): array
		{ return $this->_defaultLinkParams; }


	/**
	 *
	 * Generates a pagelink for pagination with given parameters as GET Request
	 *
	 * partly taken from wp_link_pages() and _wp_link_page()
	 *
	 * @global int $page
	 * @global bool $more
	 * @param string $link
	 * @param int $i
	 * @return string
	 *
	 */

	public function linkPagesLink($link, $i = 1): string
	{
		global $page, $more;

		$linkparams = $this->_defaultLinkParams;
		$output = '';

		if ('number' == $linkparams['next_or_number']) {
			$link = $linkparams['link_before'].str_replace('%', $i, $linkparams['pagelink'])
				.$linkparams['link_after'];
			if ($i != $page || ! $more && 1 == $page) {
				$url = $this->geturl( $i );
				$output .= '<a href="'.esc_url($url).'">'.$link.'</a>';
			} else {
				$output .= $link;
			}
		} elseif ($more) {
			$output .= $this->getLinkSnippetForPage($i, $page);
		}

		return $output;
	}


	/**
	 *
	 * @param int $i
	 * @param int $page
	 * @return string
	 *
	 */

	private function getLinkSnippetForPage($i, $page): string
	{
		$linkparams = $this->_defaultLinkParams;

		$key = $i < $page ? 'previouspagelink' : 'nextpagelink';

		return '<a href="'.esc_url($this->geturl($i)).'">'
			.$linkparams['link_before'].$linkparams[$key]
			.$linkparams['link_after'].'</a>';
	}


	/**
	 *
	 * @param int $i
	 * @return string
	 *
	 */

	public function geturl($i): string
	{
		$url = trailingslashit(get_permalink()).user_trailingslashit($i, 'single_paged');
		return add_query_arg($this->_parameters, $url);
	}
}