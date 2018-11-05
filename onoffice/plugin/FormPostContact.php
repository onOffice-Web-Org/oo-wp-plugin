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
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\Form\FormPostConfiguration;
use onOffice\WPlugin\Form\FormPostContactConfiguration;
use onOffice\WPlugin\Form\FormPostContactConfigurationDefault;
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
	/** @var FormPostContactConfiguration */
	private $_pFormPostContactConfiguration = null;


	/**
	 *
	 * @param FormPostConfiguration $pFormPostConfiguration
	 * @param FormPostContactConfiguration $pFormPostContactConfiguration
	 *
	 */

	public function __construct(FormPostConfiguration $pFormPostConfiguration = null,
		FormPostContactConfiguration $pFormPostContactConfiguration = null)
	{
		$this->_pFormPostContactConfiguration =
			$pFormPostContactConfiguration ?? new FormPostContactConfigurationDefault();

		parent::__construct($pFormPostConfiguration);
	}


	/**
	 *
	 * @param DataFormConfiguration $pFormConfig
	 * @param int $formNo
	 * @return void
	 *
	 */

	protected function analyseFormContentByPrefix(FormData $pFormData)
	{
		$pFormConfig = $pFormData->getDataFormConfiguration();
		$recipient = $pFormConfig->getRecipient();
		$subject = $pFormConfig->getSubject();
		$missingFields = $pFormData->getMissingFields();
		$pFormData->setStatus(FormPost::MESSAGE_ERROR);

		if ($missingFields !== []) {
			$pFormData->setStatus(FormPost::MESSAGE_REQUIRED_FIELDS_MISSING);
			return;
		}

		$responseNewAddress = true;

		if ($pFormConfig->getCreateAddress()) {
			$responseNewAddress = $this->createAddress($pFormData);
		}

		$response = $this->sendContactRequest($pFormData, $recipient, $subject) &&
			$responseNewAddress;

		if (true === $response) {
			$pFormData->setStatus(FormPost::MESSAGE_SUCCESS);
		}
	}


	/**
	 *
	 * @param FormData $pFormData
	 * @return bool
	 *
	 */

	private function createAddress(FormData $pFormData): bool
	{
		$pFormConfig = $pFormData->getDataFormConfiguration();
		$checkDuplicate = $pFormConfig->getCheckDuplicateOnCreateAddress();
		$responseNewAddress = $this->createOrCompleteAddress($pFormData, $checkDuplicate);

		if ($responseNewAddress === false) {
			return false;
		}

		if (!$this->_pFormPostContactConfiguration->getNewsletterAccepted()) {
			// No subscription for newsletter, which is ok
			return true;
		}

		$pSDKWrapper = $this->_pFormPostContactConfiguration->getSDKWrapper();
		$pAPIClientAction = new APIClientActionGeneric
			($pSDKWrapper, onOfficeSDK::ACTION_ID_DO, 'registerNewsletter');
		$pAPIClientAction->setParameters(['register' => true]);
		$pAPIClientAction->setResourceId($responseNewAddress);
		$pAPIClientAction->addRequestToQueue()->sendRequests();

		return $pAPIClientAction->getResultStatus();
	}


	/**
	 *
	 * @param FormData $pFormData
	 * @return bool
	 *
	 */

	private function sendContactRequest(FormData $pFormData, $recipient = null, $subject = null):
		bool
	{
		$values = $pFormData->getValues();
		$requestParams = [
			'addressdata' => $pFormData->getAddressData(),
			'estateid' => $values['Id'] ?? null,
			'message' => $values['message'] ?? null,
			'subject' => $subject,
			'referrer' => $this->_pFormPostContactConfiguration->getReferrer(),
			'formtype' => $pFormData->getFormtype(),
		];

		if (null != $recipient) {
			$requestParams['recipient'] = $recipient;
		}

		$pSDKWrapper = $this->_pFormPostContactConfiguration->getSDKWrapper();

		$pAPIClientAction = new APIClientActionGeneric
			($pSDKWrapper, onOfficeSDK::ACTION_ID_DO, 'contactaddress');
		$pAPIClientAction->setParameters($requestParams);
		$pAPIClientAction->addRequestToQueue()->sendRequests();
		return $pAPIClientAction->getResultStatus();
	}
}