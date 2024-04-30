<?php

/**
 *
 *    Copyright (C) 2020 onOffice GmbH
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

declare(strict_types=1);

namespace onOffice\WPlugin\Controller;

use DI\DependencyException;
use DI\NotFoundException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\SortList\SortListDataModel;
use onOffice\WPlugin\Controller\SortList\SortListTypes;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorGeoPositionFrontend;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\RequestVariablesSanitizer;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;

class SearchParametersModelBuilderEstate
{
	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort;

	/** @var RequestVariablesSanitizer */
	private $_pRequestVariablesSanitizer;

	/**
	 * @param FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort
	 * @param RequestVariablesSanitizer $pRequestVariablesSanitizer
	 */
	public function __construct(
		FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort,
		RequestVariablesSanitizer $pRequestVariablesSanitizer)
	{
		$this->_pFieldsCollectionBuilderShort = $pFieldsCollectionBuilderShort;
		$this->_pRequestVariablesSanitizer = $pRequestVariablesSanitizer;
	}

	/**
	 * @param DataListView $pDataView
	 * @param SortListDataModel $pSortListDataModel
	 * @return SearchParametersModel
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownFieldException
	 */
	public function buildSearchParametersModel(
		DataListView $pDataView,
		SortListDataModel $pSortListDataModel): SearchParametersModel
	{
		$pFieldsCollection = new FieldsCollection();
		$this->_pFieldsCollectionBuilderShort->addFieldsAddressEstate($pFieldsCollection);

		$pModel = new SearchParametersModel();
		$filterableFields = $this->setAllowedGetParametersEstateGeo($pDataView->getFilterableFields());
		$pFilterClosure = function ($field) use ($pFieldsCollection): bool {
			return $pFieldsCollection->containsFieldByModule(onOfficeSDK::MODULE_ESTATE, $field);
		};
		$filterableAvailableFields = array_filter($filterableFields, $pFilterClosure);

		foreach ($filterableAvailableFields as $filterableField) {
			$this->addAllowedParametersForFieldName($filterableField, $pFieldsCollection, $pModel);
		}

		if ($pSortListDataModel->isAdjustableSorting())	{
			$this->addForAdjustableSorting($pModel, $pDataView->getId());
		}
		return $pModel;
	}

	/**
	 * @param int $listViewId
	 * @param SearchParametersModel $pSearchParametersModel
	 */
	private function addForAdjustableSorting(SearchParametersModel $pSearchParametersModel, int $listViewId)
	{
		foreach (SortListTypes::getSortUrlPrameter($listViewId) as $urlParameter) {
			$pSearchParametersModel->addAllowedGetParameter($urlParameter);
			$pSearchParametersModel->setParameter($urlParameter,
				$this->_pRequestVariablesSanitizer->getFilteredGet($urlParameter));
		}
	}

	/**
	 * @param string $filterableField
	 * @param FieldsCollection $pFieldsCollection
	 * @param SearchParametersModel $pModel
	 * @throws UnknownFieldException
	 */
	private function addAllowedParametersForFieldName(
		string $filterableField,
		FieldsCollection $pFieldsCollection,
		SearchParametersModel $pModel)
	{
		$pField = $pFieldsCollection->getFieldByModuleAndName
			(onOfficeSDK::MODULE_ESTATE, $filterableField);

		if (FieldTypes::isMultipleSelectType($pField->getType())) {
			$pModel->setParameterArray
				($filterableField, $this->_pRequestVariablesSanitizer->getFilteredGet
					($filterableField, FILTER_DEFAULT, FILTER_FORCE_ARRAY));
		} else {
			$pModel->setParameter($filterableField,
				$this->_pRequestVariablesSanitizer->getFilteredGet($filterableField));
		}

		$pModel->addAllowedGetParameter($filterableField);

		if (FieldTypes::isNumericType($pField->getType()) ||
			FieldTypes::isDateOrDateTime($pField->getType())) {
			$pModel->addAllowedGetParameter($filterableField.'__von');
			$pModel->addAllowedGetParameter($filterableField.'__bis');
			$pModel->setParameter($filterableField.'__von',
				$this->_pRequestVariablesSanitizer->getFilteredGet($filterableField.'__von'));
			$pModel->setParameter($filterableField.'__bis',
				$this->_pRequestVariablesSanitizer->getFilteredGet($filterableField.'__bis'));
		}
	}


	/**
	 * @param array $filterableFields
	 * @return array
	 */
	private function setAllowedGetParametersEstateGeo(array $filterableFields): array
	{
		$positionGeoPos = array_search(GeoPosition::FIELD_GEO_POSITION,
			$filterableFields, true);

		if ($positionGeoPos !== false) {
			$pGeoPosition = new GeoPosition();
			$geoPositionFields = $pGeoPosition->getEstateSearchFields();
			// keep order safely
			foreach ($geoPositionFields as $geoPositionField) {
				$filterableFields []= $geoPositionField;
			}
			unset($filterableFields[$positionGeoPos]);
		}
		return $filterableFields;
	}
}