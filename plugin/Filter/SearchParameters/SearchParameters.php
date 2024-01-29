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

use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\Controller\SearchParametersModelBuilderEstate;
use onOffice\WPlugin\Controller\SortList\SortListDataModel;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Field\UnknownFieldException;
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
	/**
	 * Generates a pagelink for pagination with given parameters as GET Request
	 *
	 * partly taken from wp_link_pages() and _wp_link_page()
	 *
	 * @param string $link
	 * @param int $i
	 * @param SearchParametersModel $pModel
	 * @param int $pListViewId
	 * @param bool $pCheckPaginationTheme
	 * @return string
	 * @global int $page
	 * @global bool $more
	 */
	public function linkPagesLink(string $link, int $i, SearchParametersModel $pModel, int $pListViewId=0, bool $pCheckPaginationTheme=false): string
	{
		global $paged, $more;

		$linkparams = $pModel->getDefaultLinkParams();
		$output = '';

		if ('number' == $linkparams['next_or_number']) {
			$link = $linkparams['link_before'].str_replace('%', $i, $linkparams['pagelink'])
				.$linkparams['link_after'];
			if ($i != $paged || ! $more && 1 == $paged) {
				$url = !$pCheckPaginationTheme && !empty($pListViewId) ? 
					$this->getUrlByListViewId($i, $pModel->getParameters(), $pListViewId) : 
					$this->geturl($i, $pModel->getParameters());
				$output .= '<a href="'.esc_url($url).'">'.$link.'</a>';
			} else {
				$output .= $link;
			}
		} elseif ($more) {
			$output .= $this->getLinkSnippetForPage($i, $paged, $linkparams, $pModel);
		}

		return $output;
	}

	/**
	 * @param int $i
	 * @param int $page
	 * @param array $linkparams
	 * @param  SearchParametersModel $pModel
	 * @return string
	 */
	private function getLinkSnippetForPage(int $i, int $page, array $linkparams, SearchParametersModel $pModel): string
	{
		$key = $i < $page ? 'previouspagelink' : 'nextpagelink';

		return '<a href="'.esc_url($this->geturl($i, $pModel->getParameters())).'">'
			.$linkparams['link_before'].$linkparams[$key]
			.$linkparams['link_after'].'</a>';
	}

	/**
	 * @param int $i
	 * @param array $parameters
	 * @return string
	 */
	private function geturl($i, array $parameters): string
	{
		$url = trailingslashit(get_permalink()).user_trailingslashit('page/'.$i, 'single_paged');
		return add_query_arg($parameters, $url);
	}

	/**
	 * @param int $i
	 * @param array $parameters
	 * @param int $id
	 * @return string
	 */
	private function getUrlByListViewId(int $i, array $parameters, int $pListViewId): string
	{
		$url = get_permalink();
		$parameter = $parameters;
		$parameter['page_of_id_'.$pListViewId] = $i;
		return add_query_arg($parameter, $url);
	}

	/**
	 * @param SearchParametersModel $pSearchParametersModel
	 */
	public function registerNewPageLinkArgs(SearchParametersModel $pSearchParametersModel, $pListViewId, $pCheckPaginationTheme)
	{
		add_filter('wp_link_pages_link', function(string $link, int $i) use ($pSearchParametersModel, $pListViewId, $pCheckPaginationTheme): string {
			return $this->linkPagesLink($link, $i, $pSearchParametersModel, $pListViewId, $pCheckPaginationTheme);
		}, 10, 2);
		add_filter('wp_link_pages_args', [$pSearchParametersModel, 'populateDefaultLinkParams']);
	}
}