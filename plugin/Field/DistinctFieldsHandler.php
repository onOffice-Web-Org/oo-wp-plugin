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

use DI\DependencyException;
use DI\NotFoundException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\Types\FieldsCollection;

class DistinctFieldsHandler
{
	/** @var APIClientActionGeneric */
	private $_pApiClientAction;

	/** @var DistinctFieldsHandlerModelBuilder */
	private $_pDistinctFieldsHandlerModelBuilder;

	/**
	 * @param APIClientActionGeneric $pApiClientAction
	 * @param DistinctFieldsHandlerModelBuilder $pDistinctFieldsHandlerModelBuilder
	 */
	public function __construct(
		APIClientActionGeneric $pApiClientAction,
		DistinctFieldsHandlerModelBuilder $pDistinctFieldsHandlerModelBuilder)
	{
		$this->_pApiClientAction = $pApiClientAction;
		$this->_pDistinctFieldsHandlerModelBuilder = $pDistinctFieldsHandlerModelBuilder;
	}

	/**
	 * @param DistinctFieldsHandlerModel $pModel
	 * @param FieldsCollection $pFieldsCollection
	 * @return FieldsCollection
	 * @throws UnknownFieldException
	 */
	private function fetchValuesAndModifyFieldsCollection(DistinctFieldsHandlerModel $pModel, FieldsCollection $pFieldsCollection): FieldsCollection
	{
		$apiClientActions = $this->retrieveValues($pModel);
		$pFieldsCollectionCloned = clone $pFieldsCollection;
		foreach ($pModel->getDistinctFields() as $field) {
			$pField = $pFieldsCollectionCloned->getFieldByModuleAndName($pModel->getModule(), $field);
			$pApiClientAction = $apiClientActions[$field];
			$records = $pApiClientAction->getResultRecords();
			$pField->setPermittedvalues($records[0]['elements']);
		}
		return $pFieldsCollectionCloned;
	}

	/**
	 * @param DataListView $pListView
	 * @param FieldsCollection $pFieldsCollection
	 * @return FieldsCollection
	 * @throws NotFoundException
	 * @throws UnknownFieldException
	 * @throws DependencyException
	 */
	public function modifyFieldsCollectionForEstate(
		DataListView $pListView, FieldsCollection $pFieldsCollection): FieldsCollection
	{
		$pModel = $this->_pDistinctFieldsHandlerModelBuilder->buildDataModelForEstate($pListView);
		return $this->fetchValuesAndModifyFieldsCollection($pModel, $pFieldsCollection);
	}

	/**
	 * @param DistinctFieldsHandlerModel $pModel
	 * @return array
	 */
	private function retrieveValues(DistinctFieldsHandlerModel $pModel): array
	{
		$apiClientActions = [];
		foreach ($pModel->getDistinctFields() as $field) {
			$requestParams = $this->buildParameters($field, $pModel);
			$pApiClientAction = $this->_pApiClientAction->withActionIdAndResourceType
				(onOfficeSDK::ACTION_ID_GET, 'distinctValues');
			$pApiClientAction->setParameters($requestParams);
			$apiClientActions[$field] = $pApiClientAction;
			$pApiClientAction->addRequestToQueue();
		}
		$this->_pApiClientAction->sendRequests();
		return $apiClientActions;
	}

	/**
	 * @param string $field
	 * @param DistinctFieldsHandlerModel $pModel
	 * @return array
	 */
	private function buildParameters(string $field, DistinctFieldsHandlerModel $pModel): array
	{
		return [
			'language' => Language::getDefault(),
			'module' => $pModel->getModule(),
			'field' => $field,
			'filter' => $pModel->getFilterExpression(),
			'filterid' => $pModel->getFilterId(),
		];
	}
}