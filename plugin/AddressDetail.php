<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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

use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierHandler;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Types\ImageTypes;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataView;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2024, onOffice(R) GmbH
 *
 */

class AddressDetail
	extends AddressList
{
	/** @var int */
	private $_addressId = null;

	/**
	 * @param int $id
	 * @throws ApiClientException
	 */

	public function loadAddressDetailView(int $id)
	{
		$this->_addressId = $id;
		$this->loadAddressRecords();
	}

	/**
	 * @return ViewFieldModifierHandler
	 */
	protected function generateRecordModifier(): ViewFieldModifierHandler
	{
		$fields = $this->getAddressDataView()->getFields();

		if (in_array(ImageTypes::PASSPORT_PHOTO, $this->getAddressDataView()->getPictureTypes())) {
			$fields []= 'imageUrl';
		}

		// only active fields
		$fields = array_intersect($fields,
			array_keys($this->getEnvironment()->getFieldnames()->getFieldList(onOfficeSDK::MODULE_ADDRESS)));

		$pAddressFieldModifierHandler = $this->getEnvironment()->getViewFieldModifierHandler($fields);

		return $pAddressFieldModifierHandler;
	}

	/**
	 * @throws API\ApiClientException
	 * @global int $page
	 * @global bool $more
	 * @global int $numpages
	 * @global bool $multipage
	 */
	public function loadAddressRecords()
	{
		$this->getEnvironment()->getFieldnames()->loadLanguage();
		$pModifier = $this->generateRecordModifier();

		$apiOnlyFields = $pModifier->getAllAPIFields();

		$parameters = array(
			'data' => $apiOnlyFields,
			'outputlanguage' => Language::getDefault(),
			'formatoutput' => true,
		);

		$pApiCall = new APIClientActionGeneric
			($this->getEnvironment()->getSDKWrapper(), onOfficeSDK::ACTION_ID_READ, 'address');
		$pApiCall->setResourceId($this->_addressId);
		$pApiCall->setParameters($parameters);
		$pApiCall->addRequestToQueue()->sendRequests();

		$records = $pApiCall->getResultRecords();
		$elements = $records[0]['elements'] ?? [];

		$additionalContactData = $this->collectAdditionalContactData($elements);
		unset($elements['id']);
		$addressesById[$records[0]['id']] = array_merge($elements, $additionalContactData);
		$this->setAddressById($addressesById);
	}

	/**
	 *
	 * @param int $addressId
	 *
	 */

	public function setAddressId(int $addressId)
	{
		$this->_addressId = $addressId;
	}

	/**
	 */
	public function getEstateAddressOwner(int $pDataListViewAddress)
	{
		$pSDKWrapper = $this->getEnvironment()->getSDKWrapper();

		$parameters = [
			'childids' => [$pDataListViewAddress],
			'relationtype' => onOfficeSDK::RELATION_TYPE_ESTATE_ADDRESS_OWNER,
		];
		$pAPIClientAction = new APIClientActionGeneric
			($pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'idsfromrelation');
		$pAPIClientAction->setParameters($parameters);
		$pAPIClientAction->addRequestToQueue()->sendRequests();
		$this->collectEstateAddressOwner($pAPIClientAction->getResultRecords());
	}

	/**
	 * @param array $responseArrayContacts
	 * @throws API\ApiClientException
	 */
	private function collectEstateAddressOwner($responseArrayContacts)
	{
		$currentPage = 1;
		$records = $responseArrayContacts[0]['elements'] ?? [];
		$allEstateIds = [];

		foreach ($records as $addressIds) {
			$allEstateIds = array_unique(array_merge($allEstateIds, $addressIds));
		}


		if ($allEstateIds !== []) {
			$this->getEnvironment()->getEstateList()->loadEstateByAddressId($currentPage, $allEstateIds, $this->getAddressDataView());
		}
	}

	/**
	 */
	public function estateAddressOwnerIterator()
	{
		return $this->getEnvironment()->getEstateList()->estateAddressOwnerIterator();
	}
}
