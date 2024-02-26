<?php

/**
 *
 *    Copyright (C) 2017-2019 onOffice GmbH
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

use onOffice\WPlugin\API\APIClientCredentialsException;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\FilterCall;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Template\TemplateCall;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Utility\__String;
use const ONOFFICE_PLUGIN_DIR;
use function plugin_dir_path;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

abstract class FormModelBuilder
{
	const CONFIG_FIELDS = 'fields';

	/** @var array */
	private $_values = array();

	/** @var Fieldnames */
	private $_pFieldnames = null;

	/**
	 * @param string $pageSlug
	 * @return FormModel
	 */

	abstract public function generate(string $pageSlug): FormModel;

	/**
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 *
	 */

	protected function getValue(string $key, $default = null)
	{
		if (isset($this->_values[$key])) {
			return $this->_values[$key];
		}

		return $default;
	}


	/**
	 *
	 * @param string $module
	 * @param bool $withInactive
	 * @return array
	 *
	 */

	protected function readFieldnames($module, bool $withInactive = false)
	{
		try {
			$this->_pFieldnames->loadLanguage();
			$fieldnames = $this->_pFieldnames->getFieldList($module);

			if ($withInactive) {
				$pFieldnamesInactive = new Fieldnames(new FieldsCollection(), true);
				$pFieldnamesInactive->loadLanguage();
				$fieldnames += $pFieldnamesInactive->getFieldList($module);
			}
			foreach ($fieldnames as $key => $field) {
				if (!FieldTypes::isSupportType($field['type'])) {
					unset($fieldnames[$key]);
				}
			}
			$resultLabel = array_column($fieldnames, 'label');
			$result = array_combine(array_keys($fieldnames), $resultLabel);
		} catch (APIClientCredentialsException $pCredentialsException) {
			$result = [];
		}

		return $result;
	}


	/**
	 * @param $directory
	 * @param $pattern
	 *
	 * @return array
	 */

	protected function readTemplatePaths( $directory, $pattern = '*' ) {
		$templatesAll[ TemplateCall::TEMPLATE_FOLDER_INCLUDED ] = glob( plugin_dir_path( ONOFFICE_PLUGIN_DIR
										. '/index.php' ) . 'templates.dist/' . $directory . '/' . $pattern . '.php' );
		$templatesAll[ TemplateCall::TEMPLATE_FOLDER_PLUGIN ]   = glob( plugin_dir_path( ONOFFICE_PLUGIN_DIR )
										. 'onoffice-personalized/templates/' . $directory . '/' . $pattern . '.php' );
		$templatesAll[ TemplateCall::TEMPLATE_FOLDER_THEME ]    = glob( get_stylesheet_directory()
										. '/onoffice-theme/templates/' . $directory . '/' . $pattern . '.php' );

		return ( new TemplateCall() )->formatTemplatesData( array_filter( $templatesAll ), $directory );
	}

	/**
	 *
	 * @param string $module
	 * @param string $htmlType
	 * @return InputModelDB
	 *
	 */

	public function createSortableFieldList($module, string $htmlType)
	{
		$fieldNames = $this->getFieldNamesByModule($module);
		$pInputModelFieldsConfig = $this->getInputModelDBFactory()->create(
			InputModelDBFactory::INPUT_FIELD_CONFIG, null, true);

		$pInputModelFieldsConfig->setHtmlType($htmlType);

		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);

		$fields = $this->getValue(self::CONFIG_FIELDS) ?? [];
		$pInputModelFieldsConfig->setValue($fields);

		return $pInputModelFieldsConfig;
	}

	/**
	 * @param $module
	 * @return array
	 */
	private function getFieldNamesByModule($module): array
	{
		$fieldNames = [];
		try {
			$this->_pFieldnames->loadLanguage();
			if (is_array($module)) {
				foreach ($module as $submodule) {
					$fieldNamesModule = $this->_pFieldnames->getFieldList($submodule);
					$fieldNames = array_merge($fieldNames, $fieldNamesModule);
				}
			} else {
				$fieldNames = $this->_pFieldnames->getFieldList($module);
			}
		} catch (APIClientCredentialsException $pCredentialsException) {}

		return $fieldNames;
	}

	/**
	 *
	 * @param $module
	 * @param string $htmlType
	 * @return InputModelDB
	 *
	 */

	public function createSearchFieldForFieldLists($module, string $htmlType)
	{
		$fieldNames = $this->getFieldNamesByModule($module);
		$pInputModelFieldsConfig = $this->getInputModelDBFactory()->create(
			InputModelDBFactory::INPUT_FIELD_CONFIG, null, true);

		$pInputModelFieldsConfig->setHtmlType($htmlType);

		$pInputModelFieldsConfig->setValuesAvailable($this->groupByContent($fieldNames));

		$fields = $this->getValue(self::CONFIG_FIELDS) ?? [];
		$pInputModelFieldsConfig->setValue($fields);

		return $pInputModelFieldsConfig;
	}

	/**
	 *
	 * @param array $fieldNames
	 * @return array
	 *
	 */
	public function groupByContent(array $fieldNames): array
	{
		$resultByContent = [];
	
		$listTypeUnSupported = ['user', 'datei', 'redhint', 'blackhint', 'dividingline'];
		foreach ($fieldNames as $key => $properties) {
			if (in_array($properties['type'], $listTypeUnSupported)) {
				continue;
			}
			$content = $properties['content'];
			$resultByContent[$content][$key] = $properties;
		}

		if (empty($resultByContent)) {
			return [];
		}

		return array_merge(...array_values($resultByContent));
	}

	/**
	 *
	 * @return array
	 *
	 */

	protected function readFilters($module)
	{
		try {
			$pFilterCall = new FilterCall($module);
			return $pFilterCall->getFilters();
		} catch (APIClientCredentialsException $pCredentialsException) {
			return [];
		}
	}


	/**
	 *
	 * @return array
	 *
	 */

	protected function readExposes()
	{
		try {
			$pTemplateCall = new TemplateCall();
			$pTemplateCall->loadTemplates();
			return $pTemplateCall->getTemplates();
		} catch (APIClientCredentialsException $pException) {
			return [];
		}
	}


	/**
	 *
	 * @param string $category
	 * @param array $fieldNames
	 * @param string $categoryLabel
	 * @return InputModelDB
	 *
	 */

	abstract public function createButtonModelFieldsConfigByCategory
	($category, $fieldNames, $categoryLabel);

	abstract public function createInputModelFieldsConfigByCategory
		($category, $fieldNames, $categoryLabel);

	/** @return Fieldnames */
	protected function getFieldnames(): Fieldnames
		{ return $this->_pFieldnames; }

	/** @param Fieldnames $pFieldnames */
	protected function setFieldnames(Fieldnames $pFieldnames)
		{ $this->_pFieldnames = $pFieldnames; }

	/** @param array $values */
	public function setValues(array $values)
		{ $this->_values = $values; }
}
