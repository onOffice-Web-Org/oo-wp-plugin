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
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\API\DataViewToAPI\DataListViewAddressToAPIParameters;
use onOffice\WPlugin\DataView\DataListViewAddress;
use onOffice\WPlugin\Field\OutputFields;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Utility\__String;
use onOffice\WPlugin\ViewFieldModifier\AddressViewFieldModifierTypes;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierHandler;
use function esc_html;

/**
 *
 */

class AddressList
{
	/** @var string[] */
	private static $_specialContactData = [
		'mobile',
		'phoneprivate',
		'phonebusiness',
		'phone',
		'emailprivate',
		'emailbusiness',
		'email',
	];


	/** @var array */
	private $_adressesById = [];

	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var Fieldnames */
	private $_pFieldnames = null;

	/** @var DataListViewAddress */
	private $_pDataViewAddress = null;


	/**
	 *
	 * @param Fieldnames $pFieldnames
	 *
	 */

	public function __construct(Fieldnames $pFieldnames = null)
	{
		$this->_pSDKWrapper = new SDKWrapper();
		$this->_pFieldnames = $pFieldnames;
		$this->_pDataViewAddress = new DataListViewAddress(0, 'default');
	}


	/**
	 *
	 * @param array $addressIds
	 * @param array $fields
	 *
	 */

	public function loadAdressesById(array $addressIds, array $fields)
	{
		$pApiCall = new APIClientActionGeneric
			($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'address');
		$parameters = [
			'recordids' => $addressIds,
			'data' => $fields,
			'outputlanguage' => Language::getDefault(),
			'formatoutput' => true,
		];
		$pApiCall->setParameters($parameters);
		$pApiCall->addRequestToQueue();
		$this->_pSDKWrapper->sendRequests();
		if ($pApiCall->getResultStatus() === true) {
			$records = $pApiCall->getResultRecords();
			$this->fillAddressesById($records);
			$this->_pDataViewAddress->setFields($fields);
		}
	}


	/**
	 *
	 * @global int $numpages
	 * @global bool $multipage
	 * @global int $page
	 * @global bool $more
	 * @param DataListViewAddress $pDataListViewAddress
	 * @param int $inputPage
	 *
	 */

	public function loadAddresses(DataListViewAddress $pDataListViewAddress, int $inputPage = 1)
	{
		global $numpages, $multipage, $page, $more;
		$this->_pDataViewAddress = $pDataListViewAddress;
		$pModifier = $this->generateRecordModifier();

		$pDataListViewToApi = new DataListViewAddressToAPIParameters($pDataListViewAddress);
		$inputPage = $inputPage === 0 ? 1 : $inputPage;
		$pDataListViewToApi->setPage($inputPage);

		$apiOnlyFields = $pModifier->getAllAPIFields();
		$parameters = $pDataListViewToApi->buildParameters($apiOnlyFields);

		$pApiCall = new APIClientActionGeneric
			($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'address');
		$pApiCall->setParameters($parameters);
		$pApiCall->addRequestToQueue();
		$this->_pSDKWrapper->sendRequests();

		if ($pApiCall->getResultStatus() === true) {
			$records = $pApiCall->getResultRecords();
			$this->fillAddressesById($records);

			$resultMeta = $pApiCall->getResultMeta();
			$numpages = ceil($resultMeta['cntabsolute']/$pDataListViewAddress->getRecordsPerPage());

			$multipage = $numpages > 1;
			$more = true;
			$page = $inputPage;
		}
	}


	/**
	 *
	 * @return ViewFieldModifierHandler
	 *
	 */

	private function generateRecordModifier(): ViewFieldModifierHandler
	{
		$fields = $this->_pDataViewAddress->getFields();

		if ($this->_pDataViewAddress->getShowPhoto()) {
			$fields []= 'imageUrl';
		}

		$pAddressFieldModifierHandler = new ViewFieldModifierHandler($fields,
			onOfficeSDK::MODULE_ADDRESS, AddressViewFieldModifierTypes::MODIFIER_TYPE_DEFAULT);
		return $pAddressFieldModifierHandler;
	}


	/**
	 *
	 * @param array $records
	 *
	 */

	private function fillAddressesById(array $records)
	{
		foreach ($records as $address) {
			$elements = $address['elements'];

			$additionalContactData = $this->collectAdditionalContactData($elements);
			unset($elements['id']);
			$this->_adressesById[$address['id']] = array_merge($elements, $additionalContactData);
		}
	}


	/**
	 *
	 * @param array $elements
	 * @return array
	 *
	 */

	private function collectAdditionalContactData(array $elements)
	{
		$additionalContactData = [];
		foreach ($elements as $key => $value) {
			foreach (self::$_specialContactData as $startString) {
				if (__String::getNew($key)->startsWith($startString)) {
					if (!isset($additionalContactData[$startString])) {
						$additionalContactData[$startString] = array();
					}

					$additionalContactData[$startString] []= $value;
				}
			}
		}

		return $additionalContactData;
	}


	/**
	 *
	 * @param int $id
	 * @return array
	 *
	 */

	public function getAddressById($id)
	{
		$result = array();

		if (isset($this->_adressesById[$id])) {
			$result = $this->_adressesById[$id];
		}

		return $result;
	}


	/**
	 *
	 * @param bool $raw
	 * @return array
	 *
	 */

	public function getRows($raw = false)
	{
		$pAddressList = $this;
		$pAddressFieldModifier = $this->generateRecordModifier();
		return array_map(function($values) use ($pAddressFieldModifier, $pAddressList, $raw) {
			$valuesNew = $pAddressFieldModifier->processRecord($values);
			return $pAddressList->getArrayContainerByRow($raw, $valuesNew);
		}, $this->_adressesById);
	}


	/**
	 *
	 * @param bool $raw
	 * @param array $row
	 * @return ArrayContainerEscape
	 *
	 */

	public function getArrayContainerByRow($raw, array $row)
	{
		$pArrayContainer = null;

		if ($raw) {
			$pArrayContainer = new ArrayContainer($row);
		} else {
			$pArrayContainer = new ArrayContainerEscape($row);
		}

		return $pArrayContainer;
	}


	/**
	 *
	 * @param string $field
	 * @param bool $raw
	 * @return string
	 *
	 */

	public function getFieldLabel($field, $raw = false)
	{
		$label = $field;
		$pFieldnames = $this->_pFieldnames;

		if ($pFieldnames !== null) {
			$label = $pFieldnames->getFieldLabel($field, onOfficeSDK::MODULE_ADDRESS);
		}
		return $raw ? $label : esc_html($label);
	}


	/**
	 *
	 * @param string $field
	 * @return string
	 *
	 */

	public function getFieldType($field)
	{
		$pFieldnames = $this->_pFieldnames;

		if ($pFieldnames !== null) {
			$fieldInformation = $pFieldnames->getFieldInformation($field,
				onOfficeSDK::MODULE_ADDRESS);
			return $fieldInformation['type'];
		}
	}


	/**
	 *
	 * @return string[] An array of visible fields
	 *
	 */

	public function getVisibleFilterableFields(): array
	{
		$pDataListView = $this->_pDataViewAddress;
		$pFilterableFields = new OutputFields($pDataListView);
		$fieldsValues = $pFilterableFields->getVisibleFilterableFields();
		$result = [];
		foreach ($fieldsValues as $field => $value) {
			$result[$field] = $this->_pFieldnames->getFieldInformation
				($field, onOfficeSDK::MODULE_ADDRESS);
			$result[$field]['value'] = $value;
		}
		return $result;
	}


	/** @param SDKWrapper $pSDKWrapper */
	public function setSDKWrapper(SDKWrapper $pSDKWrapper)
		{ $this->_pSDKWrapper = $pSDKWrapper; }

	/** @return SDKWrapper */
	public function getSDKWrapper()
		{ return $this->_pSDKWrapper; }
}