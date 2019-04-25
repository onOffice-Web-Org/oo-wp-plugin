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
use onOffice\WPlugin\Controller\GeoPositionFieldHandler;
use onOffice\WPlugin\Controller\ViewProperty;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigGeoFields;
use onOffice\WPlugin\Model\InputModelDB;
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

	/** @var GeoPositionFieldHandler */
	private $_pGeoPositionFieldHandler = null;


	/**
	 *
	 * @param string $module
	 * @param GeoPositionFieldHandler $pGeoPositionFieldHandler
	 * @param ViewProperty $pView
	 *
	 */

	public function __construct(string $module, GeoPositionFieldHandler $pGeoPositionFieldHandler = null)
	{
		$pFactoryConfig = new InputModelDBFactoryConfigGeoFields($module);
		$this->_pInputModelFactory = new InputModelDBFactory($pFactoryConfig);
		$this->_pGeoPositionFieldHandler = $pGeoPositionFieldHandler ?? new GeoPositionFieldHandler();
	}


	/**
	 *
	 * @return Generator
	 *
	 */

	public function build(ViewProperty $pView): Generator
	{
		$this->_pGeoPositionFieldHandler->readValues($pView);
		$activeFields = $this->getFieldnamesActiveCheckbox();
		$activeGeoFields = $this->_pGeoPositionFieldHandler->getActiveFields();

		foreach ($activeFields as $field => $label) {
			$pInputModelGeoCountry = $this->_pInputModelFactory->create($field, $label);
			$pInputModelGeoCountry->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
			$pInputModelGeoCountry->setValuesAvailable(1);
			$isEnabled = array_key_exists($field, $activeGeoFields);
			$pInputModelGeoCountry->setValue((int)$isEnabled);
			yield $pInputModelGeoCountry;
		}

		yield $this->generateInputRadius();
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	private function generateInputRadius(): InputModelDB
	{
		$pInputModelRadius = $this->_pInputModelFactory->create
			(InputModelDBFactoryConfigGeoFields::FIELDNAME_RADIUS, __('Default Value for Radius', 'onoffice'));
		$pInputModelRadius->setHtmlType(InputModelOption::HTML_TYPE_SELECT);

		$valuesAvailable = [
			0 => __('Not Specified', 'onoffice'),
			1 => '1 km',
			5 => '5 km',
			10 => '10 km',
			20 => '20 km',
			50 => '50 km',
			100 => '100 km',
			200 => '200 km',
		];

		$pInputModelRadius->setValuesAvailable($valuesAvailable);
		$pInputModelRadius->setValue($this->_pGeoPositionFieldHandler->getRadiusValue());

		return $pInputModelRadius;
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
			InputModelDBFactoryConfigGeoFields::FIELDNAME_CITY_ACTIVE => __('City', 'onoffice'),
			InputModelDBFactoryConfigGeoFields::FIELDNAME_RADIUS_ACTIVE => __('Radius', 'onoffice'),
		];
	}
}
