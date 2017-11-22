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

namespace onOffice\WPlugin\Gui;
use onOffice\WPlugin\Gui\AdminPage;
use onOffice\WPlugin\Gui\Table\EstateListTable;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class AdminPageEstateList
	extends AdminPage
{
	/** @var int */
	private $_itemsDeleted = null;


	/**
	 *
	 */

	public function renderContent()
	{
		$actionFile = plugin_dir_url(ONOFFICE_PLUGIN_DIR).
			plugin_basename(ONOFFICE_PLUGIN_DIR).'/tools/listview.php';

		$pTable = new EstateListTable();
		$pTable->prepare_items();
		echo '<p>';
		echo '<form method="post" action="'.esc_html($actionFile).'">';
		$pTable->display();
		echo '</form>';
		echo '</p>';
	}


	/**
	 *
	 * @param string $subTitle
	 *
	 */

	public function generatePageMainTitle($subTitle)
	{
		echo '<h1 class="wp-heading-inline">'.esc_html__('onOffice', 'onoffice');

		if ($subTitle != '')
		{
			echo ' › '.esc_html__($subTitle, 'onoffice');
		}

		echo ' › '.esc_html__('List Views', 'onoffice');

		$new_link = admin_url('admin.php?page=onoffice-editlistview');

		echo '</h1>';
		echo '<a href="'.$new_link.'" class="page-title-action">'.esc_html__('Add New', 'onoffice').'</a>';
		echo '<hr class="wp-header-end">';
	}


	/**
	 *
	 */

	public function handleAdminNotices()
	{
		$this->_itemsDeleted = filter_input(INPUT_GET, 'delete');

		if ($this->_itemsDeleted === null || $this->_itemsDeleted === false)
		{
			return;
		}

		if ($this->_itemsDeleted > 0)
		{
			add_action( 'admin_notices', array($this, 'displayListViewDeleteSuccess') );
		}
		else
		{
			add_action( 'admin_notices', array($this, 'displayListViewDeleteError') );
		}
	}


	/**
	 *
	 */

	public function displayListViewDeleteSuccess()
	{
		$class = 'notice notice-success is-dismissible';

		$message = sprintf( _n( '%s list view has been deleted.', '%s list views have been deleted.',
			$this->_itemsDeleted, 'onoffice' ),
				number_format_i18n( $this->_itemsDeleted ) );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}


	/**
	 *
	 */

	public function displayListViewDeleteError()
	{
		$class = 'notice notice-error is-dismissible';
		$message = __( 'No list view was deleted.', 'onoffice' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}


	/**
	 *
	 */

	public function doExtraEnqueues()
	{
		$translation = array(
			'confirmdialog' => __('Are you sure you want to delete the selected items?', 'onoffice'),
		);

		wp_register_script('estatelist-bulk-actions', plugins_url('/js/estatelistview-bulk-actions.js',
			ONOFFICE_PLUGIN_DIR.'/index.php'), array('jquery'));

		wp_localize_script('estatelist-bulk-actions', 'onoffice_listviewlist_settings', $translation);
		wp_enqueue_script('estatelist-bulk-actions');
	}
}
