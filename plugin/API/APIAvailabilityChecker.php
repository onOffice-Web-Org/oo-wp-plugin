<?php

declare(strict_types=1);

namespace onOffice\WPlugin\API;


use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\SDKWrapper;

class APIAvailabilityChecker
{
	/**
	 *
	 * @throws ApiClientException
	 */

	public function checkAvailability(SDKWrapper $pSDKWrapper): array
	{
		$pApiCall = new APIClientActionGeneric(
			$pSDKWrapper->withCurlOptions(
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
		return $pApiCall->getResultRecords();
	}
}