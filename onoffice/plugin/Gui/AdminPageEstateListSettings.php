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

use onOffice\WPlugin\Model;
use onOffice\WPlugin\Form\InputModelRenderer;
use onOffice\WPlugin\Form\FormModelBuilderEstateListSettings;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class AdminPageEstateListSettings
	extends AdminPageAjax
{
	/** caution: also needs to be set in Javascript */
	const POST_RECORD_ID = 'record_id';

	/** @var int */
	private $_listViewId = null;


	/**
	 *
	 * @param string $pageSlug
	 *
	 */

	public function __construct($pageSlug)
	{
		parent::__construct($pageSlug);
		$this->_listViewId = filter_input(INPUT_GET, 'listViewId');
	}

	/**
	 *
	 * @return bool
	 *
	 */

	protected function buildForms()
	{
		$pFormModelBuilder = new FormModelBuilderEstateListSettings($this->getPageSlug());
		$pFormModel = $pFormModelBuilder->generate($this->_listViewId);

		$this->addFormModel($pFormModel);
	}


	/**
	 *
	 */

	public function renderContent()
	{
		$this->generatePageMainTitle(__('Edit list view', 'onoffice'));

		echo '<div id="onoffice-ajax">';

		foreach ($this->getFormModels() as $pFormModel)
		{
			$pFormBuilder = new InputModelRenderer($pFormModel);
			$pFormBuilder->buildForm();
		}

		do_settings_sections( $this->getPageSlug() );
		submit_button(null, 'primary', 'send_ajax');

		echo '</div>';
		echo '<script>onOffice.ajaxSaver = new onOffice.ajaxSaver("onoffice-ajax");';
		echo 'onOffice.ajaxSaver.register();';
		echo '</script>';
	}


	/**
	 *
	 */

	public function ajax_action()
	{
		$this->buildForms();
		$action = filter_input(INPUT_POST, 'action');
		$nonce = filter_input(INPUT_POST, 'nonce');
		$recordId = filter_input(INPUT_POST, self::POST_RECORD_ID);

		if (!wp_verify_nonce($nonce, $action)) {
			wp_die();
		}

		$values = json_decode(filter_input(INPUT_POST, 'values'));

		$pInputModelDBAdapterRow = new Model\InputModelDBAdapterRow();

		foreach ($this->getFormModels() as $pFormModel) {
			foreach ($pFormModel->getInputModel() as $pInputModel) {
				if ($pInputModel instanceof Model\InputModelDB) {
					$identifier = $pInputModel->getIdentifier();
					$value = isset($values->$identifier) ? $values->$identifier : null;
					$pInputModel->setValue($value);
					$pInputModelDBAdapterRow->addInputModelDB($pInputModel);
				}
			}
			$row = $pInputModelDBAdapterRow->createUpdateValuesByTable();
		}

		var_dump($recordId);
		var_dump($row);
		wp_die();
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getEnqueueData()
	{
		return array(
			self::POST_RECORD_ID => $this->getListViewId(),
		);
	}



	/** @return string */
	public function getListViewId()
		{ return $this->_listViewId; }
}
