<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

namespace onOffice\WPlugin\Model\InputModelBuilder;

use Generator;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigGeoFields;
use onOffice\WPlugin\Model\InputModelLabel;
use onOffice\WPlugin\Model\InputModelOption;
use function __;

/**
 *
 */

class InputModelBuilderGeoRange
{
	/** @var InputModelDBFactory */
	private $_pInputModelFactory = null;

	/** @var array */
	private $_values = [];


	/**
	 *
	 * @param string $module
	 * @param array $values
	 *
	 */

	public function __construct(string $module, array $values)
	{
		$pFactoryConfig = new InputModelDBFactoryConfigGeoFields($module);
		$this->_pInputModelFactory = new InputModelDBFactory($pFactoryConfig);
		$this->_values = $values;
	}


	/**
	 *
	 * @return Generator
	 *
	 */

	public function build(): Generator
	{
		$activeFields = $this->getFieldnamesActiveCheckbox();
		$activeLabel = __('Activate', 'onoffice');

		foreach ($activeFields as $field => $label) {
			yield new InputModelLabel($label, null);
			$pInputModelGeoCountry = $this->_pInputModelFactory->create($field, $activeLabel);
			$pInputModelGeoCountry->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
			$pInputModelGeoCountry->setValuesAvailable(1);
			$isEnabled = $this->_values[$field] ?? 0;
			$pInputModelGeoCountry->setValue($isEnabled);
			yield $pInputModelGeoCountry;
		}
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getFieldnamesActiveCheckbox(): array
	{
		return [
			InputModelDBFactoryConfigGeoFields::FIELDNAME_COUNTRY_ACTIVE => __('Country', 'onoffice'),
			InputModelDBFactoryConfigGeoFields::FIELDNAME_STREET_ACTIVE => __('Street', 'onoffice'),
			InputModelDBFactoryConfigGeoFields::FIELDNAME_ZIP_ACTIVE => __('Postal Code', 'onoffice'),
			InputModelDBFactoryConfigGeoFields::FIELDNAME_RADIUS_ACTIVE => __('Radius', 'onoffice'),
		];
	}
}
