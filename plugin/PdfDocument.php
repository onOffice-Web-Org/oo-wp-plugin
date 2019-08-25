<?php

/**
 *
 *    Copyright (C) 2016 onOffice Software AG
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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2016, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\ApiClientActionGetPdf;

/**
 *
 */

class PdfDocument
{
	/** @var int */
	private $_estateId = null;

	/** @var string */
	private $_language = null;

	/** @var string */
	private $_documentBinary = null;

	/** @var string */
	private $_mimeType = null;

	/** @var string */
	private $_template = null;

	/** @var SDKWrapper */
	private $_pSDKWrapper = null;


	/**
	 *
	 * @param int $estateId
	 * @param string $language
	 * @param string $template
	 *
	 */

	public function __construct(int $estateId, string $language, string $template)
	{
		$this->_estateId = $estateId;
		$this->_language = $language;
		$this->_template = $template;
		$this->_pSDKWrapper = new SDKWrapper();
	}


	/**
	 *
	 * @return bool
	 *
	 */

	public function fetch(): bool
	{
		$pSdkWrapper = $this->_pSDKWrapper;
		$parameters = $this->getParameters();

		$pApiClientAction = new ApiClientActionGetPdf
			($pSdkWrapper, onOfficeSDK::ACTION_ID_GET, 'pdf');
		$pApiClientAction->setParameters($parameters);
		$pApiClientAction->addRequestToQueue();
		$pSdkWrapper->sendRequests();

		if ($pApiClientAction->getResultStatus()) {
			$this->_mimeType = $pApiClientAction->getMimeTypeResult();
			$this->_documentBinary = $pApiClientAction->getResultRecords()[0];
		}
		return $this->_documentBinary !== null && $this->_mimeType !== null;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getParameters(): array
	{
		return [
			'estateid' => $this->_estateId,
			'language' => $this->_language,
			'gzcompress' => true,
			'template' => $this->_template,
		];
	}

	/** @param int $estateId */
	public function setEstateId(int $estateId)
		{ $this->_estateId = $estateId; }

	/** @return int */
	public function getEstateId(): int
		{ return $this->_estateId; }

	/** @param string $language */
	public function setLanguage(string $language)
		{ $this->_language = $language; }

	/** @return string */
	public function getLanguage(): string
		{ return $this->_language; }

	/** @return string binary */
	public function getDocumentBinary(): string
		{ return $this->_documentBinary; }

	/** @return string */
	public function getMimeType(): string
		{ return $this->_mimeType; }

	/** @return SDKWrapper */
	public function getSDKWrapper(): SDKWrapper
		{ return $this->_pSDKWrapper; }

	/** @param SDKWrapper $pSDKWrapper*/
	public function setSDKWrapper(SDKWrapper $pSDKWrapper)
		{ $this->_pSDKWrapper = $pSDKWrapper; }
}
