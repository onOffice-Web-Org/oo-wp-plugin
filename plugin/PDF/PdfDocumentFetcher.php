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

use AppendIterator;
use Generator;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\API\ApiClientException;

/**
 *
 */

class PdfDocumentFetcher
{
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
	 * @param PdfDocumentModel $pModel
	 * @return PdfDocumentResult
	 * @throws PdfDownloadException
	 * @throws ApiClientException
	 */
	public function fetch(PdfDocumentModel $pModel): PdfDocumentResult
	{
		$pApiClientAction = $this->_pApiClientAction
			->withActionIdAndResourceType(onOfficeSDK::ACTION_ID_GET, 'pdf');
		$parameters = $this->getParameters($pModel);
		$pApiClientAction->setParameters($parameters);
		$pApiClientAction->addRequestToQueue()->sendRequests();

		$url = $pApiClientAction->getResultRecords()[0]['elements'][0] ?? '';
		return $this->generateResult($url);
	}

	/**
	 * @param string $url
	 * @return PdfDocumentResult length of the document
	 * @throws PdfDownloadException
	 */
	private function generateResult(string $url) : PdfDocumentResult
	{
		$pResult = new AppendIterator;
		$curl = curl_init($url);
		$pCallback = function ($ch, $data) use ($pResult) {
			$pResult->append($this->makeGenerator($data));
			return strlen($data); //return the exact length
		};
		curl_setopt_array($curl, [
			CURLOPT_WRITEFUNCTION => $pCallback,
			CURLOPT_BUFFERSIZE => 1024,
		]);

		if (!curl_exec($curl) || curl_getinfo($curl, CURLINFO_HTTP_CODE) !== 200) {
			throw new PdfDownloadException;
		}

		// heads up! returns float!
		$length = (int)curl_getinfo($curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
		$mimeType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
		return new PdfDocumentResult($mimeType, $length, $pResult);
	}

	/**
	 * @param string $data
	 * @return Generator
	 */
	private function makeGenerator(string $data): Generator
	{
		yield $data;
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
