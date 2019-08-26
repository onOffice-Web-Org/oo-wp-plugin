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
	 *
	 * @param PdfDocumentFetcher $pPdfDocumentFetcher
	 * @param PdfDocumentModelValidator $pPdfDocumentModelValidator
	 *
	 */

	public function __construct(
		PdfDocumentFetcher $pPdfDocumentFetcher,
		PdfDocumentModelValidator $pPdfDocumentModelValidator)
	{
		$this->_pPdfDocumentFetcher = $pPdfDocumentFetcher;
		$this->_pPdfDocumentModelValidator = $pPdfDocumentModelValidator;
	}


	/**
	 *
	 * @param PdfDocumentModel $pModel
	 * @return PdfDocumentResult
	 * @throws PdfDocumentModelValidationException
	 *
	 */

	public function download(PdfDocumentModel $pModel): PdfDocumentResult
	{
		$pModelValidated = $this->_pPdfDocumentModelValidator->validate($pModel);
		return $this->_pPdfDocumentFetcher->fetch($pModelValidated);
	}
}
