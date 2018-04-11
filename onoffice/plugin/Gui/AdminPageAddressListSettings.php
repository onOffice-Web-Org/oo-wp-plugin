<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBAddress;
use onOffice\WPlugin\Model\InputModelBase;
use stdClass;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class AdminPageAddressListSettings
	extends AdminPageSettingsBase
{
	/** @var FormModelBuilderDBAddress */
	private $_pFormModelBuilderAddress = null;

	/**
	 *
	 * @param string $pageSlug
	 *
	 */

	public function __construct($pageSlug)
	{
		parent::__construct($pageSlug);
		$this->setPageTitle(__('Edit Address List', 'onoffice'));
	}


	/**
	 *
	 */

	protected function buildForms()
	{
		$this->_pFormModelBuilderAddress = new FormModelBuilderDBAddress($this->getPageSlug());
		$pFormModel = $this->_pFormModelBuilderAddress->generate($this->getListViewId());
		$this->addFormModel($pFormModel);

		$fieldNames = $this->readFieldnamesByContent(onOfficeSDK::MODULE_ADDRESS);

		$this->addFieldsConfiguration(onOfficeSDK::MODULE_ADDRESS, $this->_pFormModelBuilderAddress, $fieldNames);
		$this->addSortableFieldsList(array(onOfficeSDK::MODULE_ADDRESS), $this->_pFormModelBuilderAddress,
			InputModelBase::HTML_TYPE_COMPLEX_SORTABLE_DETAIL_LIST);

		$this->addFormModelName();
		$this->addFormModelTemplate();
		$this->addFormModelRecordsFilter();
	}


	/**
	 *
	 */

	private function addFormModelName()
	{
		$pInputModelName = $this->_pFormModelBuilderAddress->createInputModelName();
		$pFormModelName = new FormModel();
		$pFormModelName->setPageSlug($this->getPageSlug());
		$pFormModelName->setGroupSlug(self::FORM_RECORD_NAME);
		$pFormModelName->setLabel(__('choose name', 'onoffice'));
		$pFormModelName->addInputModel($pInputModelName);
		$this->addFormModel($pFormModelName);
	}


	/**
	 *
	 */

	private function addFormModelTemplate()
	{
		$pInputModelTemplate = $this->_pFormModelBuilderAddress->createInputModelTemplate('address');
		$pFormModelLayoutDesign = new FormModel();
		$pFormModelLayoutDesign->setPageSlug($this->getPageSlug());
		$pFormModelLayoutDesign->setGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$pFormModelLayoutDesign->setLabel(__('Layout & Design', 'onoffice'));
		$pFormModelLayoutDesign->addInputModel($pInputModelTemplate);
		$this->addFormModel($pFormModelLayoutDesign);
	}


	/**
	 *
	 */

	private function addFormModelRecordsFilter()
	{
		$pInputModelFilter = $this->_pFormModelBuilderAddress->createInputModelFilter();
		$pInputModelRecordCount = $this->_pFormModelBuilderAddress->createInputModelRecordsPerPage();
		$pInputModelSortBy = $this->_pFormModelBuilderAddress->createInputModelSortBy
			(onOfficeSDK::MODULE_ADDRESS);
		$pInputModelSortOrder = $this->_pFormModelBuilderAddress->createInputModelSortOrder();
		$pFormModelFilterRecords = new FormModel();
		$pFormModelFilterRecords->setPageSlug($this->getPageSlug());
		$pFormModelFilterRecords->setGroupSlug(self::FORM_VIEW_RECORDS_FILTER);
		$pFormModelFilterRecords->setLabel(__('Layout & Design', 'onoffice'));
		$pFormModelFilterRecords->addInputModel($pInputModelFilter);
		$pFormModelFilterRecords->addInputModel($pInputModelRecordCount);
		$pFormModelFilterRecords->addInputModel($pInputModelSortBy);
		$pFormModelFilterRecords->addInputModel($pInputModelSortOrder);
		$this->addFormModel($pFormModelFilterRecords);
	}


	/**
	 *
	 */

	protected function generateMetaBoxes()
	{
		$pFormLayoutDesign = $this->getFormModelByGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$this->createMetaBoxByForm($pFormLayoutDesign, 'side');

		$pFormFilterRecords = $this->getFormModelByGroupSlug(self::FORM_VIEW_RECORDS_FILTER);
		$this->createMetaBoxByForm($pFormFilterRecords, 'normal');
	}


	/**
	 *
	 */

	protected function generateAccordionBoxes()
	{
		$this->cleanPreviousBoxes();
		$fieldNames = array_keys($this->readFieldnamesByContent(onOfficeSDK::MODULE_ADDRESS));

		foreach ($fieldNames as $category)
		{
			$pFormFieldsConfig = $this->getFormModelByGroupSlug($category);
			$this->createMetaBoxByForm($pFormFieldsConfig, 'side');
		}
	}



	protected function updateValues(array $row, stdClass $pResult, $recordId = null)
	{

	}

	protected function validate()
	{

	}
}
