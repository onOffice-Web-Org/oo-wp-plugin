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

namespace onOffice\WPlugin\Model\FormModelBuilder;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigAddress;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelOption;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class FormModelBuilderAddress
	extends FormModelBuilder
{
	/** @var InputModelDBFactory */
	private $_pInputModelDBFactory = null;


	/**
	 *
	 * @param string $pageSlug
	 *
	 */

	public function __construct($pageSlug)
	{
		parent::__construct($pageSlug);
		$pInputModelDBFactoryConfig = new InputModelDBFactoryConfigAddress();
		$this->_pInputModelDBFactory = new InputModelDBFactory($pInputModelDBFactoryConfig);
	}


	/**
	 *
	 * @param string $category
	 * @param array $fieldNames
	 * @return InputModelDB
	 *
	 */

	public function createInputModelFieldsConfigByCategory($category, $fieldNames)
	{
		$pInputModelFieldsConfig = $this->_pInputModelDBFactory->create(
			InputModelDBFactory::INPUT_FIELD_CONFIG, $category, true);

		$pInputModelFieldsConfig->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX_BUTTON);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);
		$pInputModelFieldsConfig->setId($category);
		$pInputModelFieldsConfig->setLabel($category);
		$fields = $this->getValue(DataListView::FIELDS);

		if (null == $fields)
		{
			$fields = array();
		}

		$pInputModelFieldsConfig->setValue($fields);

		return $pInputModelFieldsConfig;
	}


	/**
	 *
	 */

	public function generate($listViewId = null)
	{
		if ($listViewId !== null)
		{
//			$pRecordReadManager = new RecordManagerReadListViewAddress();
//			$values = $pRecordReadManager->getRowById($listViewId);
//			$this->setValues($values);
		}

		$pFormModel = new FormModel();
		$pFormModel->setLabel(__('List View', 'onoffice'));
		$pFormModel->setGroupSlug('onoffice-listview-address-settings-main');
		$pFormModel->setPageSlug($this->getPageSlug());

		return $pFormModel;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelName()
	{
		$labelName = __('View Name', 'onoffice');

		$pInputModelName = $this->_pInputModelDBFactory->create
			(InputModelDBFactory::INPUT_LISTNAME, null);
		$pInputModelName->setPlaceholder($labelName);
		$pInputModelName->setHtmlType(InputModelOption::HTML_TYPE_TEXT);
		$pInputModelName->setValue($this->getValue($pInputModelName->getField()));

		return $pInputModelName;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelTemplate()
	{
		$labelTemplate = __('Template', 'onoffice');
		$selectedTemplate = $this->getValue('template');

		$pInputModelTemplate = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_TEMPLATE, $labelTemplate);
		$pInputModelTemplate->setHtmlType(InputModelOption::HTML_TYPE_SELECT);

		$pInputModelTemplate->setValuesAvailable($this->readTemplatePaths('address'));
		$pInputModelTemplate->setValue($selectedTemplate);

		return $pInputModelTemplate;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelFilter()
	{
		$labelFiltername = __('Filter', 'onoffice');
		$pInputModelFiltername = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_FILTERID, $labelFiltername);
		$pInputModelFiltername->setHtmlType(InputModelOption::HTML_TYPE_SELECT);

		$availableFilters = array(0 => '') + $this->readFilters(onOfficeSDK::MODULE_ADDRESS);

		$pInputModelFiltername->setValuesAvailable($availableFilters);
		$filteridSelected = $this->getValue($pInputModelFiltername->getField());
		$pInputModelFiltername->setValue($filteridSelected);

		return $pInputModelFiltername;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelRecordsPerPage()
	{
		$labelRecordsPerPage = __('Records per Page', 'onoffice');
		$pInputModelRecordsPerPage = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_RECORDS_PER_PAGE, $labelRecordsPerPage);
		$pInputModelRecordsPerPage->setHtmlType(InputModelOption::HTML_TYPE_SELECT);
		$pInputModelRecordsPerPage->setValuesAvailable(array(
				'5' => '5',
				'9' => '9',
				'10' => '10',
				'12' => '12',
				'15' => '15'
			)
		);

		$pInputModelRecordsPerPage->setValue($this->getValue('recordsPerPage'));
		return $pInputModelRecordsPerPage;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelSortBy()
	{
		$labelSortBy = __('Sort by', 'onoffice');

		$pInputModelSortBy = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_SORTBY, $labelSortBy);
		$pInputModelSortBy->setHtmlType(InputModelOption::HTML_TYPE_SELECT);

		$fieldnames = $this->getOnlyDefaultSortByFields();

		$pInputModelSortBy->setValuesAvailable($fieldnames);
		$pInputModelSortBy->setValue($this->getValue($pInputModelSortBy->getField()));

		return $pInputModelSortBy;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelSortOrder()
	{
		$labelSortOrder = __('Sort order', 'onoffice');
		$pInputModelSortOrder = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_SORTORDER, $labelSortOrder);
		$pInputModelSortOrder->setHtmlType(InputModelOption::HTML_TYPE_SELECT);
		$pInputModelSortOrder->setValuesAvailable(array(
			'ASC' => __('Ascending', 'onoffice'),
			'DESC' => __('Descending', 'onoffice'),
		));
		$pInputModelSortOrder->setValue($this->getValue($pInputModelSortOrder->getField()));

		return $pInputModelSortOrder;
	}



	/** @todo: remove */
	/** @return InputModelDBFactory */
	protected function getInputModelDBFactory()
		{ return $this->_pInputModelDBFactory; }
}
