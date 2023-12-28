<?php

namespace onOffice\WPlugin\Field\Collection;

use Generator;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Types\FieldTypes;

class FieldLoaderCountryValues implements FieldLoader
{
	/** @var SDKWrapper */
	private $_pSDKWrapper;

	public function __construct(SDKWrapper $_pSDKWrapper)
	{
		$this->_pSDKWrapper = $_pSDKWrapper;
	}

	/**
	 * @throws ApiClientException
	 */
	public function load(): Generator
	{
		$valueCountry = $this->getValueCountry();
		$field = $this->getField();

		if (empty($fieldArray)) {
			return;
		}

		foreach ($field as $fieldName => $fieldProperties) {
			if ($fieldName === 'Land') {
				$fieldProperties['type'] = FieldTypes::FIELD_TYPE_SINGLESELECT;
				$fieldProperties['module'] = onOfficeSDK::MODULE_ADDRESS;
				$fieldProperties['permittedvalues'] = $valueCountry;
				yield $fieldName => $fieldProperties;
			}
		}
	}

	/**
	 * @return array
	 * @throws ApiClientException
	 */
	private function getValueCountry(): array
	{
		$parameters = [
			"fieldname"=> "Land"
		];

		$pApiClientAction = new APIClientActionGeneric
		($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'confignewaddressfields');
		$pApiClientAction->setParameters($parameters);
		$pApiClientAction->setResourceId("fieldvalues");
		$pApiClientAction->addRequestToQueue()->sendRequests();

		$userRecords = $pApiClientAction->getResultRecords();
		if (empty($userRecords)) {
			return [];
		}

		$data = [];
		foreach($userRecords as $key => $value) {
			$data[$value["id"]] = $value["elements"]['title'];
		}

		return $data;
	}

	/**
	 * @return array
	 * @throws ApiClientException
	 */
	private function getField(): array
	{
		$parameters = [
			'labels' => true,
			'showContent' => true,
			'showTable' => true,
			'fieldList' => ['Land'],
			'language' => Language::getDefault(),
			'modules' => [onOfficeSDK::MODULE_ADDRESS],
			'realDataTypes' => true
		];

		$pApiClientActionFields = new APIClientActionGeneric
		($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'fields');
		$pApiClientActionFields->setParameters($parameters);
		$pApiClientActionFields->addRequestToQueue()->sendRequests();
		$result = $pApiClientActionFields->getResultRecords();

		if (empty($result[0]['elements'])) {
			return [];
		}

		return $result[0]['elements'];
	}
}