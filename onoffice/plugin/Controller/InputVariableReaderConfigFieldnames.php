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
	 *
	 * @param int $var
	 * @param string $name
	 * @param int $sanitizer
	 * @return array
	 *
	 */

	public function getFilterVariable(int $var, string $name, int $sanitizer)
	{
		// filter_input() does not work in test environment
		// @codeCoverageIgnoreStart
		return filter_input($var, $name, $sanitizer, FILTER_FORCE_ARRAY);
		// @codeCoverageIgnoreEnd
	}


	/**
	 *
	 * @param string $name
	 * @return bool
	 *
	 */

	public function getIsRequestVarArray(string $name): bool
	{
		return is_array($_REQUEST[$name] ?? null);
	}
}
