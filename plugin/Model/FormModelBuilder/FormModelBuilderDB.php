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

use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Field\FieldDefaultSorting;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelOption;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

abstract class FormModelBuilderDB
	extends FormModelBuilder
{
	/** @var InputModelDBFactory */
	private $_pInputModelDBFactory = null;


	/**
	 *
	 * @param string $category
	 * @param array $fieldNames
	 * @param string $categoryLabel
	 * @return InputModelDB
	 *
	 */

	public function createInputModelFieldsConfigByCategory($category, $fieldNames, $categoryLabel)
	{
		$pInputModelFieldsConfig = $this->getInputModelDBFactory()->create(
			InputModelDBFactory::INPUT_FIELD_CONFIG, $category, true);

		$pInputModelFieldsConfig->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX_BUTTON);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);
		$pInputModelFieldsConfig->setId($category);
		$pInputModelFieldsConfig->setLabel($categoryLabel);
		$fields = $this->getValue(DataListView::FIELDS);

		if (null == $fields) {
			$fields = array();
		}

		$pInputModelFieldsConfig->setValue($fields);

		return $pInputModelFieldsConfig;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelName()
	{
		$labelName = __('View Name', 'onoffice-for-wp-websites');

		$pInputModelName = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_LISTNAME, null);
		$pInputModelName->setPlaceholder($labelName);
		$pInputModelName->setHtmlType(InputModelOption::HTML_TYPE_TEXT);
		$pInputModelName->setValue($this->getValue($pInputModelName->getField()));

		return $pInputModelName;
	}


	/**
	 *
	 * @param string $module
	 * @return InputModelDB
	 *
	 */

	public function createInputModelSortBy($module)
	{
		$labelSortBy = __('Sort by', 'onoffice-for-wp-websites');

		$pInputModelSortBy = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_SORTBY, $labelSortBy);
		$pInputModelSortBy->setHtmlType(InputModelOption::HTML_TYPE_SELECT);

		$fieldnames = $this->getOnlyDefaultSortByFields($module);

		$pInputModelSortBy->setValuesAvailable($fieldnames);
		$pInputModelSortBy->setValue($this->getValue($pInputModelSortBy->getField()));

		return $pInputModelSortBy;
	}


	/**
	 *
	 * @param string $module
	 * @return array
	 *
	 */

	public function getOnlyDefaultSortByFields($module)
	{
		$fieldnames = $this->readFieldnames($module, false);
		$pDefaultSorting = new FieldDefaultSorting();
		$defaultFields = $pDefaultSorting->getDefaultSortByFields($module);
		natcasesort($fieldnames);
		$defaultActiveFields = array();

		foreach ($defaultFields as $value) {
			if (array_key_exists($value, $fieldnames)) {
				$defaultActiveFields[$value] = $fieldnames[$value];
			}
		}

		return $defaultActiveFields;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelRecordsPerPage()
	{
		$labelRecordsPerPage = __('Records per Page', 'onoffice-for-wp-websites');
		$pInputModelRecordsPerPage = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_RECORDS_PER_PAGE, $labelRecordsPerPage);
		$pInputModelRecordsPerPage->setHtmlType(InputModelOption::HTML_TYPE_TEXT);
		$pInputModelRecordsPerPage->setValue($this->getValue('recordsPerPage'));

		return $pInputModelRecordsPerPage;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelSortOrder()
	{
		$labelSortOrder = __('Sort order', 'onoffice-for-wp-websites');
		$pInputModelSortOrder = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_SORTORDER, $labelSortOrder);
		$pInputModelSortOrder->setHtmlType(InputModelOption::HTML_TYPE_SELECT);
		$pInputModelSortOrder->setValuesAvailable(array(
			'ASC' => __('Ascending', 'onoffice-for-wp-websites'),
			'DESC' => __('Descending', 'onoffice-for-wp-websites'),
		));
		$pInputModelSortOrder->setValue($this->getValue($pInputModelSortOrder->getField()));

		return $pInputModelSortOrder;
	}


	/**
	 *
	 * @param string $path
	 * @return InputModelDB
	 *
	 */

	public function createInputModelTemplate($path)
	{
		$labelTemplate = __('Template', 'onoffice-for-wp-websites');
		$selectedTemplate = $this->getValue('template');

		$pInputModelTemplate = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_TEMPLATE, $labelTemplate);
		$pInputModelTemplate->setHtmlType(InputModelOption::HTML_TYPE_SELECT);

		$pInputModelTemplate->setValuesAvailable($this->readTemplatePaths($path));
		$pInputModelTemplate->setValue($selectedTemplate);

		return $pInputModelTemplate;
	}


	/** @param InputModelDBFactory $pInputModelDBFactory */
	protected function setInputModelDBFactory(InputModelDBFactory $pInputModelDBFactory)
		{ $this->_pInputModelDBFactory = $pInputModelDBFactory; }

	/** @return InputModelDBFactory */
	protected function getInputModelDBFactory(): InputModelDBFactory
		{ return $this->_pInputModelDBFactory; }
}
