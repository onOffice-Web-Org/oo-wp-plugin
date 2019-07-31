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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\DistinctFieldsFilter;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;


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

	/** @var array */
	private $_geoPositionFields = [];

	/** @var array */
	private $_distinctFields = [];

	/** @var FieldsCollection */
	private $_pFieldsCollection = null;

	/** @var SDKWrapper */
	private $_pSdkWrapper = null;

	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort = null;


	/**
	 *
	 * @param SDKWrapper $pSDKWrapper
	 * @param FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort
	 * @param DistinctFieldsHandlerEnvironment $pDistinctFieldsHandlerEnvironment
	 *
	 */

	public function __construct(SDKWrapper $pSDKWrapper,
		FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort,
		DistinctFieldsHandlerEnvironment $pDistinctFieldsHandlerEnvironment)
	{
		$this->_pSdkWrapper = $pSDKWrapper;
		$this->_pFieldsCollection = new FieldsCollection();

		$pFieldsCollectionBuilderShort->addFieldsAddressEstate($this->_pFieldsCollection);
		$pFieldsCollectionBuilderShort->addFieldsSearchCriteria($this->_pFieldsCollection);

		$this->_pFieldsCollectionBuilderShort = $pFieldsCollectionBuilderShort;
		$this->_module = $pDistinctFieldsHandlerEnvironment->getModule();
		$this->_distinctFields = $pDistinctFieldsHandlerEnvironment->getDistinctFields();
		$this->_inputValues = $pDistinctFieldsHandlerEnvironment->getInputValues();
		$this->_geoPositionFields  = $pDistinctFieldsHandlerEnvironment->getGeoPositionFields();
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
		$pField = $this->_pFieldsCollection->getFieldByModuleAndName(onOfficeSDK::MODULE_ESTATE, $field);
		$fieldType = $pField->getType();

		if ($this->_module == onOfficeSDK::MODULE_ESTATE &&
			FieldTypes::isMultipleSelectType($fieldType)) {
			$field .= '[]';
		}

		return $field;
	}


	/**
	 *
	 */

	public function check()
	{
		$apiClientActions = $this->retrieveValues();

		foreach ($this->_distinctFields as $field) {
			$pApiClientAction = $apiClientActions[$field];
			$records = $pApiClientAction->getResultRecords();
			$field = $this->editMultiselectableField($field);
			$this->_values[$field] = $records[0]['elements'];
		}
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function retrieveValues(): array
	{
		$pFilter = new DistinctFieldsFilter($this->_pFieldsCollectionBuilderShort, $this->_module);
		$apiClientActions = [];

		foreach ($this->_distinctFields as $field) {
			$requestParams = $this->buildParameters($pFilter, $field);
			$pApiClientAction = new APIClientActionGeneric
				($this->_pSdkWrapper, onOfficeSDK::ACTION_ID_GET, 'distinctValues');
			$pApiClientAction->setParameters($requestParams);
			$apiClientActions[$field] = $pApiClientAction;
			$pApiClientAction->addRequestToQueue();
		}

		$this->_pSdkWrapper->sendRequests();
		return $apiClientActions;
	}


	/**
	 *
	 * @param DistinctFieldsFilter $pFilter
	 * @param string $field
	 * @return array
	 *
	 */

	private function buildParameters(DistinctFieldsFilter $pFilter, string $field): array
	{
		$filter = $pFilter->filter($field, $this->_inputValues);
		$requestParams = [
			'language' => Language::getDefault(),
			'module' => $this->_module,
			'field' => $field,
			'filter' => $filter,
		];

		if ($this->_geoPositionFields !== []) {
			$requestParams['georangesearch'] = $this->_geoPositionFields;
		}

		return $requestParams;
	}
}