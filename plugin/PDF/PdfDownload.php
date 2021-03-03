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

use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\Utility\HTTPHeaders;
use onOffice\WPlugin\WP\WPOptionWrapperBase;
use onOffice\WPlugin\WP\WPOptionWrapperDefault;

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
     * @var HTTPHeaders
     */
    private $_pHttpHeadersGeneric;
    /**
     * @var WPOptionWrapperDefault
     */
    private $_pWPOptionWrapper;

    /**
     * @param PdfDocumentFetcher $pPdfDocumentFetcher
     * @param PdfDocumentModelValidator $pPdfDocumentModelValidator
     * @param HTTPHeaders $pHttpHeadersGeneric
     * @param WPOptionWrapperBase $pWPOptionWrapper
     */
	public function __construct(
		PdfDocumentFetcher $pPdfDocumentFetcher,
		PdfDocumentModelValidator $pPdfDocumentModelValidator,
		HTTPHeaders $pHttpHeadersGeneric,
		WPOptionWrapperBase $pWPOptionWrapper)
	{
		$this->_pHttpHeadersGeneric        = $pHttpHeadersGeneric;
		$this->_pPdfDocumentFetcher        = $pPdfDocumentFetcher;
		$this->_pPdfDocumentModelValidator = $pPdfDocumentModelValidator;
		$this->_pWPOptionWrapper           = $pWPOptionWrapper ?? new WPOptionWrapperDefault();
	}

	/**
	 * @param PdfDocumentModel $pModel
	 * @throws ApiClientException
	 * @throws PdfDocumentModelValidationException
	 * @throws PdfDownloadException
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function download(PdfDocumentModel $pModel)
	{
		if (!$this->_pWPOptionWrapper->getOption('onoffice-settings-google-bot-index-pdf-expose')) {
			$this->_pHttpHeadersGeneric->addHeader('X-Robots-Tag: googlebot: noindex, nofollow');
		}
		$pModelValidated = $this->_pPdfDocumentModelValidator->validate($pModel);
		$url = $this->_pPdfDocumentFetcher->fetchUrl($pModelValidated);
		$this->_pPdfDocumentFetcher->proxyResult($pModelValidated, $url);
	}
}
