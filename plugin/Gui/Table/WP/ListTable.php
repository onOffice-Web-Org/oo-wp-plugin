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

	protected function handlePageShortcodeRecord(array $records, array $recordsDetectLanguagePage)
	{
		$outputArray = [];
		foreach ($recordsDetectLanguagePage as $item) {
			$embed_shortcode_form_page_id = $item->embed_shortcode_form_page_id;
			$translate = $item->locale;
			$outputArray[$embed_shortcode_form_page_id] = $translate;
		}

		$local = get_locale();
		if (empty($records)) {
			return [];
		}

		$recordHandled = [];
		$multilingualPluginActive = $this->getMultilingualPluginActive();
		if (count($multilingualPluginActive) > 0) {
			$current_language = icl_get_current_language();
			foreach ($records as $record) {
				$recordHandled[] = $this->processRecordMultilingualPlugin($record, $current_language);
			}
		} else {
			foreach ($records as $record) {
				$recordHandled[] = $this->processRecord($record, $local, $outputArray);
			}
		}

		return $recordHandled;
	}

	private function processRecordMultilingualPlugin($record, $current_language)
	{
		if (!empty($record->page_shortcode)) {
			$listPageID = explode(',', $record->page_shortcode);
			$listPageTest = [];
			foreach ($listPageID as $pageID) {
				$pageTitle = get_the_title((int)$pageID);
				$listPageTest[$pageID] = $pageTitle;
			}

			$filtered_list = [];
				if (defined('ICL_SITEPRESS_VERSION')) {
					$translatedPageIDs = [];
					foreach ($listPageTest as $pageID => $pageTitle) {
						$type = apply_filters('wpml_element_type', get_post_type($pageID));
						$trid = apply_filters('wpml_element_trid', false, $pageID, $type);
						$translations = apply_filters('wpml_get_element_translations', array(), $trid, $type);
						$translatedPageIDs = array_merge($translatedPageIDs, array_values($translations));
					}
					$list = array_unique($translatedPageIDs, SORT_REGULAR);

					foreach ($list as $page) {
						$is_duplicate = false;
						foreach ($filtered_list as $filtered_page) {
							if ($page->post_title == $filtered_page->post_title && $page->language_code != $current_language) {
								$is_duplicate = true;
								break;
							}
						}

						if (!$is_duplicate && $page->language_code === $current_language) {
							$filtered_list[] = $page;
						}
					}
				}
			$filteredPageIDs = [];
			foreach ($filtered_list as $item) {
				if (isset($item->element_id)) {
					$filteredPageIDs[] = $item->element_id;
				}
			}

			$filteredPageIDsStr = implode(',', $filteredPageIDs);
			$record->page_shortcode = $filteredPageIDsStr;
		}
		return $record;
	}

	private function processRecord($record, $local, $outputArray)
	{
		if (!empty($record->page_shortcode)) {
			$listPageID = explode(',', $record->page_shortcode);
			$listPageTest = [];
	
			foreach ($listPageID as $pageID) {
				$pageTitle = get_the_title((int) $pageID);
				$listPageTest[$pageID] = $pageTitle;
			}
	
			$filteredPageIDs = [];
	
			foreach ($listPageTest as $pageID => $pageTitle) {
				if ($local !== $outputArray[$pageID] && !in_array($pageID, $filteredPageIDs) && $outputArray[$pageID] !== null) {
					$filteredPageIDs[] = $pageID;
				}
			}
	
			foreach ($filteredPageIDs as $filteredPageID) {
				$key = array_search($filteredPageID, $listPageID);
				if ($key !== false) {
					unset($listPageID[$key]);
				}
			}
	
			$filteredPageIDsStr = implode(',', $listPageID);
			$record->page_shortcode = $filteredPageIDsStr;
		}
	
		return $record;
	}

	private function getMultilingualPluginActive()
	{
		$listMultilingualPluginActive = [];

		$listMultilingualPlugin = [
			"sitepress-multilingual-cms/sitepress.php",
		];

		foreach ($listMultilingualPlugin as $plugin) {
			if (in_array($plugin ,get_option("active_plugins"))) {
				array_push($listMultilingualPluginActive,$plugin);
			}
		}

		return $listMultilingualPluginActive;
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

}
