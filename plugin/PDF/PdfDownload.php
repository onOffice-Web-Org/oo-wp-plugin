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
    private $_httpHeadersGeneric;

    /**
     * @param PdfDocumentFetcher $pPdfDocumentFetcher
     * @param PdfDocumentModelValidator $pPdfDocumentModelValidator
     * @param HTTPHeaders $httpHeadersGeneric
     */
	public function __construct(
		PdfDocumentFetcher $pPdfDocumentFetcher,
		PdfDocumentModelValidator $pPdfDocumentModelValidator,
        HTTPHeaders $httpHeadersGeneric)
	{
		$this->_httpHeadersGeneric = $httpHeadersGeneric;
		$this->_pPdfDocumentFetcher = $pPdfDocumentFetcher;
		$this->_pPdfDocumentModelValidator = $pPdfDocumentModelValidator;
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
		$pModelValidated = $this->_pPdfDocumentModelValidator->validate($pModel);
		$url = $this->_pPdfDocumentFetcher->fetchUrl($pModelValidated);
		$this->_pPdfDocumentFetcher->proxyResult($pModelValidated, $url);
	}

    /**
     * @param bool $accept
     */
    public function settingGoogleBotAcceptIndex(bool $accept = true)
    {
        if ($accept === false)
        {
            $this->_httpHeadersGeneric->addHeader('X-Robots-Tag: googlebot: noindex, nofollow');
        }
    }
}
