<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class DataDetailViewHandler
{
	/** */
	const DEFAULT_VIEW_OPTION_KEY = 'onoffice-default-view';

	/**
	 *
	 * @return DataDetailView
	 *
	 */

	static public function getDetailView()
	{
		$optionKey = self::DEFAULT_VIEW_OPTION_KEY;
		$pAlternate = new DataDetailView();
		$pResult = get_option($optionKey, $pAlternate);

		if ($pResult == null)
		{
			$pResult = $pAlternate;
		}

		return $pResult;
	}


	/**
	 *
	 * @param DataDetailView $pDataDetailView
	 * @return bool
	 *
	 */

	static public function saveDetailView(DataDetailView $pDataDetailView)
	{
		$result = null;

		if (get_option(self::DEFAULT_VIEW_OPTION_KEY) !== false) {
			update_option(self::DEFAULT_VIEW_OPTION_KEY, $pDataDetailView);
			$result = true;
		} else {
			$result = add_option(self::DEFAULT_VIEW_OPTION_KEY, $pDataDetailView);
		}

		return true;
	}


	/**
	 *
	 * @param array $row
	 * @return \onOffice\WPlugin\DataView\DataDetailView
	 *
	 */

	static public function createDetailViewByValues(array $row)
	{
		$pDataDetailView = new DataDetailView();
		$pDataDetailView->setTemplate(self::getValue($row, 'template'));
		$pDataDetailView->setFields(self::getValue($row, DataDetailView::FIELDS, array()));
		$pDataDetailView->setPictureTypes(self::getValue($row, DataDetailView::PICTURES, array()));
		$pDataDetailView->setExpose(self::getValue($row, 'expose'));
		return $pDataDetailView;
	}


	/**
	 *
	 * @param array $array
	 * @param mixed $key
	 * @param mixed $default
	 * @return mixed
	 *
	 */

	static private function getValue(array $array, $key, $default = null)
	{
		if (array_key_exists($key, $array)) {
			return $array[$key];
		}

		return $default;
	}
}
