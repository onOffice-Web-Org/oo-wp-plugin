<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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

use Exception;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataView\DataListViewAddress;
use onOffice\WPlugin\Field\CompoundFieldsFilter;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Types\FieldsCollection;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class DefaultFilterBuilderListViewAddress
	implements DefaultFilterBuilder
{
	/** @var DataListViewAddress */
	private $_pDataListView = null;

	/** @var FilterBuilderInputVariables */
	private $_pFilterBuilderInputVars = null;

	/** @var CompoundFieldsFilter */
	private $_pCompoundFieldsFilter = null;

	/** @var FieldsCollectionBuilderShort */
	private $_pBuilderShort = null;


	/**
	 *
	 * @param DataListViewAddress $pDataListView
	 * @param FilterBuilderInputVariables $pFilterBuilder
	 *
	 */

	public function __construct(
		DataListViewAddress $pDataListView,
		FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort,
		CompoundFieldsFilter $pCompoundFields,
		FilterBuilderInputVariables $pFilterBuilder = null)
	{
		$this->_pDataListView = $pDataListView;
		$this->_pFilterBuilderInputVars = $pFilterBuilder ?? new FilterBuilderInputVariables
			(onOfficeSDK::MODULE_ADDRESS, true);

		if ($this->_pFilterBuilderInputVars->getModule() !== onOfficeSDK::MODULE_ADDRESS) {
			throw new Exception('Module must be address.');
		}

		$this->_pBuilderShort = $pFieldsCollectionBuilderShort;
		$this->_pCompoundFieldsFilter = $pCompoundFields;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function buildFilter(): array
	{
		$filterableFields = $this->_pDataListView->getFilterableFields();
		$pFieldsCollection = new FieldsCollection();
		$this->_pBuilderShort->addFieldsAddressEstate($pFieldsCollection);
		$filterableInputs = $this->_pCompoundFieldsFilter->mergeFields($pFieldsCollection, $filterableFields);
		$fieldFilter = $this->_pFilterBuilderInputVars->getPostFieldsFilter($filterableInputs);

		$defaultFilter = [
			'homepage_veroeffentlichen' => [
				['op' => '=', 'val' => 1],
			],
		];

		return array_merge($defaultFilter, $fieldFilter);
	}
}
