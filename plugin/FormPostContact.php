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

use Exception;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\Field\Collection\FieldsCollectionConfiguratorForm;
use onOffice\WPlugin\Field\SearchcriteriaFields;
use onOffice\WPlugin\Form\FormPostConfiguration;
use onOffice\WPlugin\Form\FormPostContactConfiguration;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Factory\AddressListFactory;
use onOffice\WPlugin\DataView\DataListViewAddress;
use onOffice\WPlugin\DataView\DataAddressDetailViewHandler;
use function sanitize_text_field;
use function home_url;

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
	/**	 */
	const PORTALFILTER_IDENTIFIER = '[onOffice-WP]';

	/** @var FormPostContactConfiguration */
	private $_pFormPostContactConfiguration = null;

	/** @var string */
	private $_messageDuplicateAddressData = '';

	/** @var AddressListFactory */
	private $_pAddressDetailFactory;
	/**
	 *
	 * @param FormPostConfiguration $pFormPostConfiguration
	 * @param FormPostContactConfiguration $pFormPostContactConfiguration
	 * @param SearchcriteriaFields $pSearchcriteriaFields
	 * @param FieldsCollectionConfiguratorForm $pFieldsCollectionConfiguratorForm
	 */

	public function __construct(
		FormPostConfiguration $pFormPostConfiguration,
		FormPostContactConfiguration $pFormPostContactConfiguration,
		SearchcriteriaFields $pSearchcriteriaFields,
		FieldsCollectionConfiguratorForm $pFieldsCollectionConfiguratorForm,
		AddressListFactory $pAddressDetailFactory)
	{
		$this->_pFormPostContactConfiguration = $pFormPostContactConfiguration;
		$this->_pAddressDetailFactory = $pAddressDetailFactory;

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
		$subject = $this->generateDefaultEmailSubject($pFormData->getFormtype(), $this->_pFormPostContactConfiguration->getNewsletterAccepted());
		$pWPQuery = $this->_pFormPostContactConfiguration->getWPQueryWrapper()->getWPQuery();
		$estateId = $pWPQuery->get('estate_id', null);
		if (!empty($pFormConfig->getSubject())) {
			$subject = $this->generateCustomEmailSubject($pFormConfig->getSubject(), $pFormData->getFieldLabelsForEmailSubject($this->getFieldsCollection()), $estateId, $pFormConfig->getInputs());
		}
		try {
			if ($pFormConfig->getCreateAddress()) {
				$this->createAddress($pFormData);
			}
		} finally {
			$this->sendContactRequest($pFormData, $recipient, $subject);
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
		return ['Id' => onOfficeSDK::MODULE_ESTATE] + parent::getAllowedPostVars($pFormConfig);
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
	 * @throws Field\UnknownFieldException
	 */

	private function createAddress(FormData $pFormData)
	{
		$pFormConfig = $pFormData->getDataFormConfiguration();
		$checkDuplicate = $pFormConfig->getCheckDuplicateOnCreateAddress();
		$writeActivity = $pFormConfig->getWriteActivity();
		$contactType = $pFormConfig->getContactType();
		$enableCreateTask = $pFormConfig->getEnableCreateTask();
		$pWPQuery = $this->_pFormPostContactConfiguration->getWPQueryWrapper()->getWPQuery();
		$estateId = $pWPQuery->get('estate_id', null);
		$latestAddressIdOnEnterPrise = null;
		if ($checkDuplicate) {
			$latestAddressIdOnEnterPrise = $this->_pFormPostContactConfiguration->getFormAddressCreator()->getLatestAddressIdInOnOfficeEnterprise();
		}
		$addressId = $this->_pFormPostContactConfiguration->getFormAddressCreator()
			->createOrCompleteAddress($pFormData, $checkDuplicate, $contactType, $estateId);
		$this->_messageDuplicateAddressData = $this->_pFormPostContactConfiguration->getFormAddressCreator()
			->getMessageDuplicateAddressData($pFormData, $addressId, $latestAddressIdOnEnterPrise);
		if ($writeActivity) {
			$this->_pFormPostContactConfiguration->getFormAddressCreator()->createAgentsLog($pFormConfig, $addressId, $estateId);
		}

		if ($enableCreateTask) {
			$this->_pFormPostContactConfiguration->getFormAddressCreator()->createTask($pFormConfig, $addressId, $estateId);
		}

		if (!$this->_pFormPostContactConfiguration->getNewsletterAccepted()) {
			// No subscription for newsletter, which is ok
			return;
		}

		$pSDKWrapper = $this->_pFormPostContactConfiguration->getSDKWrapper();
		$pAPIClientAction = new APIClientActionGeneric
			($pSDKWrapper, onOfficeSDK::ACTION_ID_DO, 'registerNewsletter');
		$pAPIClientAction->setParameters(['register' => true]);
		$pAPIClientAction->setResourceId($addressId);
		$pAPIClientAction->addRequestToQueue()->sendRequests();

		if (!$pAPIClientAction->getResultStatus()) {
			throw new ApiClientException($pAPIClientAction);
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
		$pWPQuery = $this->_pFormPostContactConfiguration->getWPQueryWrapper()->getWPQuery();
		$pWPWrapper = $this->_pFormPostContactConfiguration->getWPWrapper();
		$addressData = $pFormData->getAddressData($this->getFieldsCollection());
		$message = $values['message'] ?? '';
		$requestParams = [
			'addressdata' => $addressData,
			'estateid' => $values['Id'] ?? $pWPQuery->get('estate_id', null),
			'message' => $message . $this->_messageDuplicateAddressData,
			'subject' => sanitize_text_field($subject.' '.self::PORTALFILTER_IDENTIFIER),
			'referrer' => $this->_pFormPostContactConfiguration->getReferrer(),
			'formtype' => $pFormData->getFormtype(),
			'estatedata' => ["objekttitel", "ort", "plz", "land"],
			'estateurl' => home_url($pWPWrapper->getRequest()),
		];
		if (isset($addressData['ArtDaten']) && !empty($contactType)) {
			$requestParams['addressdata']['ArtDaten'] = $contactType;
		}
		if (isset($addressData['newsletter'])) {
			$requestParams['addressdata']['newsletter_aktiv'] = $this->_pFormPostContactConfiguration
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
		$pDataAddressDetailViewHandler = new DataAddressDetailViewHandler();
		$pAddressDataView = $pDataAddressDetailViewHandler->getAddressDetailView();

		$referrerURL = get_site_url().$requestParams['referrer'];

		if(intval($pAddressDataView->getPageId()) != 0 && (
			str_starts_with($referrerURL, get_permalink($pAddressDataView->getPageId()))
			|| str_starts_with($referrerURL, get_permalink($pAddressDataView->getPageId()))))
		{
			//if form posted on address detail page, change recipient
			$addressId = str_replace(get_permalink($pAddressDataView->getPageId()),"",$referrerURL);
			$addressId = preg_replace('/-.*|\/.*|\D+/', '', $addressId);

			$defaultFields = ['defaultemail' => 'Email'];
			$addressList = $this->_pAddressDetailFactory->create($pAddressDataView);
			$addressList->loadAddressesById([$addressId], $defaultFields);
			$requestParams['recipient'] = $addressList->getCurrentAddress()[$addressId]['Email'];
		}

		$pSDKWrapper = $this->_pFormPostContactConfiguration->getSDKWrapper();
		$pAPIClientAction = new APIClientActionGeneric
			($pSDKWrapper, onOfficeSDK::ACTION_ID_DO, 'contactaddress');
		$pAPIClientAction->setParameters($requestParams);
		$pAPIClientAction->addRequestToQueue()->sendRequests();
		if (!$pAPIClientAction->getResultStatus()) {
			throw new ApiClientException($pAPIClientAction);
		}
	}
}
