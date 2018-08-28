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


namespace onOffice\WPlugin;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationInterest;
use onOffice\WPlugin\Form\FormPostConfiguration;
use onOffice\WPlugin\Form\FormPostInterestConfiguration;
use onOffice\WPlugin\Form\FormPostInterestConfigurationDefault;
use onOffice\WPlugin\FormData;
use onOffice\WPlugin\FormPost;

/**
 *
 * Applicant form. A person registers itself and leaves his/her search criteria
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2016, onOffice(R) Software AG
 *
 */

class FormPostInterest
	extends FormPost
{
	/** @var FormPostInterestConfiguration */
	private $_pFormPostInterestConfiguration = null;


	/**
	 *
	 * @param FormPostConfiguration $pFormPostConfiguration
	 * @param FormPostInterestConfiguration $pFormPostInterestConfiguration
	 *
	 */

	public function __construct(FormPostConfiguration $pFormPostConfiguration = null,
		FormPostInterestConfiguration $pFormPostInterestConfiguration = null)
	{
		parent::__construct($pFormPostConfiguration);

		if ($pFormPostInterestConfiguration === null) {
			$pFormPostInterestConfiguration = new FormPostInterestConfigurationDefault();
		}

		$this->_pFormPostInterestConfiguration = $pFormPostInterestConfiguration;
	}


	/**
	 *
	 * @param FormData $pFormData
	 *
	 */

	protected function analyseFormContentByPrefix(FormData $pFormData)
	{
		/* @var $pFormConfiguration DataFormConfigurationInterest */
		$pFormConfiguration = $pFormData->getDataFormConfiguration();
		$recipient = $pFormConfiguration->getRecipient();
		$subject = $pFormConfiguration->getSubject();
		$checkduplicate = $pFormConfiguration->getCheckDuplicateOnCreateAddress();
		$missingFields = $pFormData->getMissingFields();

		if (count($missingFields) > 0) {
			$pFormData->setStatus(FormPost::MESSAGE_REQUIRED_FIELDS_MISSING);
		} else {
			$response = false;
			$responseAddress = $this->createOrCompleteAddress($pFormData, $checkduplicate);

			if (false !== $responseAddress) {
				$responseSearchcriteria = $this->createSearchcriteria($pFormData, $responseAddress);
				if ($recipient == null) {
					$response = $responseSearchcriteria;
				} elseif ($responseSearchcriteria) {
					$response = $this->sendEmail($pFormData, $recipient, $subject);
				}
			}

			if (true === $response) {
				$pFormData->setStatus(FormPost::MESSAGE_SUCCESS);
			} else {
				$pFormData->setStatus(FormPost::MESSAGE_ERROR);
			}
		}
	}


	/**
	 *
	 * @param DataFormConfiguration $pFormConfig
	 * @return array
	 *
	 */

	protected function getAllowedPostVars(DataFormConfiguration $pFormConfig): array
	{
		$formFields = $pFormConfig->getInputs();
		return $this->getFormFieldsConsiderSearchcriteria($formFields);
	}


	/**
	 *
	 * @param FormData $pFormData
	 * @param string $recipient
	 * @param string $subject
	 * @return bool
	 *
	 */

	private function sendEmail(FormData $pFormData, $recipient, $subject = null)
	{
		$addressData = $pFormData->getAddressData();
		$name = $addressData['Name'] ?? null;
		$firstName = $addressData['Vorname'] ?? null;
		$mailInteressent = $addressData['Email'] ?? null;

		$body = 'Sehr geehrte Damen und Herren,'."\n\n"
				.'ein neuer Interessent hat sich über das Kontaktformular auf Ihrer Webseite '
				.'eingetragen. Die Adresse ('.$firstName.' '.$name.') wurde bereits in Ihrem System '
				.'eingetragen.'."\n\n"
				.'Herzliche Grüße'."\n"
				.'Ihr onOffice Team';

		$requestParams = [
			'anonymousEmailidentity' => true,
			'body' => $body,
			'subject' => $subject,
			'replyto' => $mailInteressent,
			'receiver' => [$recipient],
		];

		$pSDKWrapper = $this->_pFormPostInterestConfiguration->getSDKWrapper();
		$pApiClientAction = new APIClientActionGeneric
			($pSDKWrapper, onOfficeSDK::ACTION_ID_DO, 'sendmail');
		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->addRequestToQueue();
		$pSDKWrapper->sendRequests();

		return $pApiClientAction->getResultStatus();
	}


	/**
	 *
	 * @param FormData $pFormData
	 * @param int $addressId
	 * @return bool
	 *
	 */

	private function createSearchcriteria(FormData $pFormData, $addressId)
	{
		$requestParams = [
			'data' => $pFormData->getSearchcriteriaData(),
			'addressid' => $addressId,
		];

		$pSDKWrapper = $this->_pFormPostInterestConfiguration->getSDKWrapper();
		$pApiClientAction = new APIClientActionGeneric
			($pSDKWrapper, onOfficeSDK::ACTION_ID_CREATE, 'searchcriteria');

		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->addRequestToQueue();
		$pSDKWrapper->sendRequests();

		return $pApiClientAction->getResultStatus();
	}
}