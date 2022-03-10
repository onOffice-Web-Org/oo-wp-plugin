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

use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\Utility\__String;
use onOffice\WPlugin\Record\RecordManagerReadListViewEstate;
use onOffice\WPlugin\Record\RecordManagerReadListViewAddress;
use onOffice\WPlugin\Record\RecordManagerReadForm;

use WP_Post;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class DetailViewPostSaveController
{
	/** @var RewriteRuleBuilder */
	private $_pRewriteRuleBuilder;


	/**
	 *
	 * @param RewriteRuleBuilder $pRewriteRuleBuilder
	 *
	 */

	public function __construct(RewriteRuleBuilder $pRewriteRuleBuilder)
	{
		$this->_pRewriteRuleBuilder = $pRewriteRuleBuilder;
	}


	/**
	 *
	 * @param int $postId
	 * @return void
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
			$viewContained = $this->postContainsViewName($postContent, $detailViewName);

			if ($viewContained) {
				$pDetailView->setPageId((int)$postId);
				$pDataDetailViewHandler->saveDetailView($pDetailView);
				$this->_pRewriteRuleBuilder->addDynamicRewriteRules();
				flush_rewrite_rules();

			} elseif ($pDetailView->getPageId() !== 0) {
				$postRevisions = wp_get_post_revisions($postId);
				$detailInPreviousRev = array_key_exists($pDetailView->getPageId(), $postRevisions);

				if ($detailInPreviousRev || $pDetailView->getPageId() === $postId) {
					$pDetailView->setPageId(0);
					$pDataDetailViewHandler->saveDetailView($pDetailView);
					$this->_pRewriteRuleBuilder->addDynamicRewriteRules();
					flush_rewrite_rules();
				}
			}
			$this->addPageUseShortCode($pPost);
		}

	}
	public function getAllPost() {
		$args = array(
			'post_status' => 'publish',
		);
		$posts = get_pages($args);
		foreach ($posts as $post)
		{
			$this->addPageUseShortCode($post);
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

		if ($pDetailView->getPageId() === (int)$postId) {
			$pDetailView->setPageId(0);
			$pDataDetailViewHandler->saveDetailView($pDetailView);
			flush_rewrite_rules();
		}
		$pPost = get_post($postId);
		$this->deletePageUseShortCode($pPost);
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

	private function generateDetailCode($detailViewName,$option) {
		return $option.'="'.$detailViewName.'"';
	}
	private function getListView()
	{
		$pRecordReadListView = new RecordManagerReadListViewEstate();
		$pRecordReadListView->addColumn('listview_id');
		$pRecordReadListView->addColumn('name');
		$pRecordReadListView->addColumn('page_shortcode');
		$listView = $pRecordReadListView->getRecords();
		return $listView;
	}
	private function getListViewAddress()
	{
		$pRecordReadListViewAddress = new RecordManagerReadListViewAddress();
		$pRecordReadListViewAddress->addColumn('listview_address_id');
		$pRecordReadListViewAddress->addColumn('name');
		$pRecordReadListViewAddress->addColumn('page_shortcode');
		$listViewAddress = $pRecordReadListViewAddress->getRecords();
		return $listViewAddress;
	}
	private function getListForm()
	{
		$pRecordReadForm = new RecordManagerReadForm();
		$pRecordReadForm->addColumn('form_id');
		$pRecordReadForm->addColumn('name');
		$pRecordReadForm->addColumn('page_shortcode');
		$listForm = $pRecordReadForm->getRecords();
		return $listForm;
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

	private function postContains($post, $viewName, $element,$option) {
		$matches = array();
		$regex = get_shortcode_regex(array($element));
		preg_match_all('/'.$regex.'/ism', $post, $matches);

		$detailviewCode = $this->generateDetailCode($viewName,$option);
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

	private function addPageUseShortCode($post)
	{
		$listView = $this->getListView();
		$listViewAddress = $this->getListViewAddress();
		$listForm = $this->getListForm();
		$isRevision = wp_is_post_revision($post);
		if (!$isRevision) {
			$postContent = $post->post_content;
			$postID = $post->ID;
			if (empty($postID))
			{
				return;
			}
			if (strpos($postContent,'oo_estate') !== false)
			{
				$this->addPageShortCode($listView,$post,'oo_estate','view',"oo_plugin_listviews","listview_id","listview_id");
			}
			if (strpos($postContent,'oo_address') !== false)
			{
				$this->addPageShortCode($listViewAddress,$post,'oo_address','view',"oo_plugin_listviews_address","listview_address_id","listview_address_id");
			}
			if (strpos($postContent,'oo_form') !== false)
			{
				$this->addPageShortCode($listForm,$post,'oo_form','form',"oo_plugin_forms","form_id","form_id");
			}

		}
	}
	private function deletePageUseShortCode($post)
	{
		$listView = $this->getListView();
		$listViewAddress = $this->getListViewAddress();
		$listForm = $this->getListForm();
		$isRevision = wp_is_post_revision($post);

		if (!$isRevision) {
			$postContent = $post->post_content;
			$postID = $post->ID;
			if (empty($postID))
			{
				return;
			}
			if (strpos($postContent,'oo_estate') !== false)
			{
				$this->deletePageShortCode($listView,$post,"oo_plugin_listviews","listview_id","listview_id");
			}
			if (strpos($postContent,'oo_address') !== false)
			{
				$this->deletePageShortCode($listViewAddress,$post,"oo_plugin_listviews_address","listview_address_id","listview_address_id");
			}
			if (strpos($postContent,'oo_form') !== false)
			{
				$this->deletePageShortCode($listForm,$post,"oo_plugin_forms","form_id","form_id");
			}

		}
	}
	private function addPageShortCode($listView,$post,$element,$option,$tableName,$column,$primaKey)
	{
		$pRecordReadListView = new RecordManagerReadListViewEstate();
		$postID = $post->ID;
		$postContent = $post->post_content;
		foreach($listView as $view)
		{
			$pageID = [];
			if (!empty($view->page_shortcode))
			{
				$pageID = explode(',',$view->page_shortcode);
			}
			if (in_array($postID,$pageID))
			{
				break;
			}
			$viewContained = $this->postContains($postContent, $view->name,$element,$option);
			if ($viewContained) {
				if (empty($pageID))
				{
					$pageID = $postID;
				}else {
					$pageID[] = $postID;
					$pageID = implode(",",$pageID);
				}
				$pRecordReadListView->updateColumnPageShortCode($pageID,$view->$primaKey,$tableName,$column);
			}
		}
	}
	private function deletePageShortCode($listView,$post,$tableName,$column,$primaKey)
	{
		$pRecordReadListView = new RecordManagerReadListViewEstate();
		$postID = $post->ID;
		foreach($listView as $view)
		{
			$pageID = '';
			if (empty($view->page_shortcode))
			{
				continue;
			}
			if (strpos($view->page_shortcode,(string)$postID)!== false)
			{
				$pageShortCode = explode(",",$view->page_shortcode);
				if (($keyPageDelete = array_search($postID, $pageShortCode)) !== false) {
					unset($pageShortCode[$keyPageDelete]);
				}
				$pageID = implode(",",$pageShortCode);
				$pRecordReadListView->updateColumnPageShortCode($pageID,$view->$primaKey,$tableName,$column);
			}

		}
	}
}
