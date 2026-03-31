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

use DI\Container;
use DI\ContainerBuilder;
use Exception;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\Utility\__String;
use onOffice\WPlugin\Record\RecordManagerReadListViewEstate;
use onOffice\WPlugin\Record\RecordManagerReadListViewAddress;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\DataView\DataAddressDetailViewHandler;

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

	/** @var Container */
	private $_pContainer;

	/** @var RecordManagerReadListViewEstate */
	private $_pRecordReadListView;

	const LIST_CONFIGS = [
		'estate'  => [
			"option" => "view",
			"tableName" => "oo_plugin_listviews",
			"key" => "listview_id",
		],
		'address' => [
			"option" => "view",
			"tableName" => "oo_plugin_listviews_address",
			"key" => "listview_address_id",
		],
		'form'    => [
			"option" => "form",
			"tableName" => "oo_plugin_forms",
			"key" => "form_id",
		],
	];

	/**
	 *
	 * @param RewriteRuleBuilder $pRewriteRuleBuilder
	 *
	 * @throws Exception
	 */

	public function __construct(RewriteRuleBuilder $pRewriteRuleBuilder)
	{
		$this->_pRewriteRuleBuilder = $pRewriteRuleBuilder;
		$pDIContainerBuilder = new ContainerBuilder;
		$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pDIContainerBuilder->build();
		$this->_pRecordReadListView = $this->_pContainer->get(RecordManagerReadListViewEstate::class);
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
			$pDataDetailViewHandler = $this->_pContainer->get(DataDetailViewHandler::class);
			$pDetailView = $pDataDetailViewHandler->getDetailView();
			$pDataAddressDetailViewHandler = $this->_pContainer->get(DataAddressDetailViewHandler::class);
			$pAddressDetailView = $pDataAddressDetailViewHandler->getAddressDetailView();

			$detailViewName = $pDetailView->getName();
			$addressDetailViewName = $pAddressDetailView->getName();
			$postContent = $pPost->post_content;
			$postType = $pPost->post_type;
			$metaKeys = get_post_meta($postId, '', true);

			$viewContained = !is_null($postContent) ? $this->postContainsViewName($postContent, $detailViewName) : false;
			$viewContainedAddressDetail = !is_null($postContent) ? $this->postContainsViewName($postContent, $addressDetailViewName, 'oo_address') : false;

			$viewContainedCustomField = false;
			$hasOtherShortcodeInPostContent = false;
			$viewContainedAddressDetailCustomField = false;
			$hasOtherAddressDetailShortcodeInPostContent = false;

			if (!$viewContained && !empty($postContent) && $this->checkOtherShortcodeInPostContent($postContent, $detailViewName)) {
				$hasOtherShortcodeInPostContent = true;
			}

			if (!$viewContainedAddressDetail && !empty($postContent) && $this->checkOtherShortcodeInPostContent($postContent, $addressDetailViewName, 'oo_address')) {
				$hasOtherAddressDetailShortcodeInPostContent = true;
			}

			foreach ($metaKeys as $metaKey) {
				$viewContainedMetaKey = !is_null($metaKey[0]) ? $this->postContainsViewName($metaKey[0], $detailViewName) : false;
				if ($viewContainedMetaKey) {
					$viewContainedCustomField = true;
				}
				$viewAddressDetailContainedMetaKey = !is_null($metaKey[0]) ? $this->postContainsViewName($metaKey[0], $addressDetailViewName, 'oo_address') : false;
				if ($viewAddressDetailContainedMetaKey) {
					$viewContainedAddressDetailCustomField = true;
				}
			}

			if (($viewContained) || ($viewContainedCustomField && $viewContained) || ($viewContainedCustomField && $hasOtherShortcodeInPostContent == false)) {
				if ($postType == 'page') {
					$pDetailView->setPageId((int) $postId);
					$pDetailView->addToPageIdsHaveDetailShortCode( (int) $postId );
					$pDataDetailViewHandler->saveDetailView($pDetailView);
					$this->_pRewriteRuleBuilder->addDynamicRewriteRules();
					flush_rewrite_rules();
				}
			} elseif ($pDetailView->getPageId() !== 0) {
				$postRevisions = wp_get_post_revisions($postId);
				$detailInPreviousRev = array_key_exists($pDetailView->getPageId(), $postRevisions);

				if ($detailInPreviousRev || $pDetailView->getPageId() === $postId) {
					$pDetailView->setPageId(0);
					$pDetailView->removeFromPageIdsHaveDetailShortCode( (int) $postId );
					$pDataDetailViewHandler->saveDetailView($pDetailView);
					$this->_pRewriteRuleBuilder->addDynamicRewriteRules();
					flush_rewrite_rules();
				}
			}

			if (($viewContainedAddressDetail) || ($viewContainedAddressDetailCustomField && $hasOtherAddressDetailShortcodeInPostContent == false)) {
				if ($postType == 'page') {
					$pAddressDetailView->setPageId((int) $postId);
					$pAddressDetailView->addToPageIdsHaveDetailShortCode((int) $postId);
					$pDataAddressDetailViewHandler->saveAddressDetailView($pAddressDetailView);
					$this->_pRewriteRuleBuilder->addDynamicRewriteRulesForAddressDetail();
					flush_rewrite_rules();
				}
			} elseif ($pAddressDetailView->getPageId() !== 0) {
				$postRevisions = wp_get_post_revisions($postId);
				$detailInPreviousRev = array_key_exists($pAddressDetailView->getPageId(), $postRevisions);

				if ($detailInPreviousRev || $pAddressDetailView->getPageId() === $postId) {
					$pAddressDetailView->setPageId(0);
					$pAddressDetailView->removeFromPageIdsHaveDetailShortCode((int) $postId);
					$pDataAddressDetailViewHandler->saveAddressDetailView($pAddressDetailView);
					$this->_pRewriteRuleBuilder->addDynamicRewriteRulesForAddressDetail();
					flush_rewrite_rules();
				}
			}

			$this->addPageUseShortCode($pPost);
			$listView        = $this->getListView();
			$listViewAddress = $this->getListViewAddress();
			$listViewForm    = $this->getListForm();
			$this->deletePageUseShortCodeWhenUpdatePage($listView, $pPost);
			$this->deletePageUseShortCodeWhenUpdatePage($listViewAddress, $pPost);
			$this->deletePageUseShortCodeWhenUpdatePage($listViewForm, $pPost);
		}

	}

	private function deletePageUseShortCodeWhenUpdatePage($listView, $pPost) {
		foreach ($listView as $view) {
			if(empty($view->page_shortcode)){
				continue;
			}
			$pageShortcode = explode(',', $view->page_shortcode);
			if ( ! in_array($pPost->ID, $pageShortcode)) {
				continue;
			}
			foreach (self::LIST_CONFIGS as $key => $listConfig) {
				$metaKeys               = get_post_meta($pPost->ID, '', true);
				$viewShortcodeName      = $this->generateViewNameOfShortCode($view->name, $listConfig['option']);
				$viewContained          = $this->postContains($pPost->post_content, "oo_" . $key, $viewShortcodeName);
				if ( is_array( $metaKeys ) && isset( $metaKeys['list_shortcode'] ) && count( $metaKeys['list_shortcode'] ) !== 0 ) {
					$viewContainedShortcode = $this->postContains( $metaKeys['list_shortcode'][0], "oo_" . $key, $viewShortcodeName );
					if ( ! $viewContainedShortcode || $viewContained ) {
						$this->deletePageUseShortCode( $pPost );
					}
				}
			}
		}
	}


	/**
	 *
	 */

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
	 */

	public function onMoveTrash() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- WordPress core handles nonce verification for trash action
		// Validate and sanitize input
		if (!isset($_GET['post'])) {
			return;
		}
		
		$posts = is_array($_GET['post']) 
			? array_map('absint', wp_unslash($_GET['post'])) 
			: [absint(wp_unslash($_GET['post']))];

		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		
		if (empty($posts)) {
			return;
		}

		$pDataDetailViewHandler = $this->_pContainer->get(DataDetailViewHandler::class);
		$pDataAddressDetailViewHandler = $this->_pContainer->get(DataAddressDetailViewHandler::class);
		$pDetailView            = $pDataDetailViewHandler->getDetailView();
		$detailPageIds          = $pDetailView->getPageIdsHaveDetailShortCode();
		$pAddressDetailView     = $pDataAddressDetailViewHandler->getAddressDetailView();
		$addressDetailPageIds   = $pAddressDetailView->getPageIdsHaveDetailShortCode();
		$hasDetailPost          = false;
		$hasAddressDetailPost   = false;

		foreach ( $posts as $postId ) {
			if ( in_array( $postId, $detailPageIds ) ) {
				$pDetailView->removeFromPageIdsHaveDetailShortCode( (int) $postId );
				$hasDetailPost = true;
			}
			if (in_array($postId, $addressDetailPageIds)) {
				$pAddressDetailView->removeFromPageIdsHaveDetailShortCode((int) $postId);
				$hasAddressDetailPost = true;
			}
			$pPost = get_post( $postId );
			$this->deletePageUseShortCode( $pPost );
		}
		if ( $hasDetailPost ) {
			if ( empty( $pDetailView->getPageIdsHaveDetailShortCode() ) ) {
				$pDetailView->setPageId( 0 );
			} elseif ( in_array( $pDetailView->getPageId(), $posts ) ) {
				$firstDetailPageId = min( array_keys( $detailPageIds ) );
				$pDetailView->setPageId( (int) $detailPageIds[ $firstDetailPageId ] );
			}
			$pDataDetailViewHandler->saveDetailView( $pDetailView );
			flush_rewrite_rules();
		}
		if ( $hasAddressDetailPost ) {
			if (empty($pAddressDetailView->getPageIdsHaveDetailShortCode())) {
				$pAddressDetailView->setPageId(0);
			} elseif (in_array($pAddressDetailView->getPageId(), $posts)) {
				$firstDetailPageId = min(array_keys( $addressDetailPageIds ));
				$pAddressDetailView->setPageId((int) $addressDetailPageIds[$firstDetailPageId]);
			}
			$pDataAddressDetailViewHandler->saveAddressDetailView($pAddressDetailView);
			flush_rewrite_rules();
		}
	}


	/**
	 *
	 * @param string $detailViewName
	 * @return string
	 *
	 */

	private function generateDetailViewCode( $detailViewName )
	{
		return 'view="' . $detailViewName . '"';
	}


	/**
	 * @param $viewName
	 * @param $option
	 *
	 * @return string
	 */

	private function generateViewNameOfShortCode( $viewName, $option )
	{
		return $option . '="' . $viewName . '"';
	}


	/**
	 * @return object[]
	 */

	private function getListView()
	{
		$this->_pRecordReadListView->addColumn( 'listview_id' );
		$this->_pRecordReadListView->addColumn( 'name' );
		$this->_pRecordReadListView->addColumn( 'page_shortcode' );

		return $this->_pRecordReadListView->getRecords();
	}


	/**
	 * @return array
	 */

	private function getListViewAddress()
	{
		$pRecordReadListViewAddress = $this->_pContainer->get( RecordManagerReadListViewAddress::class );
		$pRecordReadListViewAddress->addColumn( 'listview_address_id' );
		$pRecordReadListViewAddress->addColumn( 'name' );
		$pRecordReadListViewAddress->addColumn( 'page_shortcode' );

		return $pRecordReadListViewAddress->getRecords();
	}


	/**
	 * @return object[]
	 */

	private function getListForm()
	{
		$pRecordReadForm = $this->_pContainer->get( RecordManagerReadForm::class );
		$pRecordReadForm->addColumn( 'form_id' );
		$pRecordReadForm->addColumn( 'name' );
		$pRecordReadForm->addColumn( 'page_shortcode' );

		return $pRecordReadForm->getRecords();
	}


	/**
	 *
	 * @param string $post
	 * @param string $viewName
	 * @param string $tagShortcode
	 * @return bool
	 *
	 */

	private function postContainsViewName(string $post, string $viewName, string $tagShortcode = 'oo_estate') {
		$matches = array();
		$regex = get_shortcode_regex(array($tagShortcode));
		preg_match_all('/'.$regex.'/ism', $post, $matches);

		$detailviewCode = $this->generateDetailViewCode($viewName);

		if (!array_key_exists(3, $matches)) {
			return false;
		}

		$view = $matches[3];
		$view = str_replace('\u0022', '"', $view);
		
		foreach ($view as $tagParams) {
			if (__String::getNew($tagParams)->contains($detailviewCode)) {
				return true;
			}
		}

		return false;
	}

	/**
	 *
	 * @param string $post
	 * @param string $viewName
	 * @param string $tagShortcode
	 * @return bool
	 *
	 */

	private function checkOtherShortcodeInPostContent(string $post, string $viewName, string $tagShortcode = 'oo_estate') {
		$matches = array();
		$regex   = get_shortcode_regex(array($tagShortcode));
		preg_match_all('/' . $regex . '/ism', $post, $matches);

		$detailviewCode = $this->generateDetailViewCode($viewName);

		foreach ($matches[3] as $tagParams) {
			if ($tagParams !== $detailviewCode) {
				return true;
			}
		}

		return false;
	}


	/**
	 * @param $postContent
	 * @param $element
	 * @param $viewShortcodeName
	 *
	 * @return bool
	 */

	private function postContains( $postContent, $element, $viewShortcodeName )
	{
		$matches = array();
		$regex   = get_shortcode_regex( array( $element ) );
		preg_match_all( '/' . $regex . '/ism', $postContent, $matches );

		if ( ! array_key_exists( 3, $matches ) ) {
			return false;
		}

		foreach ( $matches[3] as $tagParams ) {
			if ( __String::getNew( $tagParams )->contains( $viewShortcodeName ) ) {
				return true;
			}
		}

		return false;
	}


	/**
	 * @param $post
	 */

	private function addPageUseShortCode( $post )
	{
		$listView        = $this->getListView();
		$listViewAddress = $this->getListViewAddress();
		$listForm        = $this->getListForm();
		$isRevision      = wp_is_post_revision( $post );
		if ( ! $isRevision && $post->post_type === 'page' ) {
			$postID      = $post->ID;
			$metaKeys    = get_post_meta( $postID, '', true );
			$allContents = [ [ 0 => $post->post_content ] ] + $metaKeys;

			if ( empty( $postID ) ) {
				return;
			}

			foreach ( $allContents as $allContent ) {
				$content = $allContent[0] ?? '';
				if ( strpos( $content, 'oo_estate' ) !== false ) {
					$this->addPageShortCode( $listView, [ 'ID' => $postID, 'post_content' => $content ],
						'estate' );
				}
				if ( strpos( $content, 'oo_address' ) !== false ) {
					$this->addPageShortCode( $listViewAddress, [ 'ID' => $postID, 'post_content' => $content ],
						'address' );
				}
				if ( strpos( $content, 'oo_form' ) !== false ) {
					$this->addPageShortCode( $listForm, [ 'ID' => $postID, 'post_content' => $content ],
						'form' );
				}
			}
		}
	}


	/**
	 * @param $post
	 */

	private function deletePageUseShortCode( $post )
	{
		$listView        = $this->getListView();
		$listViewAddress = $this->getListViewAddress();
		$listForm        = $this->getListForm();
		$isRevision      = wp_is_post_revision( $post );

		if ( ! $isRevision && $post->post_type === 'page' ) {
			$postID      = $post->ID;
			$metaKeys    = get_post_meta( $postID, '', true );
			$allContents = [ [ 0 => $post->post_content ] ] + $metaKeys;

			foreach ( $allContents as $allContent ) {
				$content = $allContent[0] ?? '';
				$shortcode = $metaKeys['list_shortcode'][0] ?? '';
				if ( strpos( $content,
						'oo_estate' ) !== false || is_array( $metaKeys ) && isset( $metaKeys['list_shortcode'] ) && strpos( $shortcode,
						'oo_estate' ) == false && count( $metaKeys['list_shortcode'] ) !== 0 ) {
					$this->deletePageShortCode( $listView, $post, "oo_plugin_listviews", "listview_id", "listview_id" );
				}
				if ( strpos( $content,
						'oo_address' ) !== false || is_array( $metaKeys ) && isset( $metaKeys['list_shortcode'] ) && strpos( $shortcode,
						'oo_estate' ) == false && count( $metaKeys['list_shortcode'] ) !== 0 ) {
					$this->deletePageShortCode( $listViewAddress, $post, "oo_plugin_listviews_address",
						"listview_address_id", "listview_address_id" );
				}
				if ( strpos( $content,
						'oo_form' ) !== false || is_array( $metaKeys ) && isset( $metaKeys['list_shortcode'] ) && strpos( $shortcode,
						'oo_estate' ) == false && count( $metaKeys['list_shortcode'] ) !== 0 ) {
					$this->deletePageShortCode( $listForm, $post, "oo_plugin_forms", "form_id", "form_id" );
				}
			}
		}
	}


	/**
	 * @param $listView
	 * @param $post
	 * @param $type
	 */

	private function addPageShortCode( $listView, $post, $type)
	{
		$listConfig  = self::LIST_CONFIGS[$type];
		$postID      = $post['ID'];
		$postContent = $post['post_content'];
		foreach ( $listView as $view ) {
			$pageShortCodeIDs = [];
			if ( ! empty( $view->page_shortcode ) ) {
				$pageShortCodeIDs = explode( ',', $view->page_shortcode );
			}
			if ((in_array($postID, $pageShortCodeIDs) && !empty($postID)) || empty($postID)) {
				break;
			}
			$viewShortcodeName = $this->generateViewNameOfShortCode( $view->name, $listConfig['option'] );
			$viewContained     = $this->postContains( $postContent, "oo_" . $type, $viewShortcodeName );
			if ( $viewContained ) {
				$pageShortCodeIDs[] = $postID;
				$key = $listConfig["key"];

				$this->_pRecordReadListView->updateColumnPageShortCode( implode( ",", $pageShortCodeIDs ),
					$view->$key, $listConfig["tableName"], $listConfig["key"] );
			}
		}
	}


	/**
	 * @param $listView
	 * @param $post
	 * @param $tableName
	 * @param $column
	 * @param $primaKey
	 */

	private function deletePageShortCode( $listView, $post, $tableName, $column, $primaKey )
	{
		$postID = $post->ID;
		foreach($listView as $view)
		{
			$pageID = '';
			if (empty($view->page_shortcode) || empty($postID))
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
				$this->_pRecordReadListView->updateColumnPageShortCode($pageID,$view->$primaKey,$tableName,$column);
			}

		}
	}
}
