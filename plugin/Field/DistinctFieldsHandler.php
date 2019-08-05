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

namespace onOffice\WPlugin\Field;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\DistinctFieldsFilter;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;


/**
 *
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


	/** @var SDKWrapper */
	private $_pSdkWrapper = null;

	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort = null;

	/** @var DistinctFieldsHandlerModelBuilder */
	private $_pDistinctFieldsHandlerModelBuilder = null;

	/** @var DistinctFieldsFilter */
	private $_pDistinctFieldsFilter = null;


	/**
	 *
	 * @param DistinctFieldsHandlerModelBuilder $pDistinctFieldsHandlerModelBuilder
	 * @param SDKWrapper $pSDKWrapper
	 * @param FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort
	 * @param DistinctFieldsFilter $pDistinctFieldsFilter
	 *
	 */

	public function __construct(
		DistinctFieldsHandlerModelBuilder $pDistinctFieldsHandlerModelBuilder,
		SDKWrapper $pSDKWrapper,
		FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort,
		DistinctFieldsFilter $pDistinctFieldsFilter)
	{
		$this->_pSdkWrapper = $pSDKWrapper;

		$this->_pFieldsCollectionBuilderShort = $pFieldsCollectionBuilderShort;
		$this->_pDistinctFieldsHandlerModelBuilder = $pDistinctFieldsHandlerModelBuilder;
		$this->_pDistinctFieldsFilter = $pDistinctFieldsFilter;
	}


	/**
	 *
	 * @param string $field
	 * @param Field $pField
	 * @return string
	 *
	 */

	private function editMultiselectableField(Field $pField)
	{
		$fieldType = $pField->getType();
		$field = $pField->getName();

		if ($pField->getModule() == onOfficeSDK::MODULE_ESTATE &&
			FieldTypes::isMultipleSelectType($fieldType)) {
			$field .= '[]';
		}

		return $field;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function check(): array
	{
		$pFieldsCollection = new FieldsCollection();
		$this->_pFieldsCollectionBuilderShort->addFieldsAddressEstate($pFieldsCollection);
		$this->_pFieldsCollectionBuilderShort->addFieldsSearchCriteria($pFieldsCollection);

		$pModel = $this->_pDistinctFieldsHandlerModelBuilder->buildDataModel();
		$apiClientActions = $this->retrieveValues($pModel);
		$values = [];

		foreach ($pModel->getDistinctFields() as $field) {
			$pField = $pFieldsCollection->getFieldByModuleAndName($pModel->getModule(), $field);
			$pApiClientAction = $apiClientActions[$field];
			$records = $pApiClientAction->getResultRecords();
			$field = $this->editMultiselectableField($pField);
			$values[$field] = $records[0]['elements'];
		}

		return $values;
	}


	/**
	 *
	 * @param DistinctFieldsHandlerModel $pModel
	 * @return array
	 *
	 */

	private function retrieveValues(DistinctFieldsHandlerModel $pModel): array
	{
		$apiClientActions = [];

		foreach ($pModel->getDistinctFields() as $field) {
			$requestParams = $this->buildParameters($field, $pModel);
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
	 * @param string $field
	 * @param DistinctFieldsHandlerModel $pModel
	 * @return array
	 *
	 */

	private function buildParameters(string $field, DistinctFieldsHandlerModel $pModel): array
	{
		$filter = $this->_pDistinctFieldsFilter->filter($field, $pModel->getInputValues(), $pModel->getModule());
		$requestParams = [
			'language' => Language::getDefault(),
			'module' => $pModel->getModule(),
			'field' => $field,
			'filter' => $filter,
		];

		if ($pModel->getGeoPositionFields() !== []) {
			$requestParams['georangesearch'] = $pModel->getGeoPositionFields();
		}

		return $requestParams;
	}
}