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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\FormData;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 */

class FormAddressCreator
{
	/** @var SDKWrapper */
	private $_pSDKWrapper;

	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort;

	/** @var array */
	private $_adressDataWithLabels = [];


	/**
	 *
	 * @param SDKWrapper $pSDKWrapper
	 * @param FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort
	 *
	 */

	public function __construct(
		SDKWrapper $pSDKWrapper,
		FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort)
	{
		$this->_pSDKWrapper = $pSDKWrapper;
		$this->_pFieldsCollectionBuilderShort = $pFieldsCollectionBuilderShort;
	}


	/**
	 *
	 * @param FormData $pFormData
	 * @param bool $mergeExisting
	 * @return int the new (or updated) address ID
	 * @throws ApiClientException
	 * @throws UnknownFieldException
	 *
	 */

	public function createOrCompleteAddress(
		FormData $pFormData, bool $mergeExisting = false): int
	{
		$requestParams = $this->getAddressDataForApiCall($pFormData);
		$requestParams['checkDuplicate'] = $mergeExisting;

		$pApiClientAction = new APIClientActionGeneric
			($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_CREATE, 'address');
		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->addRequestToQueue()->sendRequests();
		$result = $pApiClientAction->getResultRecords();
		$addressId = (int)$result[0]['id'];

		if ($addressId > 0) {
			return $addressId;
		}

		throw new ApiClientException($pApiClientAction);
	}


	/**
	 *
	 * @param FormData $pFormData
	 * @return array
	 *
	 * @throws UnknownFieldException
	 *
	 */

	private function getAddressDataForApiCall(FormData $pFormData): array
	{
		$fieldNameAliases = [
			'Telefon1' => 'phone',
			'Email' => 'email',
			'Telefax1' => 'fax',
		];

		$addressData = [];
		$addressFields = $pFormData->getAddressData();
		$pFieldsCollection = new FieldsCollection();
		$this->_pFieldsCollectionBuilderShort->addFieldsAddressEstate($pFieldsCollection);

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
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function getAddressDataForEmail(FormData $pFormData): array
	{
		$addressData = [];
		$addressFields = $pFormData->getAddressData();

		$pFieldsCollection = new FieldsCollection();
		$this->_pFieldsCollectionBuilderShort->addFieldsAddressEstate($pFieldsCollection);

		foreach ($addressFields as $inputName => $value) {
			$pField = $pFieldsCollection->getFieldByModuleAndName(onOfficeSDK::MODULE_ADDRESS, $inputName);

			switch ($pField->getType()) {
				case FieldTypes::FIELD_TYPE_SINGLESELECT:
					$addressData[$pField->getLabel()] = array_key_exists($value, $pField->getPermittedvalues()) ? $pField->getPermittedvalues()[$value] : $value;
					break;
				case FieldTypes::FIELD_TYPE_MULTISELECT:
					if (!is_array($value)) {
						$addressData[$pField->getLabel()] = array_key($value, $pField->getPermittedvalues()) ?? $value;
					} else {
						$tmpMsValues = [];
						foreach ($value as $val) {
							$tmpMsValues []= array_key($val, $pField->getPermittedvalues()) ?? $val;
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
}
