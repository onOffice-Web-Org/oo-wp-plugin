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
		$fullNameSupervisor = [];
		$userIds = $this->getUserIds();
		$parameters = [
			"data" => ["Vorname", "Nachname", "Name", "Kuerzel"],
			"filter" => [
				"Nr" => [["op" => "in", "val" => $userIds]]
			]
		];

		$pApiClientAction = new APIClientActionGeneric
		($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'user');
		$pApiClientAction->setParameters($parameters);
		$pApiClientAction->addRequestToQueue()->sendRequests();

		$userRecords = $pApiClientAction->getResultRecords();

		if (empty($userRecords)) {
			return [];
		}

		foreach ($userRecords as $userRecord) {
			$userId = $userRecord['id'];
			$firstName = $userRecord['elements']['Vorname'];
			$lastName = $userRecord['elements']['Nachname'];
			$userName = $userRecord['elements']['Name'];
			$userCode = $userRecord['elements']['Kuerzel'];

			if (!empty($firstName) && !empty($lastName)) {
				$fullName = $lastName . ', ' . $firstName;
			} else {
				if (!empty($firstName) && empty($lastName)) {
					$fullName = $firstName;
				} elseif (empty($firstName) && !empty($lastName)) {
					$fullName = $lastName;
				} else {
					$fullName = '(' .$userName . ')';
				}
			}
			$fullName .= '(' . $userCode . ')';

			$fullNameSupervisor[$userId] = $fullName;
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
		$result = $pApiClientActionFields->getResultRecords();

		if (empty($result[0]['elements'])) {
			return [];
		}

		return $result[0]['elements'];
	}

	/**
	 * @return array
	 * @throws ApiClientException
	 */
	private function getUserIds(): array
	{
		$pApiClientAction = new APIClientActionGeneric
		($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'users');

		$pApiClientAction->addRequestToQueue();
		$this->_pSDKWrapper->sendRequests();
		$result = $pApiClientAction->getResultRecords();
		$userIds= [];

		if (empty($result)) {
			return [];
		}

		foreach ($result as $value) {
			$userIds[] = $value['elements']['id'];
		}

		return $userIds;
	}
}