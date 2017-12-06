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

class FormPostApplicant
	extends FormPost
{
	/** @var FormPost */
	private static $_pInstance = null;

	/**
	 *
	 * @return FormPost
	 *
	 */

	public static function getInstance() {
		if ( is_null( self::$_pInstance ) ) {
			self::$_pInstance = new static;
		}

		return self::$_pInstance;
	}


	/**
	 *
	 */

	private function __construct() { }


	/** @return string */
	protected function getFormType(){
		return Form::TYPE_INTEREST; }


	/**
	 *
	 * @param type $estateId
	 * @param type $addressId
	 * @return bool $result
	 *
	 */

	protected function analyseFormContentByPrefix( $prefix, $formNo = null ) {
		$formConfig = ConfigWrapper::getInstance()->getConfigByKey( 'forms' );
		$recipient = null;
		$subject = null;
		$checkduplicate = false;

		$configByPrefix = $formConfig[$prefix];
		$formFields = $configByPrefix['inputs'];
		$newFormFields = $this->getFormFieldsConsiderSearchcriteria($formFields);

		$formData = array_intersect_key( $_POST, $newFormFields );

		$pFormData = new FormData( $prefix, $formNo );
		$pFormData->setConfigFields($newFormFields);
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

			if (null != $responseAddress)
			{
				$responseSearchcriteria = $this->createSearchcriteria($pFormData, $responseAddress);

				if ($responseSearchcriteria &&
					null != $recipient &&
					null != $subject)
				{
					$response = $this->sendEmail($pFormData, $recipient, $subject);
				}
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
	 * @param \onOffice\WPlugin\FormData $pFormData
	 * @param type $recipient
	 * @param type $subject
	 * @return boolean
	 *
	 */

	private function sendEmail(FormData $pFormData, $recipient, $subject = null) {
		$addressData = $pFormData->getAddressData();
		$name = $addressData['Name'];
		$vorname = $addressData['Vorname'];
		$mailInteressent = $addressData['Email'];

		$body = 'Sehr geehrte Damen und Herren,'."\n\n".'

ein neuer Interessent hat sich über das Kontaktformular auf Ihrer Webseite eingetragen. Die Adresse ('.$vorname.' '.$name.') wurde bereits in Ihrem System eingetragen.'."\n\n".'

Herzliche Grüße
Ihr onOffice Team';

		$requestParams = array(
			'anonymousEmailidentity' => true,
			'body' => $body,
			'subject' => $subject,
			'replyto' => $mailInteressent,
		);

		$requestParams['receiver'] = array($recipient);

		$pSDKWrapper = new SDKWrapper();
		$pSDKWrapper->removeCacheInstances();

		$handle = $pSDKWrapper->addRequest(
				onOfficeSDK::ACTION_ID_DO, 'sendmail', $requestParams );
		$pSDKWrapper->sendRequests();

		$response = $pSDKWrapper->getRequestResponse( $handle );

		$result = isset( $response['data']['records'] ) &&
				count( $response['data']['records'] ) > 0;

		return $result;
	}


	/**
	 *
	 * @param \onOffice\WPlugin\FormData $pFormData
	 * @param int $addressId
	 * @return boolean
	 *
	 */

	private function createSearchcriteria( FormData $pFormData, $addressId ) {
		$requestParams = array('data' => $pFormData->getSearchcriteriaData());
		$requestParams['addressid'] = $addressId;

		$pSDKWrapper = new SDKWrapper();
		$pSDKWrapper->removeCacheInstances();

		$handle = $pSDKWrapper->addRequest(
				onOfficeSDK::ACTION_ID_CREATE, 'searchcriteria', $requestParams );
		$pSDKWrapper->sendRequests();

		$response = $pSDKWrapper->getRequestResponse( $handle );

		$result = isset( $response['data']['records'] ) &&
				count( $response['data']['records'] ) > 0;

		return $result;
	}


	/**
	 *
	 * @param FormData $pFormData
	 * @param bool $mergeExisting
	 * @return bool
	 *
	 */

	private function createOrCompleteAddress( FormData $pFormData, $mergeExisting = false )	{
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
}