<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

declare (strict_types=1);

namespace onOffice\WPlugin\Form;

use onOffice\WPlugin\Vendor\DI\ContainerBuilder;
use onOffice\WPlugin\Vendor\DI\DependencyException;
use onOffice\WPlugin\Vendor\DI\NotFoundException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\Cache\CacheHandler;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\Field\Collection\FieldLoaderGeneric;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilder;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\FormData;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Language;

/**
 *
 */
class FormAddressCreator
{
	/** @var SDKWrapper */
	private $_pSDKWrapper;

	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort;

	/**
	 * @param SDKWrapper $pSDKWrapper
	 * @param FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort
	 */
	public function __construct(
		SDKWrapper $pSDKWrapper,
		FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort)
	{
		$this->_pSDKWrapper = $pSDKWrapper;
		$this->_pFieldsCollectionBuilderShort = $pFieldsCollectionBuilderShort;
	}

	/**
	 * @param FormData $pFormData
	 * @param bool $mergeExisting
	 * @param array $contactType
	 * @param int|null $estateId
	 * @param array $supervisor supervisor to set, overriding the one derived from $estateId;
	 *        empty, or ['id' => string, 'username' => string] as returned by getUserByEmail()
	 * @return int the new (or updated) address ID
	 * @throws ApiClientException
	 * @throws UnknownFieldException
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function createOrCompleteAddress(
		FormData $pFormData, bool $mergeExisting = false, array $contactType = [], int $estateId = null,
		array $supervisor = []): int
	{
		$requestParams = $this->getAddressDataForApiCall($pFormData);
		$requestParams['checkDuplicate'] = $mergeExisting;
		if ($mergeExisting) {
			$requestParams['noOverrideByDuplicate'] = true;
		}

		$addressFields = $this->getAddressFields();
		if(!isset($addressFields['ArtDaten'])){
			//Field is not active
			$contactType = [];
		}

		if (!empty($contactType)) {
			$requestParams['ArtDaten'] = $contactType;
		}
		if (isset($requestParams['gdprcheckbox']) && $requestParams['gdprcheckbox']){
			$requestParams['DSGVOStatus'] = "speicherungzugestimmt";
		}
		unset($requestParams['gdprcheckbox']);
		if ( key_exists( 'newsletter', $requestParams ) ) {
			unset( $requestParams['newsletter'] );
		}
		if ($supervisor !== []) {
			// An explicitly resolved supervisor - the advisor whose detail page the form sits on -
			// takes precedence over the one inherited from the estate the form was embedded on.
			$requestParams['Benutzer'] = $supervisor['username'];
		} elseif (!empty($estateId)) {
			$userName = $this->getSupervisorUsernameByEstateId($estateId);
			if (!empty($userName)) {
				$requestParams['Benutzer'] = $userName;
			}
		}

		$pApiClientAction = new APIClientActionGeneric
			($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_CREATE, 'address');
		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->addRequestToQueue()->sendRequests();
		$result = $pApiClientAction->getResultRecords();
		$addressId = (int)$result[0]['id'];

		if ($addressId > 0) {
			$this->assignAddressSupervisor($addressId, $supervisor['id'] ?? '');
			return $addressId;
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- ApiClientException is for internal API error handling
		throw new ApiClientException($pApiClientAction);
	}


	/**
	 * Sets the advisor as supervisor ("Betreuer") of the address via relation.
	 *
	 * The 'Benutzer' field set above only ever reaches addresses that are really created: with the
	 * duplicate check on, the create request carries noOverrideByDuplicate, so an address matched
	 * as a duplicate keeps the supervisor it already has. The relation works on an existing record
	 * too, which is the whole point of the option.
	 *
	 * Non-fatal by design, like FormPostOwner::assignEstateContactBroker(): a supervisor that
	 * cannot be assigned must not stop the address, the estate or the email from being created.
	 *
	 * @param int $addressId
	 * @param string $supervisorUserId
	 */
	private function assignAddressSupervisor(int $addressId, string $supervisorUserId): void
	{
		if ($supervisorUserId === '') {
			return;
		}

		// address = parent record, user = child record - see the constant in onOfficeSDK
		$pApiClientAction = new APIClientActionGeneric
			($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_CREATE, 'relation');
		$pApiClientAction->setParameters([
			'relationtype' => onOfficeSDK::RELATION_TYPE_ADDRESS_USER_OFFICER,
			'parentid' => $addressId,
			'childid' => $supervisorUserId,
		]);

		try {
			$pApiClientAction->addRequestToQueue();
			$this->_pSDKWrapper->sendRequests();

			if (!$pApiClientAction->getResultStatus()) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- diagnostics for a silently skipped optional step
				error_log('onOffice: could not assign the supervisor relation for address '
					. $addressId);
			}
		} catch (ApiClientException $pException) {
			// ApiClientException doesn't set a message via getMessage() (its constructor doesn't
			// pass one to the parent) - the actual API error details are only in __toString().
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- diagnostics for a silently skipped optional step
			error_log('onOffice: could not assign the supervisor relation for address '
				. $addressId . ': ' . $pException);
		}
	}


	/**
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function getAddressFields(): array
	{
		$pFieldsCollection = new FieldsCollection();
		$this->_pFieldsCollectionBuilderShort->addFieldsAddressEstate($pFieldsCollection);
		return $pFieldsCollection->getFieldsByModule('address');
	}

	/**
	 * @param FormData $pFormData
	 * @return array
	 *
	 * @throws UnknownFieldException
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function getAddressDataForApiCall(FormData $pFormData): array
	{
		$fieldNameAliases = [
			'Telefon1' => 'phone',
			'Email' => 'email',
			'Telefax1' => 'fax',
		];

		$addressData = [];
		$pFieldsCollection = new FieldsCollection();
		$this->_pFieldsCollectionBuilderShort->addFieldsAddressEstate( $pFieldsCollection )
		                                     ->addFieldsSearchCriteria( $pFieldsCollection )
		                                     ->addFieldsFormFrontend( $pFieldsCollection );
		$addressFields = $pFormData->getAddressData($pFieldsCollection);

		foreach ($addressFields as $inputName => $value) {
			$pField = $pFieldsCollection->getFieldByModuleAndName(onOfficeSDK::MODULE_ADDRESS, $inputName);
			$fieldNameAliased = $fieldNameAliases[$inputName] ?? $inputName;
			$addressData[$fieldNameAliased] = $value;

			if ($pField->getType() === FieldTypes::FIELD_TYPE_MULTISELECT && !is_array($value)) {
				$addressData[$fieldNameAliased] = [$value];
			}
		}

		return $addressData;
	}

	/**
	 * @param FormData $pFormData
	 * @return array
	 * @throws UnknownFieldException
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function getAddressDataForEmail(FormData $pFormData): array
	{
		$pFieldsCollection = new FieldsCollection();
		$this->_pFieldsCollectionBuilderShort->addFieldsAddressEstate($pFieldsCollection);
		$addressData = [];
		$addressFields = $pFormData->getAddressData($pFieldsCollection);

		foreach ($addressFields as $inputName => $value) {
			$pField = $pFieldsCollection->getFieldByModuleAndName(onOfficeSDK::MODULE_ADDRESS, $inputName);

			switch ($pField->getType()) {
				case FieldTypes::FIELD_TYPE_SINGLESELECT:
					$addressData[$pField->getLabel()] = array_key_exists($value, $pField->getPermittedvalues()) ? $pField->getPermittedvalues()[$value] : $value;
					break;
				case FieldTypes::FIELD_TYPE_MULTISELECT:
					if (!is_array($value)) {
						$addressData[$pField->getLabel()] = array_keys($pField->getPermittedvalues(), $value)[0] ?? $value;
					} else {
						$tmpMsValues = [];
						foreach ($value as $val) {
							$tmpMsValues []= array_keys(array_flip($pField->getPermittedvalues()), $val)[0] ?? $val;
						}
						$addressData[$pField->getLabel()] = implode(', ', $tmpMsValues);
					}
					break;
				default:
					$addressData[$pField->getLabel()] = $value;
					break;
			}
		}
		return $addressData;
	}

	/**
	 * @param int $estateId
	 * @return string
	 * @throws ApiClientException
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function getSupervisorUsernameByEstateId(int $estateId): string
	{
		$requestParams = [
			'filter' => ['Id' => [['op' => '=', 'val' => $estateId]]],
			'data' => ['benutzer'],
		];

		$pApiClientAction = new APIClientActionGeneric
			($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'estate');

		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->addRequestToQueue();
		$this->_pSDKWrapper->sendRequests();
		$result = $pApiClientAction->getResultRecords();

		if (!empty($result) && isset($result[0]["elements"]["benutzer"])) {
			$userId = $result[0]["elements"]["benutzer"];
			return $this->getUserNameById($userId);
		} else {
			return '';
		}
	}

	/**
	 * Reads the one user matching $filter.
	 *
	 * Deliberately the 'user' resource and not the 'users' list: 'users' returns only a subset of
	 * an account's users - in the reference account one of seven - so a supervisor that exists
	 * would silently not be found. 'user' also filters server-side instead of pulling the whole
	 * list per form submission.
	 *
	 * @param array $filter as the API's 'filter' parameter: key field, value filter expressions
	 * @return array empty, or ['id' => string, 'username' => string]
	 * @throws ApiClientException
	 */
	private function readUser(array $filter): array
	{
		$pApiClientAction = new APIClientActionGeneric
			($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'user');
		$pApiClientAction->setParameters([
			// this resource names the two representations differently than 'users' does: 'Nr' is
			// the user id the officer relations want, 'Name' the user name the address field wants
			'data' => ['Nr', 'Name'],
			'filter' => $filter,
			'listlimit' => 1,
		]);
		$pApiClientAction->addRequestToQueue()->sendRequests();

		$records = $pApiClientAction->getResultRecords();

		if (empty($records[0]['elements']['Name'])) {
			return [];
		}

		return [
			'id' => (string)$records[0]['id'],
			'username' => (string)$records[0]['elements']['Name'],
		];
	}

	/**
	 * @param string $userId
	 * @return string
	 * @throws ApiClientException
	 */
	private function getUserNameById(string $userId): string
	{
		return $this->readUser(['Nr' => [['op' => '=', 'val' => $userId]]])['username'] ?? '';
	}

	/**
	 * Resolves the onOffice user whose account carries this address record ("adrId", the linked
	 * user address under Settings > User).
	 *
	 * This is the link onOffice itself maintains between a user and the address that represents
	 * them, so it answers "which user is this address?" authoritatively - unlike getUserByEmail(),
	 * which only infers it. Callers treat [] as "no user has this address linked".
	 *
	 * @param int $addressId
	 * @return array empty, or ['id' => string, 'username' => string]
	 * @throws ApiClientException
	 */
	public function getUserByAddressId(int $addressId): array
	{
		if ($addressId <= 0) {
			return [];
		}

		// the field is really called 'adrId:adressen.ID' - the API rejects the plain 'adrId'
		// with "Unknown field", and in 'data' it is silently dropped instead
		return $this->readUser(['adrId:adressen.ID' => [['op' => '=', 'val' => $addressId]]]);
	}

	/**
	 * Resolves an onOffice user by the email of their address record.
	 *
	 * Only an inference - address and user record are maintained separately - so it is the
	 * fallback behind getUserByAddressId(). Callers treat [] as "leave the supervisor alone"
	 * rather than as an error.
	 *
	 * @param string $email
	 * @return array empty, or ['id' => string, 'username' => string]
	 * @throws ApiClientException
	 */
	public function getUserByEmail(string $email): array
	{
		if ($email === '') {
			return [];
		}

		return $this->readUser(['email' => [['op' => '=', 'val' => $email]]]);
	}

	/**
	 * @param FormData $pFormData
	 * @param int $addressId
	 * @return string
	 * @throws ApiClientException
	 * @throws UnknownFieldException
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function handleLogicMessageDuplicateAddressData(FormData $pFormData, int $addressId): string
	{
		$requestParams = $this->getAddressDataForReadAddressApiCall($pFormData);
		if (isset($requestParams['gdprcheckbox']) && $requestParams['gdprcheckbox']){
			$requestParams['DSGVOStatus'] = onOfficeSDK::MODULE_ADDRESS;
		}
		if (key_exists('newsletter', $requestParams) || key_exists('gdprcheckbox', $requestParams)) {
			unset($requestParams['newsletter']);
			unset($requestParams['gdprcheckbox']);
		}

		$oldAddressData = $this->getDataAddressById($addressId, $requestParams);
		unset($oldAddressData['id']);

		return $this->generateMessageDuplicateAddressData($oldAddressData);
	}

	/**
	 * @param FormData $pFormData
	 * @param int $addressId
	 * @param mixed $latestAddressIdOnEnterPrise
	 * @return string
	 * @throws ApiClientException
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownFieldException
	 */
	public function getMessageDuplicateAddressData(FormData $pFormData, int $addressId, $latestAddressIdOnEnterPrise): string
	{
		if ($addressId > $latestAddressIdOnEnterPrise || is_null($latestAddressIdOnEnterPrise)) {
			return '';
		}

		return $this->handleLogicMessageDuplicateAddressData($pFormData, $addressId);
	}


	/**
	 * @return mixed
	 * @throws ApiClientException
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function getLatestAddressIdInOnOfficeEnterprise()
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();
		$pContainer->get(CacheHandler::class)->clear();

		$requestParams = [
			'sortby' => 'KdNr',
			'sortorder' => 'DESC',
			'listlimit' => 1
		];
		$pApiClientAction = new APIClientActionGeneric
			($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'address');
		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->addRequestToQueue()->sendRequests();
		$result = $pApiClientAction->getResultRecords();

		if (empty($result)) {
			return null;
		}

		return $result[0]['id'];
	}

	/**
	 * @param int $addressId
	 * @param array $params
	 * @return array
	 * @throws ApiClientException
	 */
	private function getDataAddressById(int $addressId, array $params): array
	{
		$requestParams = [
			'data' => array_keys($params),
			'outputlanguage' => Language::getDefault(),
			'formatoutput' => true,
		];

		$pApiClientAction = new APIClientActionGeneric
			($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'address');
		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->setResourceId((string) $addressId);
		$pApiClientAction->addRequestToQueue()->sendRequests();
		$result = $pApiClientAction->getResultRecords();
		
		if (empty($result)) {
			return array();
		}

		return $result[0]['elements'];
	}

	/**
	 * @param array $oldData
	 * @return string
	 */
	private function generateMessageDuplicateAddressData(array $oldData): string
	{
		$pFieldsCollection = new FieldsCollection();
		$this->_pFieldsCollectionBuilderShort->addFieldsAddressEstate($pFieldsCollection);

		$messageDuplicateAddressData = '';
		$guidanceForHandlingSituation = '';
		$oldAddressData = '';

		if (!empty($oldData)) {
			$messageDuplicateAddressData .= "\n\n\n" . __( 'Attention: Duplicate detected! This data is similar to existing data records. Please take a moment to check for duplicates and decide whether updates are required.', 'onoffice-for-wp-websites') . "\n";
			$messageDuplicateAddressData .= "\n" . __( 'Existing data record:', 'onoffice-for-wp-websites' ) . "\n";
			$messageDuplicateAddressData .= "--------------------------------------------------\n";
			foreach ($oldData as $key => $value) {
				$label = $pFieldsCollection->getFieldByKeyUnsafe($key)->getLabel();
				if (is_array($value)) {
					$value = implode(", ", $value);
				}
				$oldAddressData .= $label . ': ' . $value . "\n";
			}
			$guidanceForHandlingSituation .= "\n" . __( 'How to search and update duplicates in onOffice enterprise:', 'onoffice-for-wp-websites') . "\n";
			$guidanceForHandlingSituation .=  __( 'https://de.enterprisehilfe.onoffice.com/help_entries/dubletten/?lang=en', 'onoffice-for-wp-websites') . "\n";
		}
		$messageDuplicateAddressData .= $oldAddressData . $guidanceForHandlingSituation;

		return $messageDuplicateAddressData;
	}

	/**
	 * @param FormData $pFormData
	 * @return array
	 *
	 * @throws UnknownFieldException
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function getAddressDataForReadAddressApiCall(FormData $pFormData): array
	{
		$addressData = [];
		$pFieldsCollection = new FieldsCollection();
		$this->_pFieldsCollectionBuilderShort->addFieldsAddressEstate( $pFieldsCollection )
		                                     ->addFieldsSearchCriteria( $pFieldsCollection )
		                                     ->addFieldsFormFrontend( $pFieldsCollection );
		$addressFields = $pFormData->getDataFormConfiguration()->getInputs();

		foreach ($addressFields as $fieldName => $module) {
			try {
				$pField = $pFieldsCollection->getFieldByModuleAndName($module, $fieldName);
				if ($pField->getModule() === onOfficeSDK::MODULE_ADDRESS) {
					$addressData[$fieldName] = $module;
				}
			} catch (UnknownFieldException $e) {
				continue; // Skip the field if is not found e.g., not active in enterprise
			}
		}

		return $addressData;
	}

	/**
	 * @param DataFormConfiguration $pFormConfig
	 * @param int $addressId
	 * @param int|null $estateId
	 *
	 * @return void
	 */
	public function createAgentsLog(DataFormConfiguration $pFormConfig, int $addressId, int $estateId = null)
	{
		if (empty($pFormConfig->getActionKind()) && empty($pFormConfig->getActionType()) && empty($pFormConfig->getCharacteristic()) && empty($pFormConfig->getRemark())) {
			return;
		}
		$requestParams = [
			'addressids' => [$addressId],
			'actionkind' => $pFormConfig->getActionKind() ?: null,
			'actiontype' => $pFormConfig->getActionType() ?: null,
			'origincontact' => $pFormConfig->getOriginContact() ?: null,
			'features' => !empty($pFormConfig->getCharacteristic()) ? explode(',', $pFormConfig->getCharacteristic()) : null,
			'note' => $pFormConfig->getRemark(),
		];

		if (!empty($estateId)) {
			$requestParams['estateid'] = $estateId;
			$requestParams['advisorylevel'] = $pFormConfig->getAdvisorylevel() ?: null;
		}

		$pApiClientAction = new APIClientActionGeneric
			($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_CREATE, 'agentslog');
		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->addRequestToQueue()->sendRequests();
	}

	/**
	 * @param DataFormConfiguration $pFormConfig
	 * @param int $addressId
	 * @param int|null $estateId
	 *
	 * @return void
	 */
	public function createTask(DataFormConfiguration $pFormConfig, int $addressId, int $estateId = null)
	{
		if (empty($pFormConfig->getTaskType()) || empty($pFormConfig->getTaskStatus())) {
			return;
		}

		$requestParams = [
			'data' => [
				'Prio' => $pFormConfig->getTaskPriority(),
				'Verantwortung' => $pFormConfig->getTaskResponsibility(),
				'Art' => $pFormConfig->getTaskType(),
				'Status' => $pFormConfig->getTaskStatus(),
				'Bearbeiter' => $pFormConfig->getTaskProcessor(),
				'Betreff' => $pFormConfig->getTaskSubject(),
				'Aufgabe' => $pFormConfig->getTaskDescription()
			],
			'relatedAddressId' => $addressId,
		];

		if (!empty($estateId)) {
			$requestParams['relatedEstateId'] = $estateId;
		}

		$pApiClientAction = new APIClientActionGeneric
			($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_CREATE, 'task');
		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->addRequestToQueue()->sendRequests();
	}
}
