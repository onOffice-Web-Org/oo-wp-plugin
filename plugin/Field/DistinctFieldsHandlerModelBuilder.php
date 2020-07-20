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

namespace onOffice\WPlugin\Field;

use DI\DependencyException;
use DI\NotFoundException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationApplicantSearch;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Filter\DefaultFilterBuilderFactory;

class DistinctFieldsHandlerModelBuilder
{
	/** @var DefaultFilterBuilderFactory */
	private $_pDefaultFilterBuilderFactory;

	/**
	 * @param DefaultFilterBuilderFactory $pDefaultFilterBuilderFactory
	 */
	public function __construct(
		DefaultFilterBuilderFactory $pDefaultFilterBuilderFactory)
	{
		$this->_pDefaultFilterBuilderFactory = $pDefaultFilterBuilderFactory;
	}

	/**
	 * @param DataListView $pListView
	 * @return DistinctFieldsHandlerModel
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function buildDataModelForEstate(DataListView $pListView): DistinctFieldsHandlerModel
	{
		$pListViewNoFilterableFields = clone $pListView;
		$pListViewNoFilterableFields->setFilterableFields([]);
		$pDefaultFilterBuilder = $this->_pDefaultFilterBuilderFactory->buildDefaultListViewFilter($pListViewNoFilterableFields);
		$pDataModel = new DistinctFieldsHandlerModel();
		$pDataModel->setModule(onOfficeSDK::MODULE_ESTATE);
		$pDataModel->setDistinctFields($pListView->getAvailableOptions());
		$pDataModel->setFilterExpression($pDefaultFilterBuilder->buildFilter());
		$pDataModel->setFilterId($pListView->getFilterId());
		return $pDataModel;
	}

	/**
	 * @param DataFormConfigurationApplicantSearch $pDataFormConfiguration
	 * @return DistinctFieldsHandlerModel
	 */
	public function buildDataModelForSearchCriteria(
		DataFormConfigurationApplicantSearch $pDataFormConfiguration): DistinctFieldsHandlerModel
	{
		$formFields = $pDataFormConfiguration->getAvailableOptionsFields();
		$pDataModel = new DistinctFieldsHandlerModel();
		$pDataModel->setModule(onOfficeSDK::MODULE_SEARCHCRITERIA);
		$pDataModel->setDistinctFields($formFields);

		return $pDataModel;
	}
}