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

namespace onOffice\WPlugin\Controller;

use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorGeoPositionFrontend;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Types\FieldsCollection;
use function get_option;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class InputVariableReaderConfigFieldnames
	implements InputVariableReaderConfig
{
	/** @var Fieldnames */
	private $_pFieldnames = null;

	/** @var bool */
	private $_loadComplete = false;


	/**
	 *
	 */

	public function __construct(Fieldnames $pFieldnames = null)
	{
		if ($pFieldnames === null) {
			$pFieldsCollection = new FieldModuleCollectionDecoratorGeoPositionFrontend
				(new FieldsCollection());
			$this->_pFieldnames = new Fieldnames($pFieldsCollection);
		} else {
			$this->_pFieldnames = $pFieldnames;
		}
	}


	/**
	 *
	 */

	private function lazyLoad()
	{
		if (!$this->_loadComplete) {
			$this->_pFieldnames->loadLanguage();
			$this->_loadComplete = true;
		}
	}


	/**
	 *
	 * @param string $field
	 * @param string $module
	 * @return string
	 *
	 */

	public function getFieldType(string $field, string $module): string
	{
		$this->lazyLoad();
		$fieldInformation = $this->_pFieldnames->getFieldInformation($field, $module);
		return $fieldInformation['type'];
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getTimezoneString(): string
	{
		return get_option('timezone_string', '');
	}


	/**
	 * Get and process filtered input variables
	 *
	 * @param int $var Input type (INPUT_GET, INPUT_POST)
	 * @param string $name Parameter name
	 * @param int $sanitizer Filter type (e.g. FILTER_SANITIZE_FULL_SPECIAL_CHARS)
	 * @return mixed Processed value(s)
	 */
	public function getFilterVariable(int $var, string $name, int $sanitizer)
	{
		// Get raw input and force it to be an array
		$rawValue = filter_input($var, $name, FILTER_UNSAFE_RAW, FILTER_FORCE_ARRAY);
		
		// Early return if not an array (e.g. null or false)
		if (!is_array($rawValue)) {
			return $rawValue;
		}
		
		// Process each array value
		$processedValue = array_map(function($item) use ($sanitizer) {
			// First decode any HTML entities to get clean text
			$value = html_entity_decode($item, ENT_QUOTES | ENT_HTML5, 'UTF-8');
			
			// Only apply additional sanitization if a specific filter is requested
			if ($sanitizer !== FILTER_DEFAULT) {
				if ($sanitizer === FILTER_SANITIZE_FULL_SPECIAL_CHARS) {
					// This preserves special characters like quotes while ensuring security
					$value = wp_strip_all_tags($value);
				} else {
					// Apply the original sanitizer for other filter types
					$value = filter_var($value, $sanitizer);
				}
			}
			
			return $value;
		}, $rawValue);
	
		return $processedValue;
	}
 

	/**
	 *
	 * @param string $name
	 * @return bool
	 *
	 */

	public function getIsRequestVarArray(string $name): bool
	{
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public search parameter check, no side effects
		return is_array($_REQUEST[$name] ?? null);
	}
}
