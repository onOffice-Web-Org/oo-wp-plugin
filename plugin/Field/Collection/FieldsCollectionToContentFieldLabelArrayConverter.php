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

namespace onOffice\WPlugin\Field\Collection;

use onOffice\WPlugin\Types\FieldsCollection;

/**
 *
 */

class FieldsCollectionToContentFieldLabelArrayConverter
{
	/**
	 *
	 * @param FieldsCollection $pFieldsCollection
	 * @param string $module
	 * @return array
	 *
	 */

	public function convert(FieldsCollection $pFieldsCollection, string $module): array
	{
		$result = [];
		$categories = [];

		foreach ($pFieldsCollection->getFieldsByModule($module) as $key => $pField) {
			$content = $pField->getCategory() ?: __('Specific Fields', 'onoffice-for-wp-websites');

			$categories []= $content;
			$result[$content][$key] = $pField->getLabel();
		}

		foreach ($categories as $category) {
			natcasesort($result[$category]);
		}

		return $result;
	}
}
