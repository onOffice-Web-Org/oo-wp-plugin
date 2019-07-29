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

declare (strict_types=1);

namespace onOffice\WPlugin\Model\InputModelBuilder;

use Generator;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\GeoPositionFieldHandler;
use onOffice\WPlugin\Controller\ViewProperty;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorGeoPositionFrontend;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigGeoFields;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Types\FieldsCollection;
use function __;

/**
 *
 * Builds InputModel instances for the back-end box of Geo Fields.
 *
 * Note that Applicant Forms don't have a radius.
 *
 */

class InputModelBuilderGeoRange
{
	/** @var array */
	const GEO_ORDER_OOPTIONS_TEMPLATE = [
		[
			GeoPosition::ESTATE_LIST_SEARCH_STREET,
			GeoPosition::ESTATE_LIST_SEARCH_ZIP,
			GeoPosition::ESTATE_LIST_SEARCH_CITY,
			GeoPosition::ESTATE_LIST_SEARCH_COUNTRY,
			GeoPosition::ESTATE_LIST_SEARCH_RADIUS,
		],
		[
			GeoPosition::ESTATE_LIST_SEARCH_RADIUS,
			GeoPosition::ESTATE_LIST_SEARCH_STREET,
			GeoPosition::ESTATE_LIST_SEARCH_ZIP,
			GeoPosition::ESTATE_LIST_SEARCH_CITY,
			GeoPosition::ESTATE_LIST_SEARCH_COUNTRY,
		],
	];


	/** @var InputModelDBFactory */
	private $_pInputModelFactory = null;

	/** @var GeoPositionFieldHandler */
	private $_pGeoPositionFieldHandler = null;

	/** @var FieldModuleCollectionDecoratorGeoPositionFrontend */
	private $_pFieldsCollection = null;


	/**
	 *
	 * @param string $module
	 * @param GeoPositionFieldHandler $pGeoPositionFieldHandler
	 * @param FieldModuleCollectionDecoratorGeoPositionFrontend $pFieldsCollection
	 *
	 */

	public function __construct(string $module,
		GeoPositionFieldHandler $pGeoPositionFieldHandler = null,
		FieldModuleCollectionDecoratorGeoPositionFrontend $pFieldsCollection = null)
	{
		$pFactoryConfig = new InputModelDBFactoryConfigGeoFields($module);
		$this->_pInputModelFactory = new InputModelDBFactory($pFactoryConfig);
		$this->_pGeoPositionFieldHandler = $pGeoPositionFieldHandler ?? new GeoPositionFieldHandler();
		$this->_pFieldsCollection = $pFieldsCollection ??
			new FieldModuleCollectionDecoratorGeoPositionFrontend(new FieldsCollection);
	}


	/**
	 *
	 * @return Generator
	 *
	 */

	public function build(ViewProperty $pView): Generator
	{
		$this->_pGeoPositionFieldHandler->readValues($pView);
		$activeFields = $this->getFieldnamesActiveCheckbox($pView);
		$activeGeoFields = $this->_pGeoPositionFieldHandler->getActiveFields();

		foreach ($activeFields as $field => $label) {
			$pInputModelGeoCountry = $this->_pInputModelFactory->create($field, $label);
			$pInputModelGeoCountry->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
			$pInputModelGeoCountry->setValuesAvailable(1);
			$isEnabled = isset($activeGeoFields[$field]);
			$pInputModelGeoCountry->setValue((int)$isEnabled);
			yield $pInputModelGeoCountry;
		}

		if ($pView->getViewType() !== Form::TYPE_APPLICANT_SEARCH) {
			yield $this->generateInputRadius();
		}
		yield from $this->generateInputOrder($pView);
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
	 * @param ViewProperty $pView
	 * @return array<InputModelDB> Array containing an InputModelDB if > 1 settings for the sorting are available
	 *
	 */

	private function generateInputOrder(ViewProperty $pView): array
	{
		$options = [];

		foreach ($this->filterGeoOrderOptionsTemplateByView($pView) as $keysOrdered) {
			$optionLabelParts = [];
			foreach ($keysOrdered as $key) {
				$label = $this->getGeoFieldLabel($key);
				$optionLabelParts []= $label;
			}
			$option = implode(',', $keysOrdered);
			$options[$option] = implode(', ', $optionLabelParts);
		}

		if (count($options) === 1) {
			return [];
		}

		$pInputModelRadius = $this->_pInputModelFactory->create
			(InputModelDBFactoryConfigGeoFields::FIELDNAME_GEO_ORDER, __('Order', 'onoffice'));
		$pInputModelRadius->setHtmlType(InputModelOption::HTML_TYPE_SELECT);
		$pInputModelRadius->setValuesAvailable($options);
		$geoFieldOrderValue = implode(',', $this->_pGeoPositionFieldHandler->getGeoFieldsOrdered());
		$pInputModelRadius->setValue($geoFieldOrderValue);
		return [$pInputModelRadius];
	}


	/**
	 *
	 * @param string $key
	 * @return string
	 *
	 */

	private function getGeoFieldLabel(string $key): string
	{
		// labels are equal for estate and search criteria
		return $this->_pFieldsCollection
			->getFieldByModuleAndName(onOfficeSDK::MODULE_ESTATE, $key)
			->getLabel();
	}


	/**
	 *
	 * @param ViewProperty $pView
	 * @return array
	 *
	 */

	private function filterGeoOrderOptionsTemplateByView(ViewProperty $pView): array
	{
		$geoOrderOptions = self::GEO_ORDER_OOPTIONS_TEMPLATE;

		if ($pView->getViewType() === Form::TYPE_APPLICANT_SEARCH) {
			foreach ($geoOrderOptions as &$values) {
				$result = array_search(GeoPosition::ESTATE_LIST_SEARCH_RADIUS, $values);
				if ($result !== false) {
					unset($values[$result]);
				}
			}
		}

		return $geoOrderOptions;
	}


	/**
	 *
	 * @param ViewProperty $pView
	 * @return array
	 *
	 */

	private function getFieldnamesActiveCheckbox(ViewProperty $pView): array
	{
		$fieldnames = [
			InputModelDBFactoryConfigGeoFields::FIELDNAME_COUNTRY_ACTIVE => __('Country', 'onoffice'),
			InputModelDBFactoryConfigGeoFields::FIELDNAME_ZIP_ACTIVE => __('Postal Code', 'onoffice'),
			InputModelDBFactoryConfigGeoFields::FIELDNAME_CITY_ACTIVE => __('City', 'onoffice'),
			InputModelDBFactoryConfigGeoFields::FIELDNAME_STREET_ACTIVE => __('Street', 'onoffice'),
		];

		if ($pView->getViewType() !== Form::TYPE_APPLICANT_SEARCH) {
			$fieldnames[InputModelDBFactoryConfigGeoFields::FIELDNAME_RADIUS_ACTIVE] = __('Radius', 'onoffice');
		}

		return $fieldnames;
	}


	/**
	 *
	 * @return InputModelDBFactory
	 *
	 */

	public function getInputModelFactory(): InputModelDBFactory
	{
		return $this->_pInputModelFactory;
	}


	/**
	 *
	 * @return GeoPositionFieldHandler
	 *
	 */

	public function getGeoPositionFieldHandler(): GeoPositionFieldHandler
	{
		return $this->_pGeoPositionFieldHandler;
	}


	/**
	 *
	 * @return FieldModuleCollectionDecoratorGeoPositionFrontend
	 *
	 */

	public function getFieldsCollection(): FieldModuleCollectionDecoratorGeoPositionFrontend
	{
		return $this->_pFieldsCollection;
	}
}
