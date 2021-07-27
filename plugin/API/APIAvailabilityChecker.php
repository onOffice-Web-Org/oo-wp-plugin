<?php

declare(strict_types=1);

namespace onOffice\WPlugin\API;


use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\SDKWrapper;

class APIAvailabilityChecker
{
	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/**
	 *
	 * @param SDKWrapper $pSDKWrapper
	 */

	public function __construct(SDKWrapper $pSDKWrapper = null)
	{
		$this->_pSDKWrapper = $pSDKWrapper ?? new SDKWrapper();
	}


	/**
	 *
	 * @throws ApiClientException
	 */

	public function isAvailable(): bool
	{
		$pApiCall = new APIClientActionGeneric(
			$this->_pSDKWrapper->withCurlOptions(
				[
					CURLOPT_SSL_VERIFYPEER => true,
					CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
					CURLOPT_CONNECTTIMEOUT => 1,
				]
			), onOfficeSDK::ACTION_ID_READ, 'basicsettings'
		);
		$pApiCall->setParameters(
			[
				"data" => [
					"basicData" => [
						"characteristicsCi" => [
							"logo",
							"color",
							"color2",
							"textcolorMail",
							"claim"
						],
					]
				]
			]
		);
		$pApiCall->addRequestToQueue()->sendRequests();
		return !empty($pApiCall->getResultRecords());
	}
}