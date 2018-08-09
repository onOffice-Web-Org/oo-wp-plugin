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
use onOffice\WPlugin\Controller\EstateListInputVariableReader;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationOwner;
use onOffice\WPlugin\Form;
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

		$pSDKWrapper = new SDKWrapper();
		$handle = $pSDKWrapper->addRequest
			(onOfficeSDK::ACTION_ID_CREATE, 'estate', $requestParams );
		$pSDKWrapper->sendRequests();
		$response = $pSDKWrapper->getRequestResponse( $handle );

		$result = isset( $response['data']['records'] ) &&
			count( $response['data']['records'] ) > 0;

		if ($result) {
			return $response['data']['records'][0]['id'];
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
		$pInputVariableReader = new EstateListInputVariableReader();
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
		$requestParams = array(
			'relationtype' => onOfficeSDK::RELATION_TYPE_ESTATE_ADDRESS_OWNER,
			'parentid' => $estateId,
			'childid' => $addressId,
		);

		$pSDKWrapper = new SDKWrapper();
		$handle = $pSDKWrapper->addRequest(
				onOfficeSDK::ACTION_ID_CREATE, 'relation', $requestParams );
		$pSDKWrapper->sendRequests();

		$response = $pSDKWrapper->getRequestResponse( $handle );

		$result = isset($response['status']) &&
			$response['status']['errorcode'] == 0 &&
			$response['status']['message'] == 'OK';


		return $result;
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
		$message = isset( $values['message'] ) ? $values['message'] : null;

		$requestParams = array(
			'addressdata' => $addressData,
			'estateid' => $estateId,
			'message' => $message,
			'subject' => $subject,
			'referrer' => filter_input( INPUT_SERVER, 'REQUEST_URI' ),
			'formtype' => $this->_pFormData->getFormtype(),
		);

		if ( null != $recipient ) {
			$requestParams['recipient'] = $recipient;
		}

		$pSDKWrapper = new SDKWrapper();
		$handle = $pSDKWrapper->addRequest
			(onOfficeSDK::ACTION_ID_DO, 'contactaddress', $requestParams );
		$pSDKWrapper->sendRequests();

		$response = $pSDKWrapper->getRequestResponse( $handle );

		$result = isset( $response['data']['records'][0]['elements']['success'] ) &&
			'success' == $response['data']['records'][0]['elements']['success'];

		return $result;
	}


	/** @return string */
	static protected function getFormType()
		{ return Form::TYPE_OWNER; }
}