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

use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\DataFormConfiguration\UnknownFormException;
use onOffice\WPlugin\Model;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderForm;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class AdminPageFormSettings
	extends AdminPageSettingsBase
{
	/**
	 *
	 * @param string $pageSlug
	 *
	 */

	public function __construct($pageSlug)
	{
		$this->setPageTitle(__('Edit Form', 'onoffice'));
		parent::__construct($pageSlug);
	}


	/**
	 *
	 */

protected function buildForms()
	{
		add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );
		$pFormModelBuilder = new FormModelBuilderForm($this->getPageSlug());
		$pFormModel = $pFormModelBuilder->generate($this->getListViewId());
		$this->addFormModel($pFormModel);

		$pInputModelName = $pFormModelBuilder->createInputModelName();
		$pFormModelName = new Model\FormModel();
		$pFormModelName->setPageSlug($this->getPageSlug());
		$pFormModelName->setGroupSlug(self::FORM_RECORD_NAME);
		$pFormModelName->setLabel(__('choose name', 'onoffice'));
		$pFormModelName->addInputModel($pInputModelName);
		$this->addFormModel($pFormModelName);

		$pInputModelTemplate = $pFormModelBuilder->createInputModelTemplate();
		$pFormModelLayoutDesign = new Model\FormModel();
		$pFormModelLayoutDesign->setPageSlug($this->getPageSlug());
		$pFormModelLayoutDesign->setGroupSlug(AdminPageEstateListSettings::FORM_VIEW_LAYOUT_DESIGN);
		$pFormModelLayoutDesign->setLabel(__('Layout & Design', 'onoffice'));
		$pFormModelLayoutDesign->addInputModel($pInputModelTemplate);
		$this->addFormModel($pFormModelLayoutDesign);

		$fieldNames = $this->readFieldnamesByContent(onOfficeSDK::MODULE_ESTATE);

		foreach ($fieldNames as $category => $fields)
		{
			$pInputModelFieldsConfig = $pFormModelBuilder->createInputModelFieldsConfigByCategory($category, $fields);
			$pFormModelFieldsConfig = new Model\FormModel();
			$pFormModelFieldsConfig->setPageSlug($this->getPageSlug());
			$pFormModelFieldsConfig->setGroupSlug($category);
			$pFormModelFieldsConfig->setLabel($category);
			$pFormModelFieldsConfig->addInputModel($pInputModelFieldsConfig);
			$this->addFormModel($pFormModelFieldsConfig);
		}

		$pInputModelSortableFields = $pFormModelBuilder->createSortableFieldList();
		$pFormModelSortableFields = new Model\FormModel();
		$pFormModelSortableFields->setPageSlug($this->getPageSlug());
		$pFormModelSortableFields->setGroupSlug(AdminPageEstateListSettings::FORM_VIEW_SORTABLE_FIELDS_CONFIG);
		$pFormModelSortableFields->setLabel(__('Fields Configuration', 'onoffice'));
		$pFormModelSortableFields->addInputModel($pInputModelSortableFields);
		$this->addFormModel($pFormModelSortableFields);
	}


	/**
	 *
	 */

	protected function generateMetaBoxes()
	{
		$pFormLayoutDesign = $this->getFormModelByGroupSlug(AdminPageEstateListSettings::FORM_VIEW_LAYOUT_DESIGN);
		$this->createMetaBoxByForm($pFormLayoutDesign, 'normal');
	}


	/**
	 *
	 */

	protected function generateAccordionBoxes()
	{
		$fieldNames = array_keys($this->readFieldnamesByContent(onOfficeSDK::MODULE_ESTATE));

		foreach ($fieldNames as $category)
		{
			$pFormFieldsConfig = $this->getFormModelByGroupSlug($category);
			$this->createMetaBoxByForm($pFormFieldsConfig, 'side');
		}
	}


	/**
	 *
	 * @param int $recordId
	 * @throws UnknownFormException
	 *
	 */

	protected function validate($recordId = null)
	{
		if ($recordId == null) {
			return;
		}

		$pRecordReadManager = new RecordManagerReadForm();
		$pWpDb = $pRecordReadManager->getWpdb();
		$prefix = $pRecordReadManager->getTablePrefix();
		$value = $pWpDb->get_var('SELECT form_id FROM `'.esc_sql($prefix)
			.'oo_plugin_forms` WHERE `form_id` = "'.esc_sql($recordId).'"');

		if ($value != $recordId) {
			throw new UnknownFormException;
		}
	}


	/**
	 *
	 * @param array $row
	 * @param \stdClass $pResult
	 * @param int $recordId
	 *
	 */

	protected function updateValues(array $row, \stdClass $pResult, $recordId = null)
	{
		$result = false;

		if ($recordId != null)
		{
			// update by row
		}
		else
		{
			// insert
			$result = ($recordId != null);
		}

		$pResult->result = $result;
		$pResult->record_id = $recordId;
	}
}
