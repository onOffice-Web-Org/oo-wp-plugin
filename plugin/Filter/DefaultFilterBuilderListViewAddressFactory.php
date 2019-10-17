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

declare(strict_types=1);

namespace onOffice\WPlugin\Filter;

use onOffice\WPlugin\DataView\DataListViewAddress;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\CompoundFieldsFilter;


/**
 *
 */

class DefaultFilterBuilderListViewAddressFactory
{
	/** @var CompoundFieldsFilter */
	private $_pCompoundFields = null;

	/** @var FieldsCollectionBuilderShort */
	private $_pBuilderShort = null;

	/** @var FilterBuilderInputVariablesFactory */
	private $_pFilterBuilderFactory = null;



	/**
	 *
	 * @param FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort
	 * @param CompoundFieldsFilter $pCompoundFields
	 * @param FilterBuilderInputVariablesFactory $pFilterBuilderFactory
	 *
	 */

	public function __construct(
		FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort,
		CompoundFieldsFilter $pCompoundFields,
		FilterBuilderInputVariablesFactory $pFilterBuilderFactory)
	{
		$this->_pBuilderShort = $pFieldsCollectionBuilderShort;
		$this->_pCompoundFields = $pCompoundFields;
		$this->_pFilterBuilderFactory = $pFilterBuilderFactory;
	}


	/**
	 *
	 * @param DataListViewAddress $pDataListView
	 * @return DefaultFilterBuilderListViewAddress
	 *
	 */

	public function create(DataListViewAddress $pDataListView): DefaultFilterBuilderListViewAddress
	{
		$pFilterBuilder = $this->_pFilterBuilderFactory->createForAddress();
		return new DefaultFilterBuilderListViewAddress($pDataListView,
			$this->_pBuilderShort, $this->_pCompoundFields, $pFilterBuilder);
	}
}
