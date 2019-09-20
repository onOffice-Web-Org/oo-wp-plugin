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
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationOwner;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Form\FormPostConfiguration;
use onOffice\WPlugin\Form\FormPostOwnerConfiguration;
use onOffice\WPlugin\FormData;
use onOffice\WPlugin\FormPost;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 */

class FormPostOwner
	extends FormPost
{
	/** @var FormData */
	private $_pFormData = null;

	/** @var FormPostOwnerConfiguration */
	private $_pFormPostOwnerConfiguration = null;


	/**
	 *
	 * @param FormPostConfiguration $pFormPostConfiguration
	 * @param FormPostOwnerConfiguration $pFormPostOwnerConfiguration
	 * @param FieldsCollectionBuilderShort $pBuilderShort
	 *
	 */

	public function __construct(FormPostConfiguration $pFormPostConfiguration,
		FormPostOwnerConfiguration $pFormPostOwnerConfiguration,
		FieldsCollectionBuilderShort $pBuilderShort)
	{
		$this->_pFormPostOwnerConfiguration = $pFormPostOwnerConfiguration;

		parent::__construct($pFormPostConfiguration, $pBuilderShort);
	}


	/**
	 *
	 * @param FormData $pFormData
	 *
	 */

	protected function analyseFormContentByPrefix(FormData $pFormData)
	{
		/* @var $pDataFormConfiguration DataFormConfigurationOwner */
		$pDataFormConfiguration = $pFormData->getDataFormConfiguration();
		$this->_pFormData = $pFormData;

		$recipient = $pDataFormConfiguration->getRecipient();
		$subject = $pDataFormConfiguration->getSubject();
		$checkduplicate = $pDataFormConfiguration->getCheckDuplicateOnCreateAddress();

		$addressId = $this->_pFormPostOwnerConfiguration->getFormAddressCreator()
			->createOrCompleteAddress($pFormData, $checkduplicate);
		$estateId = $this->createEstate();
		$this->createOwnerRelation($estateId, $addressId);

		if (null != $recipient) {
			$this->sendContactRequest($recipient, $estateId, $subject);
		}
	}


	/**
	 *
	 * @return int
	 *
	 */

	private function createEstate(): int
	{
		$estateValues = $this->getEstateData();
		$requestParams = ['data' => $estateValues];
		$pSDKWrapper = $this->_pFormPostOwnerConfiguration->getSDKWrapper();

		$pApiClientAction = new APIClientActionGeneric($pSDKWrapper, onOfficeSDK::ACTION_ID_CREATE,
			'estate');
		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->addRequestToQueue()->sendRequests();

		$records = $pApiClientAction->getResultRecords();
		$estateId = (int)$records[0]['id'];

		if ($estateId > 0) {
			return $estateId;
		}

		throw new ApiClientException($pApiClientAction);
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getEstateData(): array
	{
		$pFormData = $this->_pFormData;
		$pInputVariableReader = $this->_pFormPostOwnerConfiguration->getEstateListInputVariableReader();
		$configFields = $pFormData->getDataFormConfiguration()->getInputs();
		$submitFields = array_keys($pFormData->getValues());

		$estateFields = array_filter($submitFields, function($key) use ($configFields): bool {
			return onOfficeSDK::MODULE_ESTATE === $configFields[$key];
		});

		$estateData = [];

		foreach ($estateFields as $input) {
			$value = $pInputVariableReader->getFieldValue($input);

			if ($pInputVariableReader->getFieldType($input) === FieldTypes::FIELD_TYPE_MULTISELECT &&
				!is_array($value)) {
				$estateData[$input] = [$value];
			} else {
				$estateData[$input] = $value;
			}
		}

		return $estateData;
	}


	/**
	 *
	 * @param int $estateId
	 * @param int $addressId
	 * @throws ApiClientException
	 *
	 */

	private function createOwnerRelation(int $estateId, int $addressId)
	{
		$pSDKWrapper = $this->_pFormPostOwnerConfiguration->getSDKWrapper();

		$pApiClientAction = new APIClientActionGeneric
			($pSDKWrapper, onOfficeSDK::ACTION_ID_CREATE, 'relation');
		$pApiClientAction->setParameters([
			'relationtype' => onOfficeSDK::RELATION_TYPE_ESTATE_ADDRESS_OWNER,
			'parentid' => $estateId,
			'childid' => $addressId,
		]);

		$pApiClientAction->addRequestToQueue();
		$pSDKWrapper->sendRequests();

		if (!$pApiClientAction->getResultStatus()) {
			throw new ApiClientException($pApiClientAction);
		}
	}


	/**
	 *
	 * @param string $recipient
	 * @param int $estateId
	 * @param string $subject
	 * @throws ApiClientException
	 *
	 */

	private function sendContactRequest(string $recipient, int $estateId, $subject = null)
	{
		$addressData = $this->_pFormData->getAddressData();
		$values = $this->_pFormData->getValues();
		$message = $values['message'] ?? null;

		$requestParams = [
			'addressdata' => $addressData,
			'estateid' => $estateId,
			'message' => $message,
			'subject' => $subject,
			'referrer' => $this->_pFormPostOwnerConfiguration->getReferrer(),
			'formtype' => $this->_pFormData->getFormtype(),
		];

		if ($recipient !== '') {
			$requestParams['recipient'] = $recipient;
		}

		$pSDKWrapper = $this->_pFormPostOwnerConfiguration->getSDKWrapper();
		$pApiClientAction = new APIClientActionGeneric($pSDKWrapper, onOfficeSDK::ACTION_ID_DO,
			'contactaddress');
		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->addRequestToQueue()->sendRequests();

		$resultRecords = $pApiClientAction->getResultRecords();
		$result = ($resultRecords[0]['elements']['success'] ?? '') === 'success';

		if (!$result) {
			throw new ApiClientException($pApiClientAction);
		}
	}
}