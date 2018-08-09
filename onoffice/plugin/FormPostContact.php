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
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\FormData;
use onOffice\WPlugin\FormPost;

/**
 *
 * Interest/Contact form
 *
 *  - send "Id" value if in estate-context
 *
 */

class FormPostContact
	extends FormPost
{
	/**
	 *
	 * @param DataFormConfiguration $pFormConfig
	 * @param int $formNo
	 *
	 */

	protected function analyseFormContentByPrefix(FormData $pFormData)
	{
		$pFormConfig = $pFormData->getDataFormConfiguration();
		$recipient = $pFormConfig->getRecipient();
		$subject = $pFormConfig->getSubject();

		$missingFields = $pFormData->getMissingFields();

		if ( count( $missingFields ) > 0 ) {
			$pFormData->setStatus(FormPost::MESSAGE_REQUIRED_FIELDS_MISSING );
		} else {
			if ( $pFormConfig->getCreateAddress() ) {
				$checkDuplicate = $pFormConfig->getCheckDuplicateOnCreateAddress();
				$responseNewAddress = $this->createOrCompleteAddress( $pFormData, $checkDuplicate );
				$response = $responseNewAddress;
			} else	{
				$response = true;
			}

			$response = $this->sendContactRequest( $pFormData, $recipient, $subject ) && $response;

			if ( true === $response ) {
				$pFormData->setStatus( FormPost::MESSAGE_SUCCESS );
			} else {
				$pFormData->setStatus( FormPost::MESSAGE_ERROR );
			}
		}
	}


	/**
	 *
	 * @param FormData $pFormData
	 * @return bool
	 *
	 */

	private function sendContactRequest( FormData $pFormData, $recipient = null, $subject = null )
	{
		$addressData = $pFormData->getAddressData();
		$values = $pFormData->getValues();
		$estateId = isset( $values['Id'] ) ? $values['Id'] : null;
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
		{ return Form::TYPE_CONTACT; }
}