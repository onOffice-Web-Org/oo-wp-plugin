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

namespace onOffice\WPlugin\DataView;

use onOffice\WPlugin\WP\WPOptionWrapperBase;
use onOffice\WPlugin\WP\WPOptionWrapperDefault;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2024, onOffice(R) GmbH
 *
 */

class DataAddressDetailViewHandler
{
	/** */
	const ADDRESS_DEFAULT_VIEW_OPTION_KEY = 'onoffice-address-default-view';

	/** @var WPOptionWrapperBase */
	private $_pWPOptionWrapper;


	/**
	 * @param  WPOptionWrapperBase|null  $pWPOptionWrapper
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
		$optionKey = self::ADDRESS_DEFAULT_VIEW_OPTION_KEY;
		$pAlternate = new DataAddressDetailView();
		$pResult = $this->_pWPOptionWrapper->getOption($optionKey, $pAlternate);

		if ($pResult == null)
		{
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
		$viewOptionKey = self::ADDRESS_DEFAULT_VIEW_OPTION_KEY;
		$pDataAddressDetailView->setTemplate('oo-wp-plugin/templates.dist/address/default_detail.php');
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
		$pDataAddressDetailView->setTemplate( 'oo-wp-plugin/templates.dist/address/default_detail.php');
		$pDataAddressDetailView->setPictureTypes($row[DataAddressDetailView::PICTURES] ?? []);

		return $pDataAddressDetailView;
	}
}