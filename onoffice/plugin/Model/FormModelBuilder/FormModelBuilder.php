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

namespace onOffice\WPlugin\Model\FormModelBuilder;

use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\FilterCall;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\TemplateCall;
use onOffice\WPlugin\Utility\__String;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

abstract class FormModelBuilder
{
	const CONFIG_FIELDS = 'fields';

	/** @var string */
	private $_pageSlug = null;

	/** @var array */
	private $_values = array();

	/** @var array */
	private $_additionalFields = array();


	/**
	 *
	 * @param string $pageSlug
	 *
	 */

	public function __construct($pageSlug)
	{
		$this->_pageSlug = $pageSlug;
	}


	/**
	 *
	 */

	abstract public function generate();


	/**
	 *
	 * @param string $key
	 * @return mixed
	 *
	 */

	protected function getValue(string $key, $default = null)
	{
		if (isset($this->_values[$key]))
		{
			return $this->_values[$key];
		}

		return $default;
	}


	/**
	 *
	 * @return array
	 *
	 */

	protected function readFieldnames($module)
	{
		$pFieldnames = new Fieldnames();
		$pFieldnames->loadLanguage();

		$fieldnames = $pFieldnames->getFieldList($module, true, true);
		$result = array();

		foreach ($fieldnames as $key => $properties)
		{
			$result[$key] = $properties['label'];
		}

		return $result;
	}


	/**
	 *
	 * @param string $directory
	 * @param string $pattern
	 * @return array
	 *
	 */

	protected function readTemplatePaths($directory, $pattern = '*')
	{
		$templateGlobFiles = glob(plugin_dir_path(ONOFFICE_PLUGIN_DIR.'/index.php')
			.'templates.dist/'.$directory.'/'.$pattern.'.php');
		$templateLocalFiles = glob(plugin_dir_path(ONOFFICE_PLUGIN_DIR)
			.'onoffice-personalized/templates/'.$directory.'/'.$pattern.'.php');
		$templatesAll = array_merge($templateGlobFiles, $templateLocalFiles);
		$templates = array();

		foreach ($templatesAll as $value)
		{
			$value = __String::getNew($value)->replace(plugin_dir_path(ONOFFICE_PLUGIN_DIR), '');
			$templates[$value] = $value;
		}

		return $templates;
	}


	/**
	 *
	 * @param string $module
	 * @param string $htmlType
	 * @return InputModelDB
	 *
	 */

	public function createSortableFieldList($module, $htmlType)
	{
		$pInputModelFieldsConfig = $this->getInputModelDBFactory()->create(
			InputModelDBFactory::INPUT_FIELD_CONFIG, null, true);

		$pInputModelFieldsConfig->setHtmlType($htmlType);

		$pFieldnames = new Fieldnames();
		$pFieldnames->loadLanguage();

		$fieldNames = array();

		if (is_array($module)) {
			foreach ($module as $submodule) {
				$fieldNamesModule = $pFieldnames->getFieldList($submodule, true, true);
				$fieldNames = array_merge($fieldNames, $fieldNamesModule);
			}
		} else {
			$fieldNames = $pFieldnames->getFieldList($module, true, true);
		}

		$fieldNames = array_merge($fieldNames, $this->getAdditionalFields());
		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);

		$fields = $this->getValue(self::CONFIG_FIELDS);

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

	protected function readFilters($module)
	{
		$pFilterCall = new FilterCall($module);
		return $pFilterCall->getFilters();
	}


	/**
	 *
	 * @return array
	 *
	 */

	protected function readExposes()
	{
		$pTemplateCall = new TemplateCall(TemplateCall::TEMPLATE_TYPE_EXPOSE);
		return $pTemplateCall->getTemplates();
	}


	/**
	 *
	 * @param string $category
	 * @param array $fieldNames
	 * @param string $categoryLabel
	 * @return InputModelDB
	 *
	 */

	abstract public function createInputModelFieldsConfigByCategory
		($category, $fieldNames, $categoryLabel);

	/** @return string */
	public function getPageSlug()
		{ return $this->_pageSlug; }

	/** @param array $values */
	protected function setValues(array $values)
		{ $this->_values = $values; }

	/** @return array */
	public function getAdditionalFields()
		{ return $this->_additionalFields; }

	/** @param array $additionalFields */
	public function setAdditionalFields($additionalFields)
		{ $this->_additionalFields = $additionalFields; }
}
