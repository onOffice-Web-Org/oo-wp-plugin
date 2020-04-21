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

namespace onOffice\WPlugin\Filter\SearchParameters;

use DI\DependencyException;
use DI\NotFoundException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\CompoundFieldsFilter;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorGeoPositionFrontend;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\RequestVariablesSanitizer;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Utility\Logger;

/**
 *
 */
class SearchParametersModelBuilder
{
	/** @var CompoundFieldsFilter */
	private $_pCompoundFieldsFilter;

	/** @var RequestVariablesSanitizer */
	private $_pRequestVariablesSanitizer;

	/** @var Logger */
	private $_pLogger;

	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort;

	/**
	 * SearchParametersModelBuilder constructor.
	 *
	 * @param CompoundFieldsFilter $pCompoundFieldsFilter
	 * @param RequestVariablesSanitizer $pRequestVariablesSanitizer
	 * @param Logger $pLogger
	 * @param FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort
	 */
	public function __construct(
		CompoundFieldsFilter $pCompoundFieldsFilter,
		RequestVariablesSanitizer $pRequestVariablesSanitizer,
		Logger $pLogger,
		FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort)
	{
		$this->_pCompoundFieldsFilter = $pCompoundFieldsFilter;
		$this->_pRequestVariablesSanitizer = $pRequestVariablesSanitizer;
		$this->_pLogger = $pLogger;
		$this->_pFieldsCollectionBuilderShort = $pFieldsCollectionBuilderShort;
	}

	/**
	 * @param array $filterableFields
	 * @param string $module
	 * @return SearchParametersModel
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function build(array $filterableFields, string $module): SearchParametersModel
	{
		$pModel = new SearchParametersModel();
		$pFieldsCollection = new FieldsCollection();

		$this->_pFieldsCollectionBuilderShort->addFieldsAddressEstate($pFieldsCollection);
		$this->_pFieldsCollectionBuilderShort->addFieldsSearchCriteria($pFieldsCollection);

		$pGeoFieldsCollection = new FieldModuleCollectionDecoratorGeoPositionFrontend(new FieldsCollection);
		$pFieldsCollection->merge($pGeoFieldsCollection);
		$filterableFields = $this->_pCompoundFieldsFilter->mergeFields($pFieldsCollection, $filterableFields);

		foreach ($filterableFields as $field) {
			try {
				$pField = $pFieldsCollection->getFieldByModuleAndName($module, $field);
				$type = $pField->getType();
			} catch (UnknownFieldException $pException) {
				$this->_pLogger->logError($pException);
				continue;
			}

			if (FieldTypes::isMultipleSelectType($type)) {
				$pModel->setParameterArray($field, $this->_pRequestVariablesSanitizer->getFilteredGet($field,
					FILTER_DEFAULT, FILTER_FORCE_ARRAY));
			} else {
				$pModel->setParameter($field, $this->_pRequestVariablesSanitizer->getFilteredGet($field));
			}

			if ($module == onOfficeSDK::MODULE_ESTATE &&
					(FieldTypes::isNumericType($type) ||
					 FieldTypes::isDateOrDateTime($type))) {
				$pModel->addAllowedGetParameter($field.'__von');
				$pModel->addAllowedGetParameter($field.'__bis');
				$pModel->setParameter($field.'__bis', $this->_pRequestVariablesSanitizer->getFilteredGet($field.'__bis'));
				$pModel->setParameter($field.'__von', $this->_pRequestVariablesSanitizer->getFilteredGet($field.'__von'));
			}

			$pModel->addAllowedGetParameter($field);
		}

		return $pModel;
	}
}