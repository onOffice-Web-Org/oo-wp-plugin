<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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

namespace onOffice\WPlugin\DataView;

use onOffice\WPlugin\WP\WPOptionWrapperBase;
use onOffice\WPlugin\WP\WPOptionWrapperDefault;
use onOffice\WPlugin\DataView\DataAddressDetailView;


/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2024, onOffice(R) GmbH
 *
 */

class DataAddressDetailViewHandler
{
	/** */
	const DEFAULT_ADDRESS_VIEW_OPTION_KEY = 'onoffice-default-address-view';

	/** @var WPOptionWrapperBase */
	private $_pWPOptionWrapper;


	/**
	 * @param WPOptionWrapperBase $pWPOptionWrapper
	 */
	public function __construct(WPOptionWrapperBase $pWPOptionWrapper = null)
	{
		$this->_pWPOptionWrapper = $pWPOptionWrapper ?? new WPOptionWrapperDefault();
	}


	/**
	 *
	 * @return DataAddressDetailView
	 *
	 */

	public function getAddressDetailView(): DataAddressDetailView
	{
		$optionKey = self::DEFAULT_ADDRESS_VIEW_OPTION_KEY;
		$pAlternate = new DataAddressDetailView();
		$pResult = $this->_pWPOptionWrapper->getOption($optionKey, $pAlternate);

		if ($pResult == null) {
			$pResult = $pAlternate;
		}

		return $pResult;
	}


	/**
	 *
	 * @param DataAddressDetailView $pDataAddressDetailView
	 *
	 */

	public function saveAddressDetailView(DataAddressDetailView $pDataAddressDetailView)
	{
		$pWpOptionsWrapper = $this->_pWPOptionWrapper;
		$viewOptionKey = self::DEFAULT_ADDRESS_VIEW_OPTION_KEY;

		if ($pWpOptionsWrapper->getOption($viewOptionKey) !== false) {
			$pWpOptionsWrapper->updateOption($viewOptionKey, $pDataAddressDetailView);
		} else {
			$pWpOptionsWrapper->addOption($viewOptionKey, $pDataAddressDetailView);
		}
	}


	/**
	 *
	 * @param array $row
	 * @return DataAddressDetailView
	 *
	 */

	public function createAddressDetailViewByValues(array $row): DataAddressDetailView
	{
		$pDataAddressDetailView = $this->getAddressDetailView();
		$pDataAddressDetailView->setTemplate($row[DataAddressDetailView::TEMPLATE] ?? '');
		$pDataAddressDetailView->setFields($row[DataAddressDetailView::FIELDS] ?? []);
		$pDataAddressDetailView->setPictureTypes($row[DataAddressDetailView::PICTURES] ?? []);
		$pDataAddressDetailView->setCustomLabels($row[DataAddressDetailView::FIELD_CUSTOM_LABEL] ?? $pDataAddressDetailView->getCustomLabels());
		$pDataAddressDetailView->setShortCodeForm($row['shortcodeform'] ?? '');
		$pDataAddressDetailView->setShortCodeActiveEstate($row['shortcodeActiveEstates'] ?? '');
		$pDataAddressDetailView->setShortCodeReferenceEstate($row['shortcodeReferenceEstate'] ?? '');

		return $pDataAddressDetailView;
	}
}
