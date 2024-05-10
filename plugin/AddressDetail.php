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

use onOffice\WPlugin\DataView\DataViewAddress;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierHandler;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use DI\DependencyException;
use onOffice\WPlugin\Types\ImageTypes;

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

	/** @var string */
	private $_userName = '';

	const SHOW_PICTURE_TYPE_PASSPORT_PHOTO = 'PassportPhoto';
	const SHOW_PICTURE_TYPE_USER_PHOTO = 'UserPhoto';

	/**
	 *
	 * @return int
	 *
	 */

	protected function getNumEstatePages()
	{
		return 1;
	}


	/**
	 *
	 * @param int $id
	 *
	 */

	public function loadSingleAddress($id)
	{
		$this->_addressId = $id;
		$this->loadAddresses(1);
	}

	/**
	 *
	 * @return string
	 *
	 */

	public function getShortCodeForm(): string
	{
		$result = '';

		if ($this->getAddressDataView()->getShortCodeForm() == '') {
			return '';
		}

		$result = $this->getAddressDataView()->getShortCodeForm();

		return  '[oo_address view="' . $result . '"]';
	}

	/**
	 *
	 * @return int
	 *
	 */

	protected function getRecordsPerPage()
	{
		return 1;
	}


	/**
	 * @return ViewFieldModifierHandler
	 */
	protected function generateRecordModifier(): ViewFieldModifierHandler
	{
		$fields = $this->getAddressDataView()->getFields();

		if (in_array(ImageTypes::PASSPORTPHOTO, $this->getAddressDataView()->getPictureTypes())) {
			$fields []= 'imageUrl';
		}

		// only active fields
		$fields = array_intersect($fields,
			array_keys($this->getEnvironment()->getFieldnames()->getFieldList(onOfficeSDK::MODULE_ADDRESS)));

		$pAddressFieldModifierHandler = $this->getEnvironment()->getViewFieldModifierHandler($fields);
		return $pAddressFieldModifierHandler;
	}

	/**
	 * @param int $inputPage
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws API\ApiClientException
	 * @global int $page
	 * @global bool $more
	 * @global int $numpages
	 * @global bool $multipage
	 */
	public function loadAddresses(int $inputPage = 1)
	{
		$this->getEnvironment()->getFieldnames()->loadLanguage();
		$pModifier = $this->generateRecordModifier();

		$apiOnlyFields = $pModifier->getAllAPIFields();

		$parameters = array(
			'data' => $apiOnlyFields,
			'outputlanguage' => Language::getDefault(),
			'formatoutput' => true,
		);

		$parameters['data'][] = 'Benutzer';

		$pApiCall = new APIClientActionGeneric
			($this->getEnvironment()->getSDKWrapper(), onOfficeSDK::ACTION_ID_READ, 'address');
		$pApiCall->setResourceId($this->_addressId);
		$pApiCall->setParameters($parameters);
		$pApiCall->addRequestToQueue()->sendRequests();

		$records = $pApiCall->getResultRecords();
		$elements = $records[0]['elements'];
		$this->_userName = $elements['Benutzer'] ?? '';

		$additionalContactData = $this->collectAdditionalContactData($elements);
		unset($elements['id']);
		$adressesById[$records[0]['id']] = array_merge($elements, $additionalContactData);
		$this->setAddressById($adressesById);
	}

	/**
	 *
	 * @return int
	 *
	 */

	public function getAddressId(): int
	{
		return $this->_addressId;
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
	 * @param int $userId
	 *
	 * @return void
	 * @throws API\ApiClientException
	 */
	public function getUserPhoto(int $userId): string
	{
		$pApiCall = new APIClientActionGeneric
			($this->getEnvironment()->getSDKWrapper(), onOfficeSDK::ACTION_ID_READ, 'userphoto');
		$pApiCall->setParameters([
			'photosAsLinks' => true,
		]);
		$pApiCall->setResourceId($userId);
		$pApiCall->addRequestToQueue()->sendRequests();
		$userPhotoData = $pApiCall->getResultRecords();

		return $userPhotoData[0]['elements']['photo'] ?? '';
	}

	/**
	 * @return integer
	 * @throws API\ApiClientException
	 */
	public function getUserId(): int
	{
		$pApiCall = new APIClientActionGeneric
			($this->getEnvironment()->getSDKWrapper(), onOfficeSDK::ACTION_ID_GET, 'users');
		$pApiCall->addRequestToQueue()->sendRequests();

		$listUserData = $pApiCall->getResultRecords();
		foreach ($listUserData as $userData) {
			if ($userData['elements']['username'] === $this->_userName) {
				return $userData['elements']['id'];
			}
		}

		return 0;
	}

	/**
	 * @param string $pictureType
	 *
	 * @return bool
	 */
	public function getPictureTypesOption(string $pictureType): bool
	{
		$pictureTypes = get_option('onoffice-address-default-view')->getPictureTypes();
		if (in_array($pictureType, $pictureTypes)) {
			return true;
		}

		return false;
	}
}
