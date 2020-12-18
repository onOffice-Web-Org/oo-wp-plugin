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


namespace onOffice\WPlugin\DataView;

use onOffice\WPlugin\WP\WPOptionWrapperBase;


/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2020, onOffice(R) GmbH
 *
 */

class DataSimilarEstatesSettingsHandler
{
	/** */
	const DEFAULT_VIEW_OPTION_KEY = 'onoffice-similar-estates-settings-view';

	/** @var WPOptionWrapperBase */
	private $_pWPOptionWrapper;


	/**
	 * @param WPOptionWrapperBase $pWPOptionWrapper
	 */
	public function __construct(WPOptionWrapperBase $pWPOptionWrapper)
	{
		$this->_pWPOptionWrapper = $pWPOptionWrapper;
	}


	/**
	 * @return DataSimilarView
	 */

	public function getDataSimilarEstatesSettings(): DataSimilarView
	{
		$optionKey = self::DEFAULT_VIEW_OPTION_KEY;
		$pAlternate = new DataSimilarView();
		$pResult = $this->_pWPOptionWrapper->getOption($optionKey, $pAlternate);

		if ($pResult == null)
		{
			$pResult = $pAlternate;
		}

		return $pResult;
	}


	/**
	 * @param DataSimilarView $pDataSimilarView
	 */

	public function saveDataSimilarEstatesSettings(DataSimilarView $pDataSimilarView)
	{
		$pWpOptionsWrapper = $this->_pWPOptionWrapper;
		$viewOptionKey = self::DEFAULT_VIEW_OPTION_KEY;

		if ($pWpOptionsWrapper->getOption($viewOptionKey) !== false) {
			$pWpOptionsWrapper->updateOption($viewOptionKey, $pDataSimilarView);
		} else {
			$pWpOptionsWrapper->addOption($viewOptionKey, $pDataSimilarView);
		}
	}


	/**
	 * @param array $row
	 * @return DataSimilarView
	 */

	public function createDataSimilarEstatesSettingsByValues(array $row): DataSimilarView
	{
		$pDataSimilarView = $this->getDataSimilarEstatesSettings();
		$pDataSimilarView->setFields($row[DataDetailView::FIELDS] ?? []);
		$pDataSimilarView->setDataSimilarViewActive
			($row[DataSimilarView::ENABLE_SIMILAR_ESTATES] ?? false);

		$pDataViewSimilar = $pDataSimilarView->getDataViewSimilarEstates();
		$this->configureDataSimilarEstatesSettings($pDataViewSimilar, $row);

		return $pDataSimilarView;
	}


	/**
	 * @param DataViewSimilarEstates $pDataViewSimilar
	 * @param array $row
	 */

	private function configureDataSimilarEstatesSettings(DataViewSimilarEstates $pDataViewSimilar,
		array $row)
	{
		$pDataViewSimilar->setFields
		($row[DataViewSimilarEstates::FIELDS] ?? false);
		$pDataViewSimilar->setSameEstateKind
			($row[DataViewSimilarEstates::FIELD_SAME_KIND] ?? false);
		$pDataViewSimilar->setSameMarketingMethod
			($row[DataViewSimilarEstates::FIELD_SAME_MARKETING_METHOD] ?? false);
		$pDataViewSimilar->setSamePostalCode
			($row[DataViewSimilarEstates::FIELD_SAME_POSTAL_CODE] ?? false);
		$pDataViewSimilar->setShowArchived
			($row[DataViewSimilarEstates::FIELD_SHOW_ARCHIVED] ?? false);
		$pDataViewSimilar->setShowReference
			($row[DataViewSimilarEstates::FIELD_SHOW_REFERENCE] ?? false);
		$pDataViewSimilar->setRadius
			($row[DataViewSimilarEstates::FIELD_RADIUS] ?? $pDataViewSimilar->getRadius());
		$pDataViewSimilar->setRecordsPerPage
			($row[DataViewSimilarEstates::FIELD_AMOUNT] ?? $pDataViewSimilar->getRecordsPerPage());
		$pDataViewSimilar->setTemplate
			($row[DataViewSimilarEstates::FIELD_SIMILAR_ESTATES_TEMPLATE] ??
				$pDataViewSimilar->getTemplate());
	}
}
