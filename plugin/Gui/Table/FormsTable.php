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

namespace onOffice\WPlugin\Gui\Table;

use onOffice\WPlugin\Controller\UserCapabilities;
use onOffice\WPlugin\Gui\AdminPageEstateListSettingsBase;
use onOffice\WPlugin\Gui\AdminPageFormList;
use onOffice\WPlugin\Gui\Table\WP\ListTable;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\Translation\FormTranslation;
use WP_List_Table;
use function __;
use function add_query_arg;
use function admin_url;
use function current_user_can;
use function esc_html;
use function esc_html__;
use function esc_js;
use function esc_sql;
use function esc_url;
use function menu_page_url;
use function number_format_i18n;
use function wp_nonce_url;
/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class FormsTable
	extends ListTable
{
	/** @var int */
	private $_itemsPerPage = null;

	/** @var string */
	private $_listType = 'all';

	/** @var array */
	private $_countByType = array();

	/**
	 *
	 * @see WP_List_Table::__construct() for more information on default arguments.
	 *
	 * @param array $args An associative array of arguments.
	 *
	 */

	public function __construct($args = array())
	{
		$args['singular'] = 'form';
		$args['plural'] = 'forms';
		$args['screen'] = get_current_screen()->id;
		parent::__construct($args);

		$this->_itemsPerPage = $this->get_items_per_page('onoffice-forms-forms_per_page', 10);
	}


	/**
	 *
	 */

	private function fillData()
	{
		$page = $this->get_pagenum() - 1;
		$itemsPerPage = $this->_itemsPerPage;
		$offset = $page * $itemsPerPage;

		$pRecordRead = new RecordManagerReadForm();
		$pRecordRead->setLimit($itemsPerPage);
		$pRecordRead->setOffset($offset);
		$pRecordRead->addColumn('form_id', 'ID');
		$pRecordRead->addColumn('name');
		$pRecordRead->addColumn('recipient');
		$pRecordRead->addColumn('template');
		$pRecordRead->addColumn('form_type');
		$pRecordRead->addColumn('name', 'shortcode');
		$pRecordRead->addColumn('page_shortcode');
		if ($this->_listType != 'all' && $this->_listType != null) {
			$pRecordRead->addWhere("`form_type` = '".esc_sql($this->_listType)."'");
		}

		$pRecord = $pRecordRead->getRecordsSortedAlphabetically();
		$pRecord = $this->handleRecord($pRecord);
		$this->setItems($pRecord);
		$itemsCount = $pRecordRead->getCountOverall();

		$this->set_pagination_args( array(
			'total_items' => $itemsCount,
			'per_page' => $this->_itemsPerPage,
			'total_pages' => ceil($itemsCount / 10)
		) );

		$this->_countByType = $pRecordRead->getCountByType();
	}


	/**
	 *
	 */

	public function prepare_items()
	{
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'name' => __('Form name', 'onoffice-for-wp-websites'),
			'recipient' => __('Recipient e-mail address', 'onoffice-for-wp-websites'),
			'template' => __('Template', 'onoffice-for-wp-websites'),
			'form_type' => __('Form type', 'onoffice-for-wp-websites'),
			'shortcode' => __('Shortcode', 'onoffice-for-wp-websites'),
			'page_shortcode' => __('Pages using the shortcode', 'onoffice-for-wp-websites'),
		);

		$hidden = array('ID', 'filterId');
		$sortable = array();

		$this->_column_headers = array($columns, $hidden, $sortable,
			$this->get_default_primary_column_name());

		$this->fillData();
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function get_columns()
	{
		return array(
			'cb' => '<input type="checkbox" />',
			'name' => __('Name of Form', 'onoffice-for-wp-websites'),
			'form_type' => __('Type of Form', 'onoffice-for-wp-websites'),
			'shortcode' => __('Shortcode', 'onoffice-for-wp-websites'),
		);
	}


	/**
	 *
	 * @param object $pItem
	 * @param string $columnName
	 * @return string
	 *
	 */

	protected function column_default($pItem, $columnName) {
		$result = null;
		if (property_exists($pItem, $columnName)) {
			$result = $pItem->{$columnName};
		}
		return $result;
	}


	/**
	 *
	 * @return bool
	 *
	 */

	public function ajax_user_can()
	{
		$pUserCapabilities = new UserCapabilities();
		$roleEditForms = $pUserCapabilities->getCapabilityForRule
			(UserCapabilities::RULE_EDIT_VIEW_FORM);
		return current_user_can($roleEditForms);
	}


	/**
	 *
	 * @param object $pItem
	 * @return string
	 *
	 */

	protected function column_shortcode($pItem)
	{
		return '<input type="text" readonly value="[oo_form form=&quot;'
			.esc_html($pItem->name).'&quot;]">';
	}


	/**
	 *
	 * @return array
	 *
	 */

	protected function get_views() {
		$paramName = AdminPageFormList::PARAM_TYPE;
		$baseUrl = menu_page_url('onoffice-forms', false);

		$pFormTranslation = new FormTranslation();
		$formConfig = $pFormTranslation->getFormConfig();

		$result = array();

		foreach ($formConfig as $type => $label)
		{
			$editUrl = add_query_arg($paramName, $type, $baseUrl);

			$current = ($this->_listType == $type ? ' class="current" aria-current="page"' : '');
			$count = isset($this->_countByType[$type]) ? $this->_countByType[$type] : 0;

			$result[$type] = '<a href="'.esc_url($editUrl).'"'.$current.'>'.
				sprintf( '%s <span class="count">(%s)</span>',
					$pFormTranslation->getPluralTranslationForForm($type, $count),
					number_format_i18n( $count )
				).'</a>';
		}
		return $result	;
	}


	/**
	 * Generates and displays row action links.
	 *
	 * @param object $pItem Link being acted upon.
	 * @param string $column_name Current column name.
	 * @param string $primary Primary column name.
	 * @return string Row action output for links.
	 *
	 */

	protected function handle_row_actions($pItem, $column_name, $primary)
	{
		if ( $primary !== $column_name )
		{
			return '';
		}

		$formIdParam = AdminPageEstateListSettingsBase::GET_PARAM_VIEWID;
		$editLink = add_query_arg($formIdParam, $pItem->ID, admin_url('admin.php?page=onoffice-editform'));

		$actions = [];
		$actions['edit'] = '<a href="'.esc_attr($editLink).'">'.esc_html__('Edit').'</a>';
		$actions['duplicate'] = "<a class='button-duplicate' href='"
			. esc_attr(wp_nonce_url(admin_url('admin.php') . '?page=onoffice-forms&action=bulk_duplicate&form=' . $pItem->name,
				'bulk-forms'))
			. "'>" . esc_html__('Duplicate', 'onoffice-for-wp-websites') . "</a>";
		$actions['delete'] = "<a class='submitdelete' href='"
			.wp_nonce_url(admin_url('admin.php').'?page=onoffice-forms&action=bulk_delete&form[]='.$pItem->ID, 'bulk-forms')
			."' onclick=\"if ( confirm( '"
			.esc_js(sprintf(
			/* translators: %s is the name of the form. */
			__("You are about to delete the form '%s'\n  'Cancel' to stop, 'OK' to delete.", 'onoffice-for-wp-websites'), $pItem->name))
			."' ) ) { return true;}return false;\">" . __('Delete') . "</a>";
		return $this->row_actions( $actions );
	}

	/** @return string */
	public function getListType()
		{ return $this->_listType; }

	/** @param string $listType */
	public function setListType($listType)
		{ $this->_listType = $listType; }
}
