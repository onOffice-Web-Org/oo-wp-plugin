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

use DI\DependencyException;
use DI\NotFoundException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationOwner;
use onOffice\WPlugin\Field\Collection\FieldsCollectionConfiguratorForm;
use onOffice\WPlugin\Field\EstateFields;
use onOffice\WPlugin\Field\SearchcriteriaFields;
use onOffice\WPlugin\Form\FormPostConfiguration;
use onOffice\WPlugin\Form\FormPostOwnerConfiguration;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Utility\__String;

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

	/** @var EstateFields */
	private $_pEstateFields = null;

	/**
	 * @param FormPostConfiguration $pFormPostConfiguration
	 * @param FormPostOwnerConfiguration $pFormPostOwnerConfiguration
	 * @param SearchcriteriaFields $pSearchcriteriaFields
	 * @param FieldsCollectionConfiguratorForm $pFieldsCollectionConfiguratorForm
	 * @param EstateFields $pEstateFields
	 */
	public function __construct(
		FormPostConfiguration $pFormPostConfiguration,
		FormPostOwnerConfiguration $pFormPostOwnerConfiguration,
		SearchcriteriaFields $pSearchcriteriaFields,
		FieldsCollectionConfiguratorForm $pFieldsCollectionConfiguratorForm,
		EstateFields $pEstateFields)
	{
		$this->_pFormPostOwnerConfiguration = $pFormPostOwnerConfiguration;
		$this->_pEstateFields = $pEstateFields;
		parent::__construct($pFormPostConfiguration, $pSearchcriteriaFields, $pFieldsCollectionConfiguratorForm);
	}

	/**
	 *
	 * @param FormData $pFormData
	 * @throws API\APIEmptyResultException
	 * @throws ApiClientException
	 * @throws DependencyException
	 * @throws Field\UnknownFieldException
	 * @throws NotFoundException
	 */

	protected function analyseFormContentByPrefix(FormData $pFormData)
	{
		/* @var $pDataFormConfiguration DataFormConfigurationOwner */
		$pDataFormConfiguration = $pFormData->getDataFormConfiguration();
		$this->_pFormData = $pFormData;

		$recipient = $pDataFormConfiguration->getRecipientByUserSelection();
		$subject = $pDataFormConfiguration->getSubject();
		$estateData = $this->getEstateData();

		try {
			if ( $pDataFormConfiguration->getCreateOwner() ) {
				$checkduplicate = $pDataFormConfiguration->getCheckDuplicateOnCreateAddress();
				$contactType = $pDataFormConfiguration->getContactType();
				$pWPQuery = $this->_pFormPostOwnerConfiguration->getWPQueryWrapper()->getWPQuery();
				$estateId = $pWPQuery->get('estate_id', null);
				$addressId  = $this->_pFormPostOwnerConfiguration->getFormAddressCreator()
						->createOrCompleteAddress($pFormData, $checkduplicate, $contactType, $estateId);
				$estateData = $this->getEstateData();
				$estateId   = $this->createEstate( $estateData );
				$this->createOwnerRelation( $estateId, $addressId );
				$this->setNewsletter( $addressId );
			}
		} finally {
			if ( null != $recipient ) {
				$this->sendContactRequest( $recipient, $estateId ?? 0, $estateData, $subject );
			}
		}
	}

	/**
	 *
	 * @param array $estateData
	 * @return int
	 * @throws API\APIEmptyResultException
	 * @throws ApiClientException
	 */

	private function createEstate(array $estateData): int
	{
		$requestParams = ['data' => $estateData];
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
	 * @param  FormData  $pFormData
	 *
	 * @return void
	 * @throws ApiClientException
	 * @throws Field\UnknownFieldException
	 */

	private function setNewsletter( $addressId )
	{
		if ( ! $this->_pFormPostOwnerConfiguration->getNewsletterAccepted() ) {
			// No subscription for newsletter, which is ok
			return;
		}

		$pSDKWrapper      = $this->_pFormPostOwnerConfiguration->getSDKWrapper();
		$pAPIClientAction = new APIClientActionGeneric
		( $pSDKWrapper, onOfficeSDK::ACTION_ID_DO, 'registerNewsletter' );
		$pAPIClientAction->setParameters( [ 'register' => true ] );
		$pAPIClientAction->setResourceId( $addressId );
		$pAPIClientAction->addRequestToQueue()->sendRequests();

		if ( ! $pAPIClientAction->getResultStatus() ) {
			throw new ApiClientException( $pAPIClientAction );
		}
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
	 * @param array $inputData
	 * @return string
	 */
	private function createStringFromInputData(array $inputData): string
	{
		$data = [];

		foreach ($inputData as $key => $value) {
			$data []= ucfirst($key).': '.ucfirst($value);
		}

		return implode("\n", $data);
	}

	/**
	 *
	 * @param string $recipient
	 * @param int $estateId
	 * @param array $estateValues
	 * @param string $subject
	 * @throws API\APIEmptyResultException
	 * @throws ApiClientException
	 * @throws DependencyException
	 * @throws NotFoundException
	 */

	private function sendContactRequest(string $recipient, int $estateId, array $estateValues, $subject=null)
	{
		$addressData = $this->_pFormData->getAddressData($this->getFieldsCollection());
		$values = $this->_pFormData->getValues();
		$filledEstateData = $this->_pEstateFields->getFieldLabelsOfInputs($estateValues, $this->getFieldsCollection());
		$estateData = array_keys($estateValues);
		$formType = $this->_pFormData->getFormtype();
		$informationEnterFromInputOwnerForm = $this->createStringFromInputData($filledEstateData);
		if (empty($estateId)) {
			$formType .= "\n" . $informationEnterFromInputOwnerForm;
		}

		$requestParams = [
			'addressdata' => $addressData,
			'estateid' => $estateId,
			'message' => $values['message'] ?? null,
			'subject' => $subject,
			'referrer' => $this->_pFormPostOwnerConfiguration->getReferrer(),
			'formtype' => $formType,
		];

		if ($estateData != []) {
			$requestParams['estatedata'] = $estateData;
		}

		if ($recipient !== '') {
			$requestParams['recipient'] = $recipient;
		}

		if (isset($addressData['newsletter'])) {
			$requestParams['addressdata']['newsletter_aktiv'] = $this->_pFormPostOwnerConfiguration
				->getNewsletterAccepted();
		}
		if (isset($addressData['gdprcheckbox']) && $addressData['gdprcheckbox']) {
			$requestParams['addressdata']['DSGVOStatus'] = "speicherungzugestimmt";
		}
		unset($requestParams['addressdata']['gdprcheckbox']);

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
