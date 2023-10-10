<?php

namespace onOffice\WPlugin\Field\Collection;

use Generator;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Types\FieldTypes;

class FieldLoaderSupervisorValues implements FieldLoader
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
		$fullNameSupervisor = $this->getFullNameSupervisor();
		$supervisorElementsRecord = $this->getSupervisorField();

		if (empty($supervisorElementsRecord)) {
			return;
		}

		foreach ($supervisorElementsRecord as $fieldName => $fieldProperties) {
			if ($fieldName === 'benutzer') {
				$fieldProperties['type'] = FieldTypes::FIELD_TYPE_SINGLESELECT;
				$fieldProperties['module'] = onOfficeSDK::MODULE_SEARCHCRITERIA;
				$fieldProperties['permittedvalues'] = $fullNameSupervisor;
				$fieldProperties['content'] = __('Search Criteria', 'onoffice-for-wp-websites');
				yield $fieldName => $fieldProperties;
			}
		}
	}

	/**
	 * @return array
	 * @throws ApiClientException
	 */
	private function getFullNameSupervisor(): array
	{
		$pApiClientAction = new APIClientActionGeneric
		($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'users');

		$pApiClientAction->addRequestToQueue();
		$this->_pSDKWrapper->sendRequests();
		$supervisor = $pApiClientAction->getResultRecords();
		$fullNameSupervisor = [];

		if (empty($supervisor)) {
			return [];
		}

		foreach ($supervisor as $value) {
			$userName = $value['elements']['id'];
			$fullName = $value['elements']['lastname'] . ', ' . $value['elements']['firstname'];
			$fullNameSupervisor[$userName] = $fullName;
		}

		return $fullNameSupervisor;
	}

	/**
	 * @return array
	 * @throws ApiClientException
	 */
	private function getSupervisorField(): array
	{
		$parametersGetFieldList = [
			'labels' => true,
			'showContent' => true,
			'showTable' => true,
			'fieldList' => ['benutzer'],
			'language' => Language::getDefault(),
			'modules' => [onOfficeSDK::MODULE_ESTATE],
			'realDataTypes' => true
		];

		$pApiClientActionFields = new APIClientActionGeneric
		($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'fields');
		$pApiClientActionFields->setParameters($parametersGetFieldList);
		$pApiClientActionFields->addRequestToQueue()->sendRequests();
		$resultApi = $pApiClientActionFields->getResultRecords();

		if (empty($resultApi[0]['elements'])) {
			return [];
		}

		return $resultApi[0]['elements'];
	}
}