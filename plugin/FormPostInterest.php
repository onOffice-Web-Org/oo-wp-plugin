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

use DI\DependencyException;
use DI\NotFoundException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationInterest;
use onOffice\WPlugin\Field\Collection\FieldsCollectionConfiguratorForm;
use onOffice\WPlugin\Field\SearchcriteriaFields;
use onOffice\WPlugin\Form\FormPostConfiguration;
use onOffice\WPlugin\Form\FormPostInterestConfiguration;
use function sanitize_email;
use function sanitize_text_field;

/**
 *
 * Applicant form. A person registers itself and leaves his/her search criteria
 *
 */

class FormPostInterest
	extends FormPost
{
	/** */
	const PORTALFILTER_IDENTIFIER = '[onOffice-WP]';

	/** @var FormPostInterestConfiguration */
	private $_pFormPostInterestConfiguration = null;

	/** @var string */
	private $_messageDuplicateAddressData = '';

	/**
	 *
	 * @param FormPostConfiguration $pFormPostConfiguration
	 * @param FormPostInterestConfiguration $pFormPostInterestConfiguration
	 * @param SearchcriteriaFields $pSearchcriteriaFields
	 * @param FieldsCollectionConfiguratorForm $pFieldsCollectionConfiguratorForm
	 */

	public function __construct(
		FormPostConfiguration $pFormPostConfiguration,
		FormPostInterestConfiguration $pFormPostInterestConfiguration,
		SearchcriteriaFields $pSearchcriteriaFields,
		FieldsCollectionConfiguratorForm $pFieldsCollectionConfiguratorForm)
	{
		parent::__construct($pFormPostConfiguration, $pSearchcriteriaFields, $pFieldsCollectionConfiguratorForm);
		$this->_pFormPostInterestConfiguration = $pFormPostInterestConfiguration;
	}

	/**
	 * @param FormData $pFormData
	 * @throws ApiClientException
	 * @throws DependencyException
	 * @throws Field\UnknownFieldException
	 * @throws NotFoundException
	 */
	protected function analyseFormContentByPrefix(FormData $pFormData)
	{
		/* @var $pFormConfiguration DataFormConfigurationInterest */
		$pFormConfiguration = $pFormData->getDataFormConfiguration();
		$recipient = $pFormConfiguration->getRecipientByUserSelection();
		$pWPQuery = $this->_pFormPostInterestConfiguration->getWPQueryWrapper()->getWPQuery();
		$estateId = $pWPQuery->get('estate_id', null);
		$subject = $this->generateDefaultEmailSubject($pFormData->getFormtype(), $this->_pFormPostInterestConfiguration->getNewsletterAccepted());
		if (!empty($pFormConfiguration->getSubject())) {
			$subject = $this->generateCustomEmailSubject($pFormConfiguration->getSubject(), $pFormData->getFieldLabelsForEmailSubject($this->getFieldsCollection()), $estateId, $pFormConfiguration->getInputs());
		}

		try {
			if ( $pFormConfiguration->getCreateInterest() ) {
				$checkDuplicate = $pFormConfiguration->getCheckDuplicateOnCreateAddress();
						$contactType = $pFormConfiguration->getContactType();
				$writeActivity = $pFormConfiguration->getWriteActivity();
				$latestAddressIdOnEnterPrise = null;
				$enableCreateTask = $pFormConfiguration->getEnableCreateTask();
				if ($checkDuplicate) {
					$latestAddressIdOnEnterPrise = $this->_pFormPostInterestConfiguration->getFormAddressCreator()->getLatestAddressIdInOnOfficeEnterprise();
				}
				$addressId = $this->_pFormPostInterestConfiguration->getFormAddressCreator()
						->createOrCompleteAddress($pFormData, $checkDuplicate, $contactType, $estateId);
				$this->_messageDuplicateAddressData = $this->_pFormPostInterestConfiguration->getFormAddressCreator()
						->getMessageDuplicateAddressData($pFormData, $addressId, $latestAddressIdOnEnterPrise);
				$this->createSearchcriteria( $pFormData, $addressId );
				if ($writeActivity) {
					$this->_pFormPostInterestConfiguration->getFormAddressCreator()->createAgentsLog($pFormConfiguration, $addressId, null);
				}
				$this->setNewsletter( $addressId );
				if ($enableCreateTask) {
					$this->_pFormPostInterestConfiguration->getFormAddressCreator()->createTask($pFormConfiguration, $addressId, $estateId);
				}
			}
		} finally {
			if ( $recipient != null ) {
				$this->sendEmail( $pFormData, $recipient, $subject );
			}
		}
	}

	/**
	 *
	 * @param DataFormConfiguration $pFormConfig
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */

	protected function getAllowedPostVars(DataFormConfiguration $pFormConfig): array
	{
		$formFields = parent::getAllowedPostVars($pFormConfig);
		$postvars = $this->_pFormPostInterestConfiguration->getSearchcriteriaFields()
			->getFormFieldsWithRangeFields($formFields);
		return $postvars;
	}


	/**
	 *
	 * @param FormData $pFormData
	 * @return void
	 * @throws ApiClientException
	 * @throws Field\UnknownFieldException
	 */

	private function setNewsletter( $addressId)
	{
		if (!$this->_pFormPostInterestConfiguration->getNewsletterAccepted()) {
			// No subscription for newsletter, which is ok
			return;
		}

		$pSDKWrapper = $this->_pFormPostInterestConfiguration->getSDKWrapper();
		$pAPIClientAction = new APIClientActionGeneric
			($pSDKWrapper, onOfficeSDK::ACTION_ID_DO, 'registerNewsletter');
		$pAPIClientAction->setParameters(['register' => true]);
		$pAPIClientAction->setResourceId($addressId);
		$pAPIClientAction->addRequestToQueue()->sendRequests();

		if (!$pAPIClientAction->getResultStatus()) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception is for internal API error handling, not user-facing output
			throw new ApiClientException($pAPIClientAction);
		}
	}


	/**
	 * @param FormData $pFormData
	 * @param string $recipient
	 * @param string $subject
	 * @throws ApiClientException
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws Field\UnknownFieldException
	 */
	private function sendEmail(FormData $pFormData, string $recipient, $subject = null)
	{
		$values = $pFormData->getValues();
		$listDataInputs = $pFormData->getDataFormConfiguration()->getInputs();
		$filledSearchCriteriaData = $this->_pFormPostInterestConfiguration->getSearchcriteriaFields()
			->getFieldLabelsOfInputs($pFormData->getSearchcriteriaData(), $listDataInputs);
		$searchCriterias = $this->createStringFromInputData($filledSearchCriteriaData);
		$message = $values['message'] ?? '';
		$message .= "\nSuchkriterien des Interessenten:\n".
					"$searchCriterias";
		$addressData = $pFormData->getAddressData( $this->getFieldsCollection() );
		$requestParams = [
			'addressdata' => $addressData,
			'message' => $message . $this->_messageDuplicateAddressData,
			'subject' => sanitize_text_field($subject.' '.self::PORTALFILTER_IDENTIFIER),
			'formtype' => $pFormData->getFormtype(),
			'referrer' => filter_input(INPUT_SERVER, 'REQUEST_URI') ?? '',
			'recipient' => $recipient
		];

		if ($recipient !== '') {
			$requestParams['recipient'] = $recipient;
		}
		if (isset($addressData['newsletter'])) {
			$requestParams['addressdata']['newsletter_aktiv'] = $this->_pFormPostInterestConfiguration
				->getNewsletterAccepted();
		}
		if (isset($addressData['gdprcheckbox']) && $addressData['gdprcheckbox']) {
			$requestParams['addressdata']['DSGVOStatus'] = "speicherungzugestimmt";
		}
		unset($requestParams['addressdata']['gdprcheckbox']);

		$pSDKWrapper = $this->_pFormPostInterestConfiguration->getSDKWrapper();
		$pAPIClientAction = new APIClientActionGeneric
		($pSDKWrapper, onOfficeSDK::ACTION_ID_DO, 'contactaddress');
		$pAPIClientAction->setParameters($requestParams);
		$pAPIClientAction->addRequestToQueue()->sendRequests();
		if (!$pAPIClientAction->getResultStatus()) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception is for internal API error handling, not user-facing output
			throw new ApiClientException($pAPIClientAction);
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
			$data []= $key.': '.$value;
		}

		return implode("\n", $data);
	}

	/**
	 * @param FormData $pFormData
	 * @param int $addressId
	 * @throws ApiClientException
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function createSearchcriteria(FormData $pFormData, int $addressId)
	{
		$searchData = $pFormData->getSearchcriteriaData();
		
		// Clean up German number format for range fields
		foreach ($searchData as $key => $value) {
			if (str_ends_with($key, '__von') || str_ends_with($key, '__bis')) {
				if (is_string($value) && !empty($value)) {
					// Remove dots and commas that are thousand separators (followed by exactly 3 digits)
					$searchData[$key] = preg_replace('/[.,](?=\d{3}(?:\D|$))/', '', $value);
				}
			}
		}

		$requestParams = [
			'data' => $searchData,
			'addressid' => $addressId,
		];

		$pSDKWrapper = $this->_pFormPostInterestConfiguration->getSDKWrapper();
		$pApiClientAction = new APIClientActionGeneric
			($pSDKWrapper, onOfficeSDK::ACTION_ID_CREATE, 'searchcriteria');

		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->addRequestToQueue();
		$pSDKWrapper->sendRequests();

		if (!$pApiClientAction->getResultStatus()) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception is for internal API error handling, not user-facing output
			throw new ApiClientException($pApiClientAction);
		}
	}
}
