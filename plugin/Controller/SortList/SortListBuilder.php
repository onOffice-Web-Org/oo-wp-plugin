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

namespace onOffice\WPlugin\Controller\SortList;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\RequestVariablesSanitizer;
use onOffice\WPlugin\Types\FieldsCollection;

class SortListBuilder
{
	/** @var FieldsCollectionBuilderShort */
	private $_pBuilderShort;

	/**
	 * @param FieldsCollectionBuilderShort $pBuilderShort
	 */
	public function __construct(FieldsCollectionBuilderShort $pBuilderShort)
	{
		$this->_pBuilderShort = $pBuilderShort;
	}

	/**
	 * @param DataListView $pListView
	 * @return SortListDataModel
	 * @throws UnknownFieldException
	 */
	public function build(DataListView $pListView): SortListDataModel
	{
		$pSortListDataModel = new SortListDataModel();

		$pSortListDataModel->setAdjustableSorting($this->estimateAdjustable(
				$pListView->getSortBySetting(),  $pListView->getSortByUserValues()));

		$sortbyDefault = $this->estimateSortbyDefault($pListView->getSortByUserDefinedDefault(),
			$pListView->getSortByUserValues());
		$pSortListDataModel->setSortbyDefaultValue($sortbyDefault);

		$pSortListDataModel->setSortbyUserDirection($pListView->getSortByUserDefinedDirection());

		$sortbyValues = $this->estimateSortByValues($pListView->getSortByUserValues());
		$pSortListDataModel->setSortByUserValues($sortbyValues);

		if ($pSortListDataModel->isAdjustableSorting())	{
			$pSortListDataModel->setSelectedSortby($this->estimateAdjustableSelectedSortby($sortbyDefault,
				$pSortListDataModel->getSortByUserValues()));
			$pSortListDataModel->setSelectedSortorder($this->estimateAdjustableSelectedSortorder());
		} else {
			$pSortListDataModel->setSelectedSortby($pListView->getSortby());
			$pSortListDataModel->setSelectedSortorder($pListView->getSortorder());
		}

		return $pSortListDataModel;
	}

	/**
	 * @param int $sortBySetting
	 * @param array $sortByUserValues
	 * @return bool
	 */
	private function estimateAdjustable(int $sortBySetting, array $sortByUserValues): bool
	{
		$isUserSetting = (bool) $sortBySetting;
		$isAdjustable = false;

		if ($isUserSetting && count($sortByUserValues) > 0)	{
			$isAdjustable = true;
		}

		return $isAdjustable;
	}

	/**
	 * @param string $sortbyDefault
	 * @param array $sortByUserValues
	 * @return string
	 */
	private function estimateSortbyDefault(string $sortbyDefault, array $sortByUserValues): string
	{
		if ($sortbyDefault != '') {
			$default = $sortbyDefault;
		} else {
			$default = array_shift($sortByUserValues);
		}

		$default = strval($default);
		return $default;
	}

	/**
	 * @param array $sortByUserValues
	 * @return array
	 * @throws UnknownFieldException
	 */
	private function estimateSortByValues(array $sortByUserValues): array
	{
		$pFieldsCollection = new FieldsCollection();
		$this->_pBuilderShort->addFieldsAddressEstate($pFieldsCollection);
		$sortbyValues = [];

		foreach ($sortByUserValues as $sortByField) {
			if ($pFieldsCollection->containsFieldByModule(onOfficeSDK::MODULE_ESTATE, $sortByField)) {
				$sortbyValues[$sortByField] = $pFieldsCollection->getFieldByModuleAndName(
					onOfficeSDK::MODULE_ESTATE, $sortByField)->getLabel();
			}
		}
		return $sortbyValues;
	}

	/**
	 * @param string $default
	 * @param array $sortbyUserValues
	 * @return string
	 */
	private function estimateAdjustableSelectedSortby(string $default, array $sortbyUserValues): string
	{
		$pRequestVariables = new RequestVariablesSanitizer();
		$sortby = $pRequestVariables->getFilteredGet(SortListTypes::SORT_BY) ?? '';
		$pValidator = new SortListValidator;
		if ($sortby != '' && $pValidator->isSortbyValide($sortby, $sortbyUserValues)) {
			$selectedSortBy = $sortby;
		} else {
			$selectedSortBy = $default;
		}
		return $selectedSortBy;
	}

	/**
	 * @return string
	 */
	private function estimateAdjustableSelectedSortorder(): string
	{
		$pRequestVariables = new RequestVariablesSanitizer();
		$sortorder = $pRequestVariables->getFilteredGet(SortListTypes::SORT_ORDER) ?? '';
		$pValidator = new SortListValidator;

		if ($sortorder != null && $pValidator->isSortorderValide($sortorder)) {
			$selectedSortOrder = $sortorder;
		} else {
			$selectedSortOrder = SortListTypes::SORTORDER_ASC;
		}
		return $selectedSortOrder;
	}
}