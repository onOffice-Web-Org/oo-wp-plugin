<?php

/**
 *
 *    Copyright (C) 2016-2019 onOffice GmbH
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
use onOffice\WPlugin\API\ApiClientActionGetPdf;


/**
 *
 */

class PdfDocumentFetcher
{
	/** @var ApiClientActionGetPdf */
	private $_pApiClientActionGetPdf = null;


	/**
	 *
	 * @param ApiClientActionGetPdf $pApiClientActionGetPdf
	 *
	 */

	public function __construct(ApiClientActionGetPdf $pApiClientActionGetPdf)
	{
		$this->_pApiClientActionGetPdf = $pApiClientActionGetPdf;
	}


	/**
	 *
	 * @param PdfDocumentModel $pModel
	 * @return PdfDocumentResult
	 *
	 */

	public function fetch(PdfDocumentModel $pModel): PdfDocumentResult
	{
		$pApiClientAction = $this->_pApiClientActionGetPdf
			->withActionIdAndResourceType(onOfficeSDK::ACTION_ID_GET, 'pdf');
		$parameters = $this->getParameters($pModel);
		$pApiClientAction->setParameters($parameters);
		$pApiClientAction->addRequestToQueue()->sendRequests();
		$pPdfDocumentResult = new PdfDocumentResult
			($pApiClientAction->getMimeTypeResult(), $pApiClientAction->getResultRecords()[0]);
		return $pPdfDocumentResult;
	}


	/**
	 *
	 * @param PdfDocumentModel $pModel
	 * @return array
	 *
	 */

	private function getParameters(PdfDocumentModel $pModel): array
	{
		return [
			'estateid' => $pModel->getEstateId(),
			'language' => $pModel->getLanguage(),
			'template' => $pModel->getTemplate(),
			'gzcompress' => true,
		];
	}
}
