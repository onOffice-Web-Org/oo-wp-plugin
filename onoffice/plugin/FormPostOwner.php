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

use onOffice\WPlugin\Form;
use onOffice\WPlugin\FormData;
use onOffice\WPlugin\FormPost;
use onOffice\SDK\onOfficeSDK;

/**
 *
 */

class FormPostOwner
	extends FormPost
{
	/**
	 *
	 * @param string $prefix
	 * @param string $formNo
	 *
	 */

	protected function analyseFormContentByPrefix( $prefix, $formNo = null )
	{
		$formConfig = ConfigWrapper::getInstance()->getConfigByKey( 'forms' );
		$recipient = null;
		$subject = null;
		$checkduplicate = false;

		$configByPrefix = $formConfig[$prefix];
		$formFields = $configByPrefix['inputs'];

		$formData = array_intersect_key( $_POST, $formFields );
		$pFormData = new FormData( $prefix, $formNo );
		$pFormData->setRequiredFields( $configByPrefix['required'] );
		$pFormData->setFormtype( $configByPrefix['formtype'] );

		if ( isset( $configByPrefix['recipient'] ) ) {
			$recipient = $configByPrefix['recipient'];
		}

		if ( isset( $configByPrefix['subject'] ) ) {
			$subject = $configByPrefix['subject'];
		}

		if ( isset( $configByPrefix['checkduplicate']))
		{
			$checkduplicate = $configByPrefix['checkduplicate'];
		}

		$this->setFormDataInstances($prefix, $formNo, $pFormData);
		$pFormData->setValues( $formData );

		$missingFields = $pFormData->getMissingFields();

		if ( count( $missingFields ) > 0  )
		{
			$pFormData->setStatus(FormPost::MESSAGE_REQUIRED_FIELDS_MISSING );
		}
		else
		{
			$response = false;

			$responseAddress = $this->createOrCompleteAddress($pFormData, $checkduplicate);
			$responseEstate = $this->modifyOrCreateEstate($pFormData);

			if ($responseAddress !== false &&
				$responseEstate !== false)
			{
				$response = $this->createOwnerRelation($responseEstate, $responseAddress);
			}

			$pFormData->setFormSent( true );

			if (null != $recipient && null != $subject)
			{
				$response = $this->sendContactRequest( $pFormData, $recipient, $subject, $responseEstate) && $response;
			}

			if ( true === $response )
			{
				$pFormData->setStatus( FormPost::MESSAGE_SUCCESS );
			}
			else
			{
				$pFormData->setStatus( FormPost::MESSAGE_ERROR );
			}
		}

	}


	/**
	 *
	 * @param FormData $pFormData
	 * @param bool $mergeExisting
	 * @return bool
	 *
	 */

	private function createOrCompleteAddress( FormData $pFormData, $mergeExisting = false )
	{
		$requestParams = $pFormData->getAddressDataForApiCall();
		$requestParams['checkDuplicate'] = $mergeExisting;

		$pSDKWrapper = new SDKWrapper();
		$pSDKWrapper->removeCacheInstances();

		$handle = $pSDKWrapper->addRequest(
				onOfficeSDK::ACTION_ID_CREATE, 'address', $requestParams );
		$pSDKWrapper->sendRequests();

		$response = $pSDKWrapper->getRequestResponse( $handle );

		$result = isset( $response['data']['records'] ) &&
				count( $response['data']['records'] ) > 0;

		if ($result)
		{
			return $response['data']['records'][0]['id'];
		}

		return false;
	}


	/**
	 *
	 * @param array $estateValues
	 * @return bool
	 *
	 */

	private function createEstate( $estateValues)
	{
		$requestParams = array('data' => $estateValues);

		$pSDKWrapper = new SDKWrapper();
		$pSDKWrapper->removeCacheInstances();
		$handle = $pSDKWrapper->addRequest(
				onOfficeSDK::ACTION_ID_CREATE, 'estate', $requestParams );
		$pSDKWrapper->sendRequests();

		$response = $pSDKWrapper->getRequestResponse( $handle );

		$result = isset( $response['data']['records'] ) &&
				count( $response['data']['records'] ) > 0;

		if ($result)
		{
			return $response['data']['records'][0]['id'];
		}

		return false;
	}



	/**
	 *
	 * @param int $estateId
	 * @param array $estateValues
	 * @return mixed
	 *
	 */

	private function updateEstate($estateId, $estateValues)
	{
		$requestParams = array('data' => $estateValues);

		$pSDKWrapper = new SDKWrapper();
		$pSDKWrapper->removeCacheInstances();
		$handle = $pSDKWrapper->addFullRequest(
				onOfficeSDK::ACTION_ID_MODIFY, 'estate', $estateId, $requestParams );

		$pSDKWrapper->sendRequests();

		$response = $pSDKWrapper->getRequestResponse( $handle );

		if (isset( $response['data']['records'] ) &&
			count( $response['data']['records'] ) > 0)
		{
			return $estateId;
		}

		return false;
	}



	/**
	 *
	 * @param \onOffice\WPlugin\FormData $pFormData
	 * @return mixed
	 *
	 */

	private function modifyOrCreateEstate(FormData $pFormData)
	{
		$estateValues = $pFormData->getEstateData();
		$estateId = isset( $estateValues['Id'] ) ? $estateValues['Id'] : null;
		$result = null;

		if (null != $estateId)
		{
			unset($estateValues['Id']);
			$result = $this->updateEstate($estateId, $estateValues);
		}
		else
		{
			$result = $this->createEstate($estateValues);
		}

		return $result;
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
		$result = false;

		$requestParams = array
			(
				'relationtype' => onOfficeSDK::RELATION_TYPE_ESTATE_ADDRESS_OWNER,
				'parentid' => $estateId,
				'childid' => $addressId,
			);

		$pSDKWrapper = new SDKWrapper();
		$pSDKWrapper->removeCacheInstances();

		$handle = $pSDKWrapper->addRequest(
				onOfficeSDK::ACTION_ID_CREATE, 'relation', $requestParams );
		$pSDKWrapper->sendRequests();

		$response = $pSDKWrapper->getRequestResponse( $handle );

		if (isset($response['status']))
		{
			if ($response['status']['errorcode'] == 0 &&
				$response['status']['message'] == 'OK')
			{
				$result = true;
			}
		}

		return $result;
	}

	/**
	 *
	 * @param FormData $pFormData
	 * @return bool
	 *
	 */

	private function sendContactRequest( FormData $pFormData, $recipient = null, $subject = null, $estateId) {
		$addressData = $pFormData->getAddressData();
		$values = $pFormData->getValues();
		$message = isset( $values['message'] ) ? $values['message'] : null;

		$requestParams = array(
			'addressdata' => $addressData,
			'estateid' => $estateId,
			'message' => $message,
			'subject' => $subject,
			'referrer' => filter_input( INPUT_SERVER, 'REQUEST_URI' ),
			'formtype' => $pFormData->getFormtype(),
		);

		if ( null != $recipient ) {
			$requestParams['recipient'] = $recipient;
		}

		$pSDKWrapper = new SDKWrapper();
		$pSDKWrapper->removeCacheInstances();

		$handle = $pSDKWrapper->addRequest(
				onOfficeSDK::ACTION_ID_DO, 'contactaddress', $requestParams );
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