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

namespace onOffice\WPlugin\PDF;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\DataView\DataListViewFactory;
use onOffice\WPlugin\DataView\UnknownViewException;
use onOffice\WPlugin\Filter\DefaultFilterBuilderDetailView;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListView;

/**
 *
 * Validates a PdfDocumentModel by checking if the contained estate ID is visible in
 * the list with the name provided in PdfDocumentModel.
 *
 */

class PdfDocumentModelValidator
{
	/** @var APIClientActionGeneric */
	private $_pAPIClientActionGeneric;

	/** @var DataDetailViewHandler */
	private $_pDataDetailViewHandler;

	/** @var DataListViewFactory */
	private $_pDataListViewFactory;


	/**
	 *
	 * @param APIClientActionGeneric $pAPIClientActionGeneric
	 *
	 */

	public function __construct(
		APIClientActionGeneric $pAPIClientActionGeneric,
		DataDetailViewHandler $pDataDetailViewHandler,
		DataListViewFactory $pDataListViewFactory)
	{
		$this->_pAPIClientActionGeneric = $pAPIClientActionGeneric;
		$this->_pDataDetailViewHandler = $pDataDetailViewHandler;
		$this->_pDataListViewFactory = $pDataListViewFactory;
	}


	/**
	 *
	 * @param PdfDocumentModel $pModel
	 * @return PdfDocumentModel
	 * @throws PdfDocumentModelValidationException
	 *
	 */

	public function validate(PdfDocumentModel $pModel): PdfDocumentModel
	{
		$pModelClone = clone $pModel;
		try {
			$parametersGetEstate = $this->buildParameters($pModelClone);
		} catch (UnknownViewException $pEx) {
			throw new PdfDocumentModelValidationException('', 0, $pEx);
		}

		$pApiClientAction = $this->_pAPIClientActionGeneric
			->withActionIdAndResourceType(onOfficeSDK::ACTION_ID_READ, 'estate');
		$pApiClientAction->setParameters($parametersGetEstate);
		$pApiClientAction->addRequestToQueue()->sendRequests();

		if ($pModelClone->getTemplate() === '' ||
			!$pApiClientAction->getResultStatus() ||
			count($pApiClientAction->getResultRecords()) !== 1) {
			throw new PdfDocumentModelValidationException();
		}
		return $pModelClone;
	}


	/**
	 *
	 * @param PdfDocumentModel $pModel
	 * @return array
	 *
	 */

	private function buildParameters(PdfDocumentModel $pModel): array
	{
		$parametersGetEstate = [
			'data' => ['Id'],
			'estatelanguage' => $pModel->getLanguage(),
			'formatoutput' => 0,
		];

		$pView = $this->_pDataDetailViewHandler->getDetailView();
		$isDetailView = $pModel->getViewName() === $pView->getName();

		if ($isDetailView) {
			$pModel->setTemplate($pView->getExpose());
			$pDefaultFilterBuilder = new DefaultFilterBuilderDetailView();
			$pDefaultFilterBuilder->setEstateId($pModel->getEstateId());
			$filter = $pDefaultFilterBuilder->buildFilter();
		} else {
			 /* @var $pView \onOffice\WPlugin\DataView\DataListView */
			$pView = $this->_pDataListViewFactory->getListViewByName($pModel->getViewName());
			$pModel->setTemplate($pView->getExpose());
			$pDefaultFilterBuilder = new DefaultFilterBuilderListView($pView);
			$filter = $pDefaultFilterBuilder->buildFilter();
			$filter['Id'][] = ['op' => '=', 'val' => $pModel->getEstateId()];
			$parametersGetEstate['filterid'] = $pView->getFilterId();
		}

		$parametersGetEstate['filter'] = $filter;
		return $parametersGetEstate;
	}
}
