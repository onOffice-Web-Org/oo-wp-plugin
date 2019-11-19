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

namespace onOffice\WPlugin\Renderer;

use onOffice\WPlugin\Controller\SortList\SortListTypes;
use onOffice\WPlugin\Controller\SortList\SortListDataModel;
use function esc_html;

class SortListRenderer
{

	/** */
	const DIRECTION_TYPE_HIGHEST_FIRST = 'highest first';
    /** */
	const DIRECTION_TYPE_LOWEST_FIRST = 'lowest first';
    /** */
	const DIRECTION_TYPE_ASCENDING = 'ascending';
    /** */
	const DIRECTION_TYPE_DESCENDING = 'descending';

	/** */
	const ELEMENT_ID = 'onofficeSortListSelector';


	/** @var array */
	const SORT_ORDER_MAPPING = [
		0 => [
			SortListTypes::SORTORDER_ASC => self::DIRECTION_TYPE_LOWEST_FIRST,
			SortListTypes::SORTORDER_DESC => self::DIRECTION_TYPE_HIGHEST_FIRST,
		],
		1 => [
			SortListTypes::SORTORDER_ASC => self::DIRECTION_TYPE_ASCENDING,
			SortListTypes::SORTORDER_DESC => self::DIRECTION_TYPE_DESCENDING,
		],
	];



	/**
	 * @param SortListDataModel $pSortListModel
	 * @return string
	 */
	public function createHtmlSelector(SortListDataModel $pSortListModel): string
	{
		$isAdjustable = $pSortListModel->isAdjustableSorting();
		$htmlSortListSelector = '';

		if ($isAdjustable) {
			$htmlSortListSelector = $this->createHtml($pSortListModel);
		}

		return $htmlSortListSelector;
	}

	/**
	 * @param SortListDataModel $pSortListModel
	 * @param string $sortorder
	 * @return string
	 */
	private function estimateDirectionLabelBySortorder(SortListDataModel $pSortListModel, string $sortorder): string
	{
		$sortorderDirectionLabel = self::SORT_ORDER_MAPPING[$pSortListModel->getSortbyUserDirection()][$sortorder];
		return esc_html($sortorderDirectionLabel, 'onoffice');
	}

	/**
	 * @param SortListDataModel $pSortListModel
	 * @param string $sortby
	 * @param string $sortorder
	 * @return string
	 */
	private function createOptionLabel(SortListDataModel $pSortListModel, string $sortby, string $sortorder): string
    {
    	$sortOrderDirectionLabel = $this->estimateDirectionLabelBySortorder($pSortListModel, $sortorder);
		return esc_html($sortby, 'onoffice').' '.$sortOrderDirectionLabel;
    }

	/**
	 * @param string $sortby
	 * @param string $sortorder
	 * @return string
	 */
    private function createOptionValue(string $sortby, string $sortorder): string
	{
		return $sortby.'#'.$sortorder;
	}

	/**
	 * @param SortListDataModel $pSortListModel
	 * @return string
	 */
	private function createSelectedValue(SortListDataModel $pSortListModel): string
	{
		$selectedValue = '';

		if ( $pSortListModel->getSelectedSortorder() != null &&
			$pSortListModel->getSelectedSortby() != null) {
				$selectedValue = $this->createOptionValue(
					$pSortListModel->getSelectedSortby(),  $pSortListModel->getSelectedSortorder());
		}

		return $selectedValue;
	}

	/**
	 * @param SortListDataModel $pSortListModel
	 * @return string
	 */
	private function createHtml(SortListDataModel $pSortListModel): string
	{
		$htmlString = '<select name="userDefinedSelection" id="'.self::ELEMENT_ID.'">';

		$selectedValue = $this->createSelectedValue($pSortListModel);
		$possibleSortorders = [SortListTypes::SORTORDER_ASC, SortListTypes::SORTORDER_DESC];

		foreach ($pSortListModel->getSortByUserValues() as $value => $label) {
			foreach ($possibleSortorders as $sortorder) {
				$optionValue = $this->createOptionValue($value, $sortorder);
				$selected = ($selectedValue == $optionValue) ? ' selected' : '';
				$htmlString .= '<option value="'.$optionValue.'" '.$selected.'>'.$this->createOptionLabel($pSortListModel, $label, $sortorder).'</option>';
			}
		}

		$htmlString .= '</select>';

		return $htmlString;
	}
}
