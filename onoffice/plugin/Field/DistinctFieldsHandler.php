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
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Utility\__String;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\API\APIClientActionGeneric;


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
	 */

	public function __construct()
	{
		$this->_pSDKWrapper = new SDKWrapper();

		$this->_pFieldnames = new Fieldnames(new FieldsCollection());
		$this->_pFieldnames->loadLanguage();
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


	/**
	 *
	 * @param array $distinctFields
	 *
	 */

	public function setDistinctFields(array $distinctFields)
	{
		$this->_distinctFields = $distinctFields;
	}


	/**
	 *
	 * @param array $inputValues
	 *
	 */

	public function setInputValues(array $inputValues)
	{
		$this->_inputValues = $inputValues;
	}


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
	 */

	public function check()
	{
		$pSDKWrapper = $this->_pSDKWrapper;

		foreach ($this->_distinctFields as $field)
		{
			$filter = $this->createFilterForField($field);

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

				if ($this->_module == onOfficeSDK::MODULE_ESTATE &&
					in_array($this->_pFieldnames->getType($field, onOfficeSDK::MODULE_ESTATE),
					[FieldTypes::FIELD_TYPE_MULTISELECT, FieldTypes::FIELD_TYPE_SINGLESELECT]))
				{
					$field .= '[]';
				}

				$this->_values[$field] = $records[0]['elements'];
			}
		}
	}


	/**
	 *
	 * @param string $distinctField
	 * @return array
	 *
	 */

	private function createFilterForField($distinctField)
	{
		$filter = [];

		foreach ($this->_inputValues as $key => $value)
		{
			if ($value == '' || $key == '' || $key == 's' || $key == 'oo_formid' || $key == 'oo_formno')
			{
				continue;
			}

			$pString = new __String($key);
			$operator = null;
			$field = null;
			$key = $pString->replace('[]', '');

			if ($pString->endsWith('__von'))
			{
				$operator = '>=';
				$field = $pString->replace('__von', '');
			}
			elseif ($pString->endsWith('__bis'))
			{
				$operator = '<=';
				$field = $pString->replace('__bis', '');
			}
			else
			{
				if (is_array($value))
				{
					$operator = 'in';
				}
				else
				{
					if ($this->_module == onOfficeSDK::MODULE_SEARCHCRITERIA &&
						in_array($this->_pFieldnames->getType($key, onOfficeSDK::MODULE_ESTATE),
							[FieldTypes::FIELD_TYPE_MULTISELECT, FieldTypes::FIELD_TYPE_SINGLESELECT]))
					{
						$operator = 'regexp';
					}
					else
					{
						$operator = '=';
					}
				}

				$field = $key;
			}

			if ($field == $distinctField)
			{
				continue;
			}
			else
			{
				if ($this->_module == onOfficeSDK::MODULE_SEARCHCRITERIA &&
						in_array($this->_pFieldnames->getType($field, onOfficeSDK::MODULE_ESTATE),
							[FieldTypes::FIELD_TYPE_FLOAT, FieldTypes::FIELD_TYPE_INTEGER]))
				{
					if (!array_key_exists($field.'__von', $filter))
					{
						$filter[$field.'__von'] = [array('op' => '<=', 'val' => $value)];
					}

					if (!array_key_exists($field.'__bis', $filter))
					{
						$filter[$field.'__bis'] = [array('op' => '>=', 'val' => $value)];
					}
				}
				else
				{
					$filter[$field] = [array('op' => $operator, 'val' => $value)];
				}
			}
		}
		return $filter;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getValues(): array
	{ return $this->_values; }
}