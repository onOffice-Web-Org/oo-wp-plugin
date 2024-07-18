<?php

/**
 *
 *    Copyright (C) 2016-2019 onOffice GmbH
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
use onOffice\WPlugin\Field\Collection\FieldsCollectionConfiguratorForm;
use onOffice\WPlugin\Field\SearchcriteriaFields;
use onOffice\WPlugin\Form\FormPostConfiguration;
use onOffice\WPlugin\Form\FormPostAddressCompletionConfiguration;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldTypes;
use function sanitize_text_field;
use function home_url;

/**
 *
 * Interest/Contact form
 *
 *  - send "Id" value if in estate-context
 *
 */

class FormPostAddressCompletion
	extends FormPost
{
	/**	 */
	const PORTALFILTER_IDENTIFIER = '[onOffice-WP]';

	/** @var FormPostAddressCompletionConfiguration */
	private $_pFormPostAddressCompletionConfiguration = null;

	/**
	 *
	 * @param FormPostConfiguration $pFormPostConfiguration
	 * @param FormPostAddressCompletionConfiguration $pFormPostAddressCompletionConfiguration
	 * @param SearchcriteriaFields $pSearchcriteriaFields
	 * @param FieldsCollectionConfiguratorForm $pFieldsCollectionConfiguratorForm
	 */

	public function __construct(
		FormPostConfiguration $pFormPostConfiguration,
		FormPostAddressCompletionConfiguration $pFormPostAddressCompletionConfiguration,
		SearchcriteriaFields $pSearchcriteriaFields,
		FieldsCollectionConfiguratorForm $pFieldsCollectionConfiguratorForm)
	{
		$this->_pFormPostAddressCompletionConfiguration = $pFormPostAddressCompletionConfiguration;

		parent::__construct($pFormPostConfiguration, $pSearchcriteriaFields, $pFieldsCollectionConfiguratorForm);
	}


	/**
	 * @param  FormData  $pFormData
	 *
	 * @throws ApiClientException
	 * @throws DependencyException
	 * @throws NotFoundException
	 */

	protected function analyseFormContentByPrefix(FormData $pFormData)
	{
		$pFormConfig = $pFormData->getDataFormConfiguration();
		$recipient = $pFormConfig->getRecipientByUserSelection();
		$subject = $pFormConfig->getSubject();

		try {
			$this->createAddress($pFormData);
		} finally {
			$this->sendContactRequest($pFormData, $recipient, $subject);
		}
	}

	/**
	 * @return array
	 */

	protected function expandFieldsCollection()
	{
		$pField = new Field('Id', onOfficeSDK::MODULE_ESTATE);
		$pField->setType(FieldTypes::FIELD_TYPE_INTEGER);

		return [$pField];
	}

	/**
	 *
	 * @param FormData $pFormData
	 * @return void
	 * @throws ApiClientException
	 */

	private function createAddress(FormData $pFormData)
	{
		$pFormConfig = $pFormData->getDataFormConfiguration();
		$contactType = $pFormConfig->getContactType();
		$pWPQuery = $this->_pFormPostAddressCompletionConfiguration->getWPQueryWrapper()->getWPQuery();
		$estateId = $pWPQuery->get('estate_id', null);
		$latestAddressIdOnEnterPrise = null;

		$addressId = $this->_pFormPostAddressCompletionConfiguration->getFormAddressCreator()
			->createOrCompleteAddress($pFormData, false, $contactType, $estateId);
echo "<pre>";
var_dump($addressId);
die();
		if (!$this->_pFormPostAddressCompletionConfiguration->getNewsletterAccepted()) {
			// No subscription for newsletter, which is ok
			return;
		}
	}

	/**
	 *
	 * @param FormData $pFormData
	 * @param string $recipient
	 * @param string $subject
	 * @throws ApiClientException
	 * @throws DependencyException
	 * @throws NotFoundException
	 */

	private function sendContactRequest(FormData $pFormData, string $recipient = '', $subject = null)
	{
		$pFormConfig = $pFormData->getDataFormConfiguration();
		$contactType = $pFormConfig->getContactType();
		$values = $pFormData->getValues();
		$pWPQuery = $this->_pFormPostAddressCompletionConfiguration->getWPQueryWrapper()->getWPQuery();
		$pWPWrapper = $this->_pFormPostAddressCompletionConfiguration->getWPWrapper();
		$addressData = $pFormData->getAddressData($this->getFieldsCollection());
		$message = $values['message'] ?? '';
		$requestParams = [
			'addressdata' => $addressData,
			'estateid' => $values['Id'] ?? $pWPQuery->get('estate_id', null),
			'message' => $message,
			'subject' => sanitize_text_field($subject.' '.self::PORTALFILTER_IDENTIFIER),
			'referrer' => $this->_pFormPostAddressCompletionConfiguration->getReferrer(),
			'formtype' => $pFormData->getFormtype(),
			'estatedata' => ["objekttitel", "ort", "plz", "land"],
			'estateurl' => home_url($pWPWrapper->getRequest()),
		];
		if (isset($addressData['ArtDaten']) && !empty($contactType)) {
			$requestParams['addressdata']['ArtDaten'] = $contactType;
		}
		if (isset($addressData['newsletter'])) {
			$requestParams['addressdata']['newsletter_aktiv'] = $this->_pFormPostAddressCompletionConfiguration
				->getNewsletterAccepted();
		}
		if (isset($addressData['gdprcheckbox']) && $addressData['gdprcheckbox']){
			$requestParams['addressdata']['DSGVOStatus'] = "speicherungzugestimmt";
		}
		unset($requestParams['addressdata']['gdprcheckbox']);
		unset($requestParams['addressdata']['newsletter']);
		if ($recipient !== '') {
			$requestParams['recipient'] = $recipient;
		}
		$pSDKWrapper = $this->_pFormPostAddressCompletionConfiguration->getSDKWrapper();
		$pAPIClientAction = new APIClientActionGeneric
			($pSDKWrapper, onOfficeSDK::ACTION_ID_DO, 'contactaddress');
		$pAPIClientAction->setParameters($requestParams);
		$pAPIClientAction->addRequestToQueue()->sendRequests();

		if (!$pAPIClientAction->getResultStatus()) {
			throw new ApiClientException($pAPIClientAction);
		}
	}
}
