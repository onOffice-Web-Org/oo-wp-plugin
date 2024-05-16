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

class SortListTypes
{
	/** */
	const SORTORDER_ASC = 'ASC';
	/** */
	const SORTORDER_DESC = 'DESC';

	/** */
	const SORT_BY = 'sortby';
	/** */
	const SORT_ORDER = 'sortorder';

	/** */
	const SORT_BY_USER_DEFINED_DEFAULT_DELIMITER = '#';

	/**
	 * @param int $listViewId
	 * @return array
	 */
	static public function getSortUrlPrameter(int $listViewId): array
	{
		return [self::SORT_BY . '_id_' . $listViewId, self::SORT_ORDER . '_id_' . $listViewId];
	}


	/**
	 * @return array
	 */
	static public function getSortOrder(): array
	{
		return [
			0 => [
				SortListTypes::SORTORDER_ASC => __('lowest first', 'onoffice-for-wp-websites'),
				SortListTypes::SORTORDER_DESC => __('highest first', 'onoffice-for-wp-websites'),
			],
			1 => [
				SortListTypes::SORTORDER_ASC => __('ascending', 'onoffice-for-wp-websites'),
				SortListTypes::SORTORDER_DESC => __('descending', 'onoffice-for-wp-websites'),
			],
		];
	}

	/**
	 * @param int $sortByUserDirection
	 * @param string $sortorder
	 * @return string
	 */
	static public function getSortOrderMapping(int $sortByUserDirection, string $sortorder): string
	{
		$mapping = self::getSortOrder();

		return $mapping[$sortByUserDirection][$sortorder];
	}
}
