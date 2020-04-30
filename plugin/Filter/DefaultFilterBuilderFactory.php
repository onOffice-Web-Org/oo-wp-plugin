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

namespace onOffice\WPlugin\Filter;

use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;

class DefaultFilterBuilderFactory
{
	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort;


	/**
	 * @param FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort
	 */

	public function __construct(FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort)
	{
		$this->_pFieldsCollectionBuilderShort = $pFieldsCollectionBuilderShort;
	}


	/**
	 * @param DataListView $pDataListView
	 * @return DefaultFilterBuilderListView
	 */
	public function buildDefaultListViewFilter(DataListView $pDataListView): DefaultFilterBuilderListView
	{
		$pEnvironment = new DefaultFilterBuilderListViewEnvironmentDefault();
		return new DefaultFilterBuilderListView($pDataListView, $this->_pFieldsCollectionBuilderShort, $pEnvironment);
	}
}