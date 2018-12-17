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

namespace onOffice\WPlugin\Field;

use onOffice\WPlugin\Fieldnames;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Field\DistinctFieldsFilter;
use onOffice\WPlugin\Field\DistinctFieldsHandlerConfigurationDefault;


/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class DistinctFieldsHandler
{
	/** */
	const PARAMETER_FIELD = 'field';

	/** */
	const PARAMETER_INPUT_VALUES = 'inputValues';

	/** */
	const PARAMETER_DISTINCT_VALUES = 'distinctValues';

	/** */
	const PARAMETER_MODULE = 'module';


	/** @var string */
	private $_module = null;


	/** @var array */
	private $_inputValues = [];

	/** @var array */
	private $_values = [];

	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** var Fieldnames */
	private $_pFieldnames = null;

	/** @var array */
	private $_geoPositionFields = [];

	/** @var array */
	private $_distinctFields = [];


	/**
	 *
	 * @param DistinctFieldsHandlerConfigurationDefault $pDistinctFieldsHandlerConfiguration
	 *
	 */

	public function __construct(DistinctFieldsHandlerConfiguration $pDistinctFieldsHandlerConfiguration = null)
	{
		$pDistinctFieldsHandlerConfiguration == null &&
				$pDistinctFieldsHandlerConfiguration = new DistinctFieldsHandlerConfigurationDefault();

		$this->_pSDKWrapper = $pDistinctFieldsHandlerConfiguration->getSDKWrapper();
		$this->_pFieldnames = $pDistinctFieldsHandlerConfiguration->getFieldnames();
	}


	/**
	 *
	 * @param string $module
	 *
	 */

	public function setModule(string $module)
	{
		$this->_module = $module;
	}


	/** @return string */
	public function getModule(): string
		{ return $this->_module; }


	/**
	 *
	 * @param array $distinctFields
	 *
	 */

	public function setDistinctFields(array $distinctFields)
	{
		$this->_distinctFields = $distinctFields;
	}


	/** @return string */
	public function getDistinctFields(): array
		{ return $this->_distinctFields; }

	/**
	 *
	 * @param array $inputValues
	 *
	 */

	public function setInputValues(array $inputValues)
	{
		$this->_inputValues = $inputValues;
	}


	/** @return string */
	public function getInputValues(): array
		{ return $this->_inputValues; }


	/**
	 *
	 * @param array $geoPositionFields
	 *
	 */

	public function setGeoPositionFields(array $geoPositionFields)
	{
		$this->_geoPositionFields = $geoPositionFields;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getValues(): array
	{ return $this->_values; }


	/**
	 *
	 * @param string $field
	 *
	 */

	private function editMultiselectableField($field)
	{
		if ($this->_module == onOfficeSDK::MODULE_ESTATE &&
			in_array($this->_pFieldnames->getType($field, onOfficeSDK::MODULE_ESTATE),
			[FieldTypes::FIELD_TYPE_MULTISELECT, FieldTypes::FIELD_TYPE_SINGLESELECT]))
		{
			$field .= '[]';
		}

		return $field;
	}


	/**
	 *
	 */

	public function check()
	{
		$pSDKWrapper = $this->_pSDKWrapper;
		$pFilter = new DistinctFieldsFilter($this->_pFieldnames, $this->_module);

		foreach ($this->_distinctFields as $field)
		{
			$filter = $pFilter->filter($field, $this->_inputValues);

			$requestParams =
			[
				'language' => Language::getDefault(),
				'module' => $this->_module,
				'field' => $field,
				'filter' => $filter,
			];

			if (count($this->_geoPositionFields) > 0)
			{
				$requestParams['georangesearch'] = $this->_geoPositionFields;
			}

			$pApiClientAction = new APIClientActionGeneric($pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'distinctFields');
			$pApiClientAction->setParameters($requestParams);
			$pApiClientAction->addRequestToQueue();
			$pApiClientAction->sendRequests();
			if ($pApiClientAction->getResultStatus()) {

				$records = $pApiClientAction->getResultRecords();
				$field = $this->editMultiselectableField($field);
				$this->_values[$field] = $records[0]['elements'];
			}
		}
	}
}