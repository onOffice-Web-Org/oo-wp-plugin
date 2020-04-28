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

use onOffice\WPlugin\API\ApiClientException;

/**
 *
 */

class PdfDownload
{
	/** @var PdfDocumentFetcher */
	private $_pPdfDocumentFetcher;

	/** @var PdfDocumentModelValidator */
	private $_pPdfDocumentModelValidator;

	/**
	 * @param PdfDocumentFetcher $pPdfDocumentFetcher
	 * @param PdfDocumentModelValidator $pPdfDocumentModelValidator
	 */
	public function __construct(
		PdfDocumentFetcher $pPdfDocumentFetcher,
		PdfDocumentModelValidator $pPdfDocumentModelValidator)
	{
		$this->_pPdfDocumentFetcher = $pPdfDocumentFetcher;
		$this->_pPdfDocumentModelValidator = $pPdfDocumentModelValidator;
	}

	/**
	 * @param PdfDocumentModel $pModel
	 * @throws PdfDocumentModelValidationException
	 * @throws PdfDownloadException
	 * @throws ApiClientException
	 */
	public function download(PdfDocumentModel $pModel)
	{
		$pModelValidated = $this->_pPdfDocumentModelValidator->validate($pModel);
		$pDocumentResponse =  $this->_pPdfDocumentFetcher->fetch($pModelValidated);
		header('Content-Type: '.$pDocumentResponse->getContentType());
		header('Content-Length: '.$pDocumentResponse->getContentLength());
		header('Content-Disposition: attachment; filename="document_'.$pModel->getEstateId().'.pdf"');

		foreach ($pDocumentResponse->getIterator() as $chunk) {
			echo $chunk;
		}
	}
}
