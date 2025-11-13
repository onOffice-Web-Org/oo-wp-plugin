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
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\Utility\__String;

/**
 *
 */

class PdfDocumentFetcher
{
    /** @var array */
    const WHITELIST_HEADERS = ['content-length', 'content-type'];

    /** @var APIClientActionGeneric */
    private $_pApiClientAction;

    /**
     * @param APIClientActionGeneric $pApiClientAction
     */
    public function __construct(APIClientActionGeneric $pApiClientAction)
    {
        $this->_pApiClientAction = $pApiClientAction;
    }

     /**
     * HTTP tunnel meant to transport big files with low RAM usage
     * @param PdfDocumentModel $pModel
     * @param string $url
     * @throws PdfDownloadException
     */
    public function proxyResult(PdfDocumentModel $pModel, string $url)
    {
        // First, make a HEAD request to get headers without downloading the full content
        $headResponse = wp_remote_head($url, ['timeout' => 30]);
        
        if (is_wp_error($headResponse)) {
            throw new PdfDownloadException();
        }

        $responseCode = wp_remote_retrieve_response_code($headResponse);
        if ($responseCode !== 200) {
            throw new PdfDownloadException();
        }

        // Set headers BEFORE streaming starts
        $headers = wp_remote_retrieve_headers($headResponse);
        foreach (self::WHITELIST_HEADERS as $headerName) {
            if (isset($headers[$headerName])) {
                header($headerName . ': ' . $headers[$headerName]);
            }
        }

        $filename = sprintf('%s_%s.pdf',
            str_replace('urn:onoffice-de-ns:smart:2.5:pdf:expose:lang:', '', $pModel->getTemplate()),
            $pModel->getEstateIdExternal());
        header('Content-Disposition: attachment; filename="'.$filename.'"');

        // Now stream the actual content
        $response = wp_remote_get($url, [
            'timeout' => 300,
            'stream' => true,
            'filename' => 'php://output',
        ]);

        if (is_wp_error($response)) {
            throw new PdfDownloadException();
        }
    }

    /**
     * @param PdfDocumentModel $pModel
     * @return string
     * @throws ApiClientException
     */
    public function fetchUrl(PdfDocumentModel $pModel): string
    {
        $pApiClientAction = $this->_pApiClientAction
            ->withActionIdAndResourceType(onOfficeSDK::ACTION_ID_GET, 'pdf');
        $parameters = $this->getParameters($pModel);
        $pApiClientAction->setParameters($parameters);
        $pApiClientAction->addRequestToQueue()->sendRequests();
        return $pApiClientAction->getResultRecords()[0]['elements'][0] ?? '';
    }

    /**
     * @param PdfDocumentModel $pModel
     * @return array
     */
    private function getParameters(PdfDocumentModel $pModel): array
    {
        return [
            'estateid' => $pModel->getEstateId(),
            'language' => $pModel->getLanguage(),
            'template' => $pModel->getTemplate(),
            'asurl' => true,
        ];
    }
}