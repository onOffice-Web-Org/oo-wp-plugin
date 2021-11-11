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

use DI\Container;
use DI\ContainerBuilder;
use Exception;
use onOffice\WPlugin\Controller\UserCapabilities;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Form\BulkDeleteRecord;
use onOffice\WPlugin\Gui\Table\FormsTable;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilder;
use onOffice\WPlugin\Record\RecordManagerDeleteForm;
use onOffice\WPlugin\Record\RecordManagerDuplicateListViewForm;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\Translation\FormTranslation;
use onOffice\WPlugin\Utility\__String;
use onOffice\WPlugin\WP\WPQueryWrapper;
use const ONOFFICE_DI_CONFIG_PATH;
use const ONOFFICE_PLUGIN_DIR;
use function __;
use function add_action;
use function add_filter;
use function add_query_arg;
use function admin_url;
use function check_admin_referer;
use function esc_html__;
use function plugins_url;
use function wp_enqueue_script;
use function wp_localize_script;
use function wp_register_script;

/**
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
		$pFormTranslation = new FormTranslation();
		$pWPQueryWrapper = new WPQueryWrapper();
		$pWPQuery = $pWPQueryWrapper->getWPQuery();

		if (__String::getNew($pWPQuery->get('page', ''))->startsWith('onoffice-') &&
			!__String::getNew($tab)->isEmpty() &&
			!array_key_exists($tab, $pFormTranslation->getFormConfig()))
		{
			throw new Exception('Unknown Form type');
		}
	}


	/**
	 *
	 */

	public function renderContent()
	{
		$this->generatePageMainTitle(__('Forms', 'onoffice-for-wp-websites'));

		$this->_pFormsTable->prepare_items();
		$page = '<input type="hidden" id="fname" name="type" value="'.$_GET['type'].'">
			<input type="hidden" id="fname" name="page" value="onoffice-forms">';
		$buttonSearch = 'Search Forms';
		$this->generateSearchForm($page,$buttonSearch);
		echo '<p>';
		echo '<form method="post">';
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

	private function getTab(): string
	{
		return filter_input(INPUT_GET, self::PARAM_TYPE) ?? 'all';
	}


	/**
	 *
	 */

	public function handleAdminNotices()
	{
		$itemsDeleted = filter_input(INPUT_GET, 'delete', FILTER_SANITIZE_NUMBER_INT);

		if ($itemsDeleted !== null && $itemsDeleted !== false) {
			add_action('admin_notices', function() use ($itemsDeleted) {
				$pHandler = new AdminNoticeHandlerListViewDeletion();
				echo $pHandler->handleFormView($itemsDeleted);
			});
		}
	}

	/**
	 *
	 */

	public function doExtraEnqueues()
	{
		$translation = array(
			'confirmdialog' => __('Are you sure you want to delete the selected items?', 'onoffice-for-wp-websites'),
		);

		wp_register_script('onoffice-bulk-actions', plugins_url('/js/onoffice-bulk-actions.js',
			ONOFFICE_PLUGIN_DIR.'/index.php'), array('jquery'));

		wp_localize_script('onoffice-bulk-actions', 'onoffice_table_settings', $translation);
		wp_enqueue_script('onoffice-bulk-actions');
	}


	/**
	 *
	 */

	public function preOutput()
	{
		$this->_pFormsTable = new FormsTable();
		$this->_pFormsTable->setListType($this->getTab());
		$pDIBuilder = new ContainerBuilder();
		$pDIBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pDI = $pDIBuilder->build();
		$this->registerDeleteAction($pDI);
		$this->registerDuplicateAction($pDI);

		parent::preOutput();
	}


	/**
	 *
	 * @param string $subTitle
	 *
	 */

	public function generatePageMainTitle($subTitle)
	{
		echo '<h1 class="wp-heading-inline">'.esc_html__('onOffice', 'onoffice-for-wp-websites');

		$tab = $this->getTab();

		$pFormTranslation = new FormTranslation();
		$translation = $pFormTranslation->getPluralTranslationForForm($tab, 1);

		if ($subTitle != '') {
			echo ' › '.esc_html__($subTitle, 'onoffice-for-wp-websites'). ' › '.$translation;
		}

		$typeParam = AdminPageFormSettingsMain::GET_PARAM_TYPE;
		$newLink = add_query_arg($typeParam, $tab, admin_url('admin.php?page=onoffice-editform'));

		echo '</h1>';

		if ($tab !== 'all') {
			echo '<a href="'.$newLink.'" class="page-title-action">'
				.esc_html__('Add New', 'onoffice-for-wp-websites').'</a>';
		}
		echo '<hr class="wp-header-end">';
	}


	/**
	 *
	 * @param Container $pDI
	 *
	 */

	private function registerDeleteAction(Container $pDI)
	{
		$pClosureDeleteForm = function(string $redirectTo, Table\WP\ListTable $pTable, array $formIds)
			use ($pDI): string
		{
			/* @var $pBulkDeleteRecord BulkDeleteRecord */
			$pBulkDeleteRecord = $pDI->get(BulkDeleteRecord::class);
			/* @var $pRecordManagerDeleteForm RecordManagerDeleteForm */
			$pRecordManagerDeleteForm = $pDI->get(RecordManagerDeleteForm::class);
			//delete form in select form in detail estate
			foreach ($formIds as $formId) {
				/* @var $pRecordManagerReadForm RecordManagerReadForm */
				$pRecordManagerReadForm = $pDI->get(RecordManagerReadForm::class);
				$nameFormByFormId = $pRecordManagerReadForm->getNameByFormId($formId);
				$onofficeDefaultViewOption = get_option('onoffice-default-view');
				if ($nameFormByFormId[0]->name === $onofficeDefaultViewOption->getShortCodeForm()) {
					$onofficeDefaultViewOption->setShortCodeForm('');
				}
				update_option('onoffice-default-view', $onofficeDefaultViewOption);
			}
			if (in_array($pTable->current_action(), ['delete', 'bulk_delete'])) {
				check_admin_referer('bulk-forms');
				$capability = UserCapabilities::RULE_EDIT_VIEW_FORM;
				$itemsDeleted = $pBulkDeleteRecord->delete($pRecordManagerDeleteForm, $capability, $formIds);
				$redirectTo = add_query_arg('delete', $itemsDeleted,
					admin_url('admin.php?page=onoffice-forms'));
			}
			return $redirectTo;
		};

		add_filter('handle_bulk_actions-onoffice_page_onoffice-forms', $pClosureDeleteForm, 10, 3);
		add_filter('handle_bulk_actions-table-onoffice_page_onoffice-forms', function(): Table\WP\ListTable {
			return $this->_pFormsTable;
		});
	}


	/**
	 *
	 * @param Container $pDI
	 */

	private function registerDuplicateAction(Container $pDI)
	{
		$pClosureDuplicateForm = function(string $redirectTo, Table\WP\ListTable $pTable)
		use ($pDI): string
		{
			if (in_array($pTable->current_action(), ['duplicate', 'bulk_duplicate'])) {
				check_admin_referer('bulk-' . $pTable->getArgs()['plural']);
				if (!(isset($_GET['form']))) {
					wp_die('No List Views for duplicating!');
				}

				/* @var $pRecordManagerDuplicateListViewForm RecordManagerDuplicateListViewForm */
				$pRecordManagerDuplicateListViewForm = $pDI->get(RecordManagerDuplicateListViewForm::class);
				$listViewRootName = $_GET['form'];
				$pRecordManagerDuplicateListViewForm->duplicateByName($listViewRootName);
			}
			return $redirectTo;
		};
		add_filter('handle_bulk_actions-onoffice_page_onoffice-forms', $pClosureDuplicateForm, 10, 3);
		add_filter('handle_bulk_actions-table-onoffice_page_onoffice-forms', function(): Table\WP\ListTable {
			return $this->_pFormsTable;
		});
	}
}
