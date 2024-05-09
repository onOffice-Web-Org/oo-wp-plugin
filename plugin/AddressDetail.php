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

		if (in_array(ImageTypes::USERPHOTO, $this->getAddressDataView()->getPictureTypes())) {
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

		$pApiCall = new APIClientActionGeneric
			($this->getEnvironment()->getSDKWrapper(), onOfficeSDK::ACTION_ID_READ, 'address');
		$pApiCall->setResourceId($this->_addressId);
		$pApiCall->setParameters($parameters);
		$pApiCall->addRequestToQueue()->sendRequests();

		$records = $pApiCall->getResultRecords();
		$elements = $records[0]['elements'];

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
}
