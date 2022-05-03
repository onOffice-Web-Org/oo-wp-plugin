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

declare (strict_types=1);

namespace onOffice\WPlugin\WP;

use WP_Post;
use function get_page_by_path;

/**
 *
 */

class WPPageWrapper
{
	/**
	 *
	 * @param string $path
	 * @return WP_Post
	 * @throws UnknownPageException
	 *
	 */

	public function getPageByPath(string $path): WP_Post
	{
		$pPost = get_page_by_path($path);
		if ($pPost === null) {
			throw new UnknownPageException($path);
		}
		return $pPost;
	}


	/**
	 *
	 * @param WP_Post $pPost
	 * @return string
	 *
	 */

	public function getPageLinkByPost(WP_Post $pPost): string
	{
		return get_permalink($pPost);
	}

	public function getPageLinkById(int $pageId): string
	{
		return get_permalink($pageId);
	}

	public function getPageUriByPageId(int $pageId): string
	{
		return get_page_uri($pageId) ?: '';
	}
}
