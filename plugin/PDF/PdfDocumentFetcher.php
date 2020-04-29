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
		header('Content-Disposition: attachment; filename="document_'.$pModel->getEstateId().'.pdf"');
		$curl = curl_init($url);
		$pCallbackHeader = function($ch, $data): int {
			return $this->setHeaders($data);
		};

		$writeCallback = function($ch, $data): int {
			echo $data;
			return strlen($data);
		};

		curl_setopt_array($curl, [
			CURLOPT_FAILONERROR => true,
			CURLOPT_WRITEFUNCTION => $writeCallback,
			CURLOPT_HEADERFUNCTION => $pCallbackHeader,
			CURLOPT_BUFFERSIZE => 1024 ** 2,
		]);

		if (!curl_exec($curl) || curl_getinfo($curl, CURLINFO_HTTP_CODE) !== 200) {
			curl_close($curl);
			throw new PdfDownloadException;
		}
		curl_close($curl);
	}

	/**
	 * @param string $inputString
	 * @return int
	 */
	private function setHeaders(string $inputString): int
	{
		foreach (self::WHITELIST_HEADERS as $header) {
			if (__String::getNew(strtolower($inputString))->startsWith($header . ':') &&
				substr_count($inputString, ':') === 1) {
				header($inputString);
			}
		}
		return strlen($inputString);
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
