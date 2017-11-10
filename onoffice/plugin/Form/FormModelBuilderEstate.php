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

namespace onOffice\WPlugin\Form;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\FilterCall;
use onOffice\WPlugin\TemplateCall;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Model\InputModel\ListView\InputModelDBFactory;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class FormModelBuilderEstate
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
		$this->_pInputModelDBFactory = new InputModelDBFactory();
	}


	/**
	 *
	 * @return array
	 *
	 */

	protected function readFilters()
	{
		$pFilterCall = new FilterCall(\onOffice\SDK\onOfficeSDK::MODULE_ESTATE);
		return $pFilterCall->getFilters();
	}


	/**
	 *
	 * @return array
	 *
	 */

	protected function readFieldnames()
	{
		$pFieldnames = new \onOffice\WPlugin\Fieldnames();
		$pFieldnames->loadLanguage();

		$fieldnames = $pFieldnames->getFieldList(onOfficeSDK::MODULE_ESTATE);
		$result = array();

		foreach ($fieldnames as $key => $properties)
		{
			$result[$key] = $properties['label'];
		}

		return $result;
	}


	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	public function createInputModelFieldsConfig()
	{
		$pInputModelFieldsConfig = $this->_pInputModelDBFactory->create(
			InputModelDBFactory::INPUT_FIELD_CONFIG, null, true);

		$fieldNames = $this->readFieldnames();
		$pInputModelFieldsConfig->setHtmlType(InputModelBase::HTML_TYPE_COMPLEX_SORTABLE_CHECKBOX_LIST);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);
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
	 * @return array
	 *
	 */

	protected function readExposes()
	{
		$pTemplateCall = new \onOffice\WPlugin\TemplateCall(TemplateCall::TEMPLATE_TYPE_EXPOSE);
		return $pTemplateCall->getTemplates();
	}


	/**
	 *
	 * @return InputModelDBFactory
	 *
	 */

	protected function getInputModelDBFactory() {
		return $this->_pInputModelDBFactory;
	}
}
