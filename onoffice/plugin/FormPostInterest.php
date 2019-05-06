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
use onOffice\WPlugin\API\ApiClientException;
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
		$this->_pFormPostInterestConfiguration =
			$pFormPostInterestConfiguration ?? new FormPostInterestConfigurationDefault();
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

		$addressId = $this->createOrCompleteAddress($pFormData, $checkduplicate);
		$this->createSearchcriteria($pFormData, $addressId);

		if ($recipient != null) {
			$this->sendEmail($pFormData, $recipient, $subject);
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
		$formFields = parent::getAllowedPostVars($pFormConfig);
		return $this->getFormFieldsConsiderSearchcriteria($formFields);
	}


	/**
	 *
	 * @param FormData $pFormData
	 * @param string $recipient
	 * @param string $subject
	 * @throws ApiClientException
	 *
	 */

	private function sendEmail(FormData $pFormData, string $recipient, $subject = null)
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
			'X-Original-From' => $mailInteressent,
			'saveToAgentsLog' => false,
		];

		$pSDKWrapper = $this->_pFormPostInterestConfiguration->getSDKWrapper();
		$pApiClientAction = new APIClientActionGeneric
			($pSDKWrapper, onOfficeSDK::ACTION_ID_DO, 'sendmail');
		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->addRequestToQueue();
		$pSDKWrapper->sendRequests();

		if (!$pApiClientAction->getResultStatus()) {
			throw new ApiClientException($pApiClientAction);
		}
	}


	/**
	 *
	 * @param FormData $pFormData
	 * @param int $addressId
	 *
	 */

	private function createSearchcriteria(FormData $pFormData, int $addressId)
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

		if (!$pApiClientAction->getResultStatus()) {
			throw new ApiClientException($pApiClientAction);
		}
	}
}