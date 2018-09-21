<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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

namespace onOffice\WPlugin\Controller;

use onOffice\WPlugin\ContentFilter;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\Utility\__String;
use WP_Post;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class DetailViewPostSaveController
{
	/**
	 *
	 * @param int $postId
	 * @return null
	 *
	 */

	public function onSavePost($postId) {

		$pPost = WP_Post::get_instance($postId);
		if ($pPost->post_status === 'trash') {
			return;
		}

		$isRevision = wp_is_post_revision($pPost);

		if (!$isRevision) {
			$pDataDetailViewHandler = new DataDetailViewHandler();
			$pDetailView = $pDataDetailViewHandler->getDetailView();

			$detailViewName = $pDetailView->getName();
			$postContent = $pPost->post_content;
			$pContentFilter = new ContentFilter();
			$viewContained = $this->postContainsViewName($postContent, $detailViewName);

			if ($viewContained) {
				$pDetailView->setPageId($postId);
				DataDetailViewHandler::saveDetailView($pDetailView);
				$pContentFilter->addCustomRewriteRules();
				flush_rewrite_rules();

			} elseif ($pDetailView->getPageId() != null) {
				$postRevisions = wp_get_post_revisions($postId);
				$detailInPreviousRev = array_key_exists($pDetailView->getPageId(), $postRevisions);

				if ($detailInPreviousRev || $pDetailView->getPageId() === $postId) {
					$pDetailView->setPageId(null);
					DataDetailViewHandler::saveDetailView($pDetailView);
					$pContentFilter->addCustomRewriteRules();
					flush_rewrite_rules();
				}
			}
		}
	}


	/**
	 *
	 * If a post is moved to trash, it gets unpublished first.
	 * In case it contains the detail view name we need to remove the ID from the
	 * \onOffice\WPlugin\DataView\DataDetailView.
	 *
	 * @param int $postId
	 *
	 */

	public function onMoveTrash($postId) {
		$pDataDetailViewHandler = new DataDetailViewHandler();
		$pDetailView = $pDataDetailViewHandler->getDetailView();

		if ($pDetailView->getPageId() == $postId) {
			$pDetailView->setPageId(null);
			DataDetailViewHandler::saveDetailView($pDetailView);
			flush_rewrite_rules();
		}
	}


	/**
	 *
	 * @param string $detailViewName
	 * @return string
	 *
	 */

	private function generateDetailViewCode($detailViewName) {
		return 'view="'.$detailViewName.'"';
	}


	/**
	 *
	 * @param string $post
	 * @param string $viewName
	 * @return bool
	 *
	 */

	private function postContainsViewName($post, $viewName) {
		$matches = array();
		$regex = get_shortcode_regex(array('oo_estate'));
		preg_match_all('/'.$regex.'/ism', $post, $matches);

		$detailviewCode = $this->generateDetailViewCode($viewName);

		if (!array_key_exists(3, $matches)) {
			return false;
		}

		foreach ($matches[3] as $tagParams) {
			if (__String::getNew($tagParams)->contains($detailviewCode)) {
				return true;
			}
		}

		return false;
	}
}
