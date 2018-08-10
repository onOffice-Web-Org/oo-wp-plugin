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
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationOwner;
use onOffice\WPlugin\Form\FormPostConfiguration;
use onOffice\WPlugin\Form\FormPostOwnerConfiguration;
use onOffice\WPlugin\Form\FormPostOwnerConfigurationDefault;
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
	 *
	 */

	public function __construct(FormPostConfiguration $pFormPostConfiguration = null,
		FormPostOwnerConfiguration $pFormPostOwnerConfiguration = null)
	{
		if ($pFormPostOwnerConfiguration === null) {
			$pFormPostOwnerConfiguration = new FormPostOwnerConfigurationDefault();
		}

		$this->_pFormPostOwnerConfiguration = $pFormPostOwnerConfiguration;

		parent::__construct($pFormPostConfiguration);
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

		$missingFields = $pFormData->getMissingFields();

		if ( count( $missingFields ) > 0 ) {
			$pFormData->setStatus(FormPost::MESSAGE_REQUIRED_FIELDS_MISSING );
		} else {
			$response = false;
			$responseAddress = $this->createOrCompleteAddress($pFormData, $checkduplicate);
			$responseEstate = $this->createEstate();

			if ($responseAddress !== false &&
				$responseEstate !== false) {
				$response = $this->createOwnerRelation($responseEstate, $responseAddress);
			}

			if (null != $recipient && $responseEstate && $responseAddress) {
				$response = $response &&
					$this->sendContactRequest($recipient, $responseEstate, $subject);
			}

			if ($response) {
				$pFormData->setStatus( FormPost::MESSAGE_SUCCESS );
			} else {
				$pFormData->setStatus( FormPost::MESSAGE_ERROR );
			}
		}
	}


	/**
	 *
	 * @return bool
	 *
	 */

	private function createEstate()
	{
		$estateValues = $this->getEstateData();
		$requestParams = ['data' => $estateValues];
		$pSDKWrapper = $this->_pFormPostOwnerConfiguration->getSDKWrapper();

		$pApiClientAction = new APIClientActionGeneric($pSDKWrapper, onOfficeSDK::ACTION_ID_CREATE,
			'estate');
		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->addRequestToQueue();
		$pSDKWrapper->sendRequests();
		$records = $pApiClientAction->getResultRecords();

		if ($records !== null && $records !== []) {
			return $records[0]['id'];
		}

		return false;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getEstateData()
	{
		$pFormData = $this->_pFormData;
		$pInputVariableReader = $this->_pFormPostOwnerConfiguration->getEstateListInputVariableReader();
		$configFields = $pFormData->getDataFormConfiguration()->getInputs();
		$submitFields = array_keys($pFormData->getValues());

		$estateFields = array_filter($submitFields, function($key) use ($configFields) {
			return onOfficeSDK::MODULE_ESTATE === $configFields[$key];
		});

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
	 * @return bool $result
	 *
	 */

	private function createOwnerRelation($estateId, $addressId)
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

		return $pApiClientAction->getResultStatus();
	}


	/**
	 *
	 * @param string $recipient
	 * @param int $estateId
	 * @param string $subject
	 * @return bool
	 *
	 */

	private function sendContactRequest($recipient, $estateId, $subject = null)
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

		if ( null != $recipient ) {
			$requestParams['recipient'] = $recipient;
		}

		$pSDKWrapper = $this->_pFormPostOwnerConfiguration->getSDKWrapper();
		$pApiClientAction = new APIClientActionGeneric($pSDKWrapper, onOfficeSDK::ACTION_ID_DO,
			'contactaddress');
		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->addRequestToQueue();
		$pSDKWrapper->sendRequests();

		$result = false;

		if ($pApiClientAction->getResultStatus()) {
			$resultRecords = $pApiClientAction->getResultRecords();
			$result = ($resultRecords[0]['elements']['success'] ?? '') === 'success';
		}

		return $result;
	}
}