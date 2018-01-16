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

use Exception;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Gui\Table\FormsTable;
use onOffice\WPlugin\Utility\__String;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class AdminPageFormList
	extends AdminPage
{
	/** */
	const PARAM_TYPE = 'type';

	/** @var FormsTable */
	private $_pFormsTable = null;


	/**
	 *
	 * @param string $pageSlug
	 *
	 */

	public function __construct($pageSlug)
	{
		parent::__construct($pageSlug);

		$tab = $this->getTab();
		$this->_pFormsTable = new FormsTable();

		if (!__String::getNew($tab)->isEmpty() &&
			!array_key_exists($tab, $this->_pFormsTable->getFormConfig())) {
			throw new Exception('Unknown Form type');
		}

		$this->_pFormsTable->setListType($tab);
	}


	/**
	 *
	 */

	public function renderContent()
	{
		$this->generatePageMainTitle(__('Forms', 'onoffice'));
		$actionFile = plugin_dir_url(ONOFFICE_PLUGIN_DIR).
			plugin_basename(ONOFFICE_PLUGIN_DIR).'/tools/form.php';

		$this->_pFormsTable->prepare_items();
		echo '<p>';
		echo '<form method="post" action="'.esc_html($actionFile).'">';
		echo $this->_pFormsTable->views();
		$this->_pFormsTable->display();
		echo '</form>';
		echo '</p>';
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function getTab()
	{
		$getParamType = filter_input(INPUT_GET, self::PARAM_TYPE);
		return $getParamType;
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
			add_action( 'admin_notices', array($this, 'displayFormDeleteSuccess') );
		}
		else
		{
			add_action( 'admin_notices', array($this, 'displayFormDeleteError') );
		}
	}


	/**
	 *
	 */

	public function displayFormDeleteSuccess()
	{
		$class = 'notice notice-success is-dismissible';

		$message = sprintf( _n( '%s form has been deleted.', '%s forms have been deleted.',
			$this->_itemsDeleted, 'onoffice' ),
				number_format_i18n( $this->_itemsDeleted ) );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}


	/**
	 *
	 */

	public function displayFormDeleteError()
	{
		$class = 'notice notice-error is-dismissible';
		$message = __( 'No form was deleted.', 'onoffice' );

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

		wp_register_script('onoffice-bulk-actions', plugins_url('/js/onoffice-bulk-actions.js',
			ONOFFICE_PLUGIN_DIR.'/index.php'), array('jquery'));

		wp_localize_script('onoffice-bulk-actions', 'onoffice_table_settings', $translation);
		wp_enqueue_script('onoffice-bulk-actions');
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

		$tab = $this->getTab();

		if ($tab == null) {
			$tab = Form::TYPE_CONTACT;
		}

		$typeParam = AdminPageFormSettingsMain::GET_PARAM_TYPE;

		$new_link = add_query_arg($typeParam, $tab, admin_url('admin.php?page=onoffice-editform'));

		$formConfig = $this->_pFormsTable->getFormConfig();
		$label = $formConfig[$tab];

		$translation = translate_nooped_plural( $label[FormsTable::SUB_LABEL], 1, 'onoffice' );

		echo '</h1>';
		echo '<a href="'.$new_link.'" class="page-title-action">'
			.esc_html(sprintf(__('Add New %s', 'onoffice'), $translation)).'</a>';
		echo '<hr class="wp-header-end">';
	}
}
