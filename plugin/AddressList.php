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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

namespace onOffice\WPlugin;

use DI\DependencyException;
use DI\NotFoundException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Controller\AddressListEnvironment;
use onOffice\WPlugin\Controller\AddressListEnvironmentDefault;
use onOffice\WPlugin\Controller\GeoPositionFieldHandlerEmpty;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataListViewAddress;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Utility\__String;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierHandler;
use function esc_html;

/**
 *
 */

class AddressList
{
	/** @var string[] */
	private $_specialContactData = [
		'mobile',
		'phoneprivate',
		'phonebusiness',
		'phone',
		'emailprivate',
		'emailbusiness',
		'email',
	];

	const DEFAULT_FIELDS_REPLACE = [
		'defaultemail' => 'Email',
		'defaultphone' => 'Telefon1',
		'defaultfax'   => 'Telefax1'
	];


	/** @var array */
	private $_adressesById = [];

	/** @var AddressListEnvironment */
	private $_pEnvironment = null;

	/** @var DataListViewAddress */
	private $_pDataViewAddress = null;


	/**
	 *
	 * @param AddressListEnvironment $pEnvironment
	 *
	 */

	public function __construct(AddressListEnvironment $pEnvironment = null)
	{
		$this->_pEnvironment = $pEnvironment ?? new AddressListEnvironmentDefault();
		$this->_pDataViewAddress = new DataListViewAddress(0, 'default');
	}

	/**
	 * @param array $addressIds
	 * @param array $fields
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws API\ApiClientException
	 */
	public function loadAdressesById(array $addressIds, array $fields)
	{
		$this->_pEnvironment->getFieldnames()->loadLanguage();
		$pApiCall = new APIClientActionGeneric
			($this->_pEnvironment->getSDKWrapper(), onOfficeSDK::ACTION_ID_READ, 'address');
		$parameters = [
			'recordids' => $addressIds,
			'data' => $fields,
			'outputlanguage' => Language::getDefault(),
			'formatoutput' => true,
		];
		$pApiCall->setParameters($parameters);
		$pApiCall->addRequestToQueue()->sendRequests();

		$records = $pApiCall->getResultRecords();
		$this->fillAddressesById($records);
		$this->_pDataViewAddress->setFields($fields);
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
		global $numpages, $multipage, $page, $more;
		$this->_pEnvironment->getFieldnames()->loadLanguage();
		$pModifier = $this->generateRecordModifier();

		$pDataListViewToApi = $this->_pEnvironment->getDataListViewAddressToAPIParameters();

		$newPage = $inputPage === 0 ? 1 : $inputPage;
		$apiOnlyFields = $pModifier->getAllAPIFields();
		$parameters = $pDataListViewToApi->buildParameters($apiOnlyFields, $this->_pDataViewAddress, $newPage);

		$pApiCall = new APIClientActionGeneric
			($this->_pEnvironment->getSDKWrapper(), onOfficeSDK::ACTION_ID_READ, 'address');
		$pApiCall->setParameters($parameters);
		$pApiCall->addRequestToQueue()->sendRequests();

		$records = $pApiCall->getResultRecords();
		$this->fillAddressesById($records);

		$resultMeta = $pApiCall->getResultMeta();
		$numpages = ceil($resultMeta['cntabsolute']/$this->_pDataViewAddress->getRecordsPerPage());

		$multipage = $numpages > 1;
		$more = true;
		$page = $newPage;
	}

	/**
	 * @return ViewFieldModifierHandler
	 */
	private function generateRecordModifier(): ViewFieldModifierHandler
	{
		$fields = $this->_pDataViewAddress->getFields();

		if ($this->_pDataViewAddress->getShowPhoto()) {
			$fields []= 'imageUrl';
		}

		// only active fields
		$fields = array_intersect($fields,
			array_keys($this->_pEnvironment->getFieldnames()->getFieldList(onOfficeSDK::MODULE_ADDRESS)));

		$pAddressFieldModifierHandler = $this->_pEnvironment->getViewFieldModifierHandler($fields);
		return $pAddressFieldModifierHandler;
	}

	/**
	 * @param array $records
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
	 * @param array $elements
	 * @return array
	 */
	private function collectAdditionalContactData(array $elements): array
	{
		$additionalContactData = [];
		foreach ($elements as $key => $value) {
			foreach ($this->_specialContactData as $startString) {
				if (__String::getNew($key)->startsWith($startString)) {
					if (!isset($additionalContactData[$startString])) {
						$additionalContactData[$startString] = [];
					}

					$additionalContactData[$startString] []= $value;
				}
			}
		}
		foreach ( self::DEFAULT_FIELDS_REPLACE as $defaultField => $replaceField ) {
			$additionalContactData[ $defaultField ] = $elements[ $replaceField ];
		}

		return $additionalContactData;
	}

	/**
	 * @param int $id
	 * @return array
	 */
	public function getAddressById($id): array
	{
		return $this->_adressesById[$id] ?? [];
	}

	/**
	 * @param bool $raw
	 * @return array
	 */
	public function getRows(bool $raw = false): array
	{
		$pAddressFieldModifier = $this->generateRecordModifier();
		return array_map(function($values) use ($pAddressFieldModifier, $raw): ArrayContainer {
			$valuesNew = $pAddressFieldModifier->processRecord($values);
			return $this->getArrayContainerByRow($raw, $valuesNew);
		}, $this->_adressesById);
	}

	/**
	 * @param bool $raw
	 * @param array $row
	 * @return ArrayContainerEscape
	 */
	private function getArrayContainerByRow(bool $raw, array $row): ArrayContainer
	{
		if ($raw) {
			return new ArrayContainer($row);
		}
		return new ArrayContainerEscape($row);
	}

	/**
	 * @param string $field
	 * @param bool $raw
	 * @return string
	 */
	public function getFieldLabel($field, bool $raw = false): string
	{
		$label = $this->_pEnvironment->getFieldnames()
			->getFieldLabel($field, onOfficeSDK::MODULE_ADDRESS);

		return $raw ? $label : esc_html($label);
	}

	/**
	 * @param string $field
	 * @return string
	 * @throws Field\UnknownFieldException
	 */
	public function getFieldType($field): string
	{
		$fieldInformation = $this->_pEnvironment
			->getFieldnames()
			->getFieldInformation($field, onOfficeSDK::MODULE_ADDRESS);
		return $fieldInformation['type'];
	}

	/**
	 * @return string[] An array of visible fields
	 * @throws Field\UnknownFieldException
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function getVisibleFilterableFields(): array
	{
		$pFilterableFields = $this->_pEnvironment->getOutputFields();
		/** @var FieldsCollectionBuilderShort $pBuilderShort */
		$pBuilderShort = $this->_pEnvironment->getFieldsCollectionBuilderShort();
		$pFieldsCollection = new FieldsCollection();
		$pBuilderShort->addFieldsAddressEstate($pFieldsCollection);
		$fieldsValues = $pFilterableFields->getVisibleFilterableFields
			($this->_pDataViewAddress, $pFieldsCollection, new GeoPositionFieldHandlerEmpty);
		$result = [];
		foreach ($fieldsValues as $field => $value) {
			$result[$field] = $pFieldsCollection->getFieldByKeyUnsafe($field)
				->getAsRow();
			$result[$field]['name'] = $field;
			$result[$field]['value'] = $value;
		}
		return $result;
	}

	/**
	 * @param DataListViewAddress $pDataListViewAddress
	 * @return AddressList
	 */
	public function withDataListViewAddress(DataListViewAddress $pDataListViewAddress): AddressList
	{
		$pAddressList = clone $this;
		$pAddressList->_pDataViewAddress = $pDataListViewAddress;
		return $pAddressList;
	}
}