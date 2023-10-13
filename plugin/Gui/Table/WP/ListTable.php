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

namespace onOffice\WPlugin\Gui\Table\WP;

use WP_List_Table;
use const ABSPATH;
use function __;
use function _e;

if (!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

abstract class ListTable extends WP_List_Table
{
	/**
	 *
	 * $columns = array(
	 *		'link_name' => __('Link Name', 'onoffice-for-wp-websites'),
	 *		'link_category' => __('Link Category', 'onoffice-for-wp-websites'),
	 *		'cb' => '<input type="checkbox" />',
	 *	);
	 *
	 *	$hidden = array('ID');
	 *	$sortable = array();
	 *
	 *	$this->_column_headers = array($columns, $hidden, $sortable);
	 *	$this->items = array(
	 *		array('link_name' => 'test', 'link_category' => 'check', 'ID' => 4),
	 *		array('link_name' => 'test', 'link_category' => 'check', 'ID' => 5),
	 *		array('link_name' => 'test', 'link_category' => 'check', 'ID' => 6),
	 *	);
	 *
	 *	$this->set_pagination_args( array(
	 *		'total_items' => 3,
	 *		'per_page'    => 3,
	 *		'total_pages' => 1
	 *	) );
	 *
	 */

	public function prepare_items()
	{
		return parent::prepare_items();
	}


	/**
	 *
	 */

	public function no_items()
	{
		_e( 'No items found.' );
	}


	/**
	 *
	 * @return array
	 *
	 */

	protected function get_bulk_actions()
	{
		$actions = array();
		$actions['bulk_delete'] = __( 'Delete' );

		return $actions;
	}


	/**
	 *
	 * @param object $pItem
	 * @return string
	 *
	 */

	protected function column_cb($pItem)
	{
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'],
			$pItem->ID);
	}


	/**
	 *
	 * @param object $pItem
	 * @param string $columnName
	 * @return string
	 *
	 */

	protected function column_default($pItem, $columnName)
	{
		return $pItem->{$columnName};
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getArgs(): array
	{
		return $this->_args;
	}


	/** @var array $items */
	protected function setItems(array $items)
		{ $this->items = $items; }

	/** @return array */
	protected function getItems()
		{ return $this->items; }

	protected function handleRecord( array $records )
	{
		if ( empty( $records ) ) {
			return [];
		}
		$recordHandled = [];
		foreach ( $records as $record ) {
			if ( ! empty( $record->page_shortcode ) ) {
				$listPageID = explode( ',', $record->page_shortcode );
				$listPage   = [];
				foreach ( $listPageID as $pageID ) {
					$listPage[] = "<a href='" . esc_attr( get_edit_post_link( (int) $pageID ) ) . "' target='_blank'>" . esc_html( get_the_title( (int) $pageID ) ) . "</a>";
				}
				$pages                  = implode( ',', $listPage );
				$record->page_shortcode = $pages;
			}
			if (isset($record->default_recipient) && $record->default_recipient === '1') {
				if ( get_option( 'onoffice-settings-default-email', '' ) ) {
					$record->recipient = esc_html( __( "Default", 'onoffice-for-wp-websites' ) . ' (' .
					                               get_option( 'onoffice-settings-default-email', '' ) . ')' );
				} else {
					$record->recipient = esc_html( __( "Default",
							'onoffice-for-wp-websites' ) ) . " <i>(" . __( "missing", 'onoffice-for-wp-websites' ) . ")</i>";
				}
			} else {
				if(isset($record->recipient)){
					$record->recipient = sprintf( esc_html( __( "%s (override)", 'onoffice-for-wp-websites' ) ), $record->recipient );
				}
			}
			if (isset($record->form_type) && $record->form_type === 'applicantsearch') {
				$record->recipient = esc_html( '-' );
			}
			$recordHandled[] = $record;
		}

		return $recordHandled;
	}

	/**
	 * @param array $records
	 * @param array $detectLanguagePageRecords
	 * @return array
	 */
	protected function getDetectLanguagePageRecords(array $records, array $detectLanguagePageRecords)
	{
		$pageLocale = [];

		if (empty($records)) {
			return [];
		} elseif (empty($detectLanguagePageRecords)) {
			return $records;
		}

		foreach ($detectLanguagePageRecords as $detectLanguagePageRecord) {
			$pageLocale[$detectLanguagePageRecord->page_using_the_shortcode_id] = $detectLanguagePageRecord->locale;
		}

		$detectLanguagePageRecords = [];
		$getMultilingualPluginLists = $this->getMultilingualPluginLists();

		foreach ($records as $record) {
			if (count($getMultilingualPluginLists) > 0) {
				$currentLanguage = icl_get_current_language();
				$detectLanguagePageRecords[] = $this->getPagesUsingTheShortcodeWhenUseMultilingualIds($record, $currentLanguage);
			} else {
				$currentLanguage = get_locale();
				$detectLanguagePageRecords[] = $this->getPagesUsingTheShortcodeIds($record, $currentLanguage, $pageLocale);
			}
		}

		return $detectLanguagePageRecords;
	}

	/**
	 * @param object $record
	 * @param string $currentLanguage
	 * @return object
	 */
	private function getPagesUsingTheShortcodeWhenUseMultilingualIds(object $record, string $currentLanguage)
	{
		if (!empty($record->page_shortcode) && $currentLanguage !== 'all') {
			$currentPagesUsingTheShortcodeIds = explode(',', $record->page_shortcode);
			$pageLists = [];

			foreach ($currentPagesUsingTheShortcodeIds as $pageId) {
				$pageLists[$pageId] = get_the_title((int) $pageId);
			}

			if (defined('ICL_SITEPRESS_VERSION')) {
				$desiredPageIds = [];
				foreach ($pageLists as $pageId => $pageTitle) {
					$wpmlType = apply_filters('wpml_element_type', get_post_type($pageId));
					$wpmlTrid = apply_filters('wpml_element_trid', false, $pageId, $wpmlType);
					$wpmlTranslations = apply_filters('wpml_get_element_translations', array(), $wpmlTrid, $wpmlType);
					$informationPages = array_values($wpmlTranslations);
					foreach ($informationPages as $informationPage) {
						if ($informationPage->post_title == $pageTitle && $informationPage->language_code != $currentLanguage) {
							continue;
						}
						$desiredPageIds[] = $informationPage;
					}
				}

				$lastPageIds = array_map(function ($item) {
					return $item->element_id;
				}, $desiredPageIds);

				$record->page_shortcode = implode(',', array_unique($lastPageIds));
			}
		}
		return $record;
	}

	/**
	 * @param object $record
	 * @param string $currentLanguage
	 * @param array $pageLocale
	 *
	 * @return object
	 */
	private function getPagesUsingTheShortcodeIds(object $record, string $currentLanguage, array $pageLocale)
	{
		if (empty($record->page_shortcode)) {
			return $record;
		}

		$currentPagesUsingTheShortcodeIds = explode(',', $record->page_shortcode);

		$pageList = [];
		foreach ($currentPagesUsingTheShortcodeIds as $pageId) {
			$pageTitle = get_the_title((int) $pageId);
			$pageList[$pageId] = $pageTitle;
		}

		$needRemovePages = [];
		foreach ($currentPagesUsingTheShortcodeIds as $pageId) {
			$pageTitles = $pageList;
			$pageTitle = get_the_title((int) $pageId);
			unset($data[$pageId]);
			if (isset($pageLocale[$pageId]) && $pageLocale[$pageId] !== $currentLanguage && in_array($pageTitle, $pageTitles)) {
				$needRemovePages[] = $pageId;
			}
		}

		$desiredPageIds = array_diff($currentPagesUsingTheShortcodeIds, $needRemovePages);
		$lastPageIds = array_values($desiredPageIds);

		$record->page_shortcode = implode(',', $lastPageIds);

		return $record;
	}

	/**
	 * @return array
	 */
	private function getMultilingualPluginLists()
	{
		$multilingualPluginActiveLists = [];

		$multilingualPluginLists = [
			"sitepress-multilingual-cms/sitepress.php",
		];

		foreach ($multilingualPluginLists as $multilingualPlugin) {
			if (in_array($multilingualPlugin ,get_option("active_plugins"))) {
				array_push($multilingualPluginActiveLists, $multilingualPlugin);
			}
		}

		return $multilingualPluginActiveLists;
	}
}
