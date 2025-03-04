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
use onOffice\WPlugin\Controller\AddressListBase;
use onOffice\WPlugin\Controller\AddressListEnvironment;
use onOffice\WPlugin\Controller\AddressListEnvironmentDefault;
use onOffice\WPlugin\Controller\GeoPositionFieldHandlerEmpty;
use onOffice\WPlugin\DataView\DataListViewAddress;
use onOffice\WPlugin\DataView\DataViewAddress;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Utility\__String;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierHandler;
use function esc_html;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\DataView\DataAddressDetailView;
use onOffice\WPlugin\Controller\AddressDetailUrl;
use onOffice\WPlugin\ViewFieldModifier\AddressViewFieldModifierTypes;

/**
 *
 */

class AddressList
implements AddressListBase
{
	/** @var string */
	const CONTACT_CATEGORY_PRIVATE_CUSTOMER = 'privateCustomer';

	/** @var string */
	const CONTACT_CATEGORY_BUSINESS_CUSTOMER = 'businessCustomer';

	/** @var string */
	const CONTACT_CATEGORY_COMPANY = 'company';

	/** @var string */
	const CONTACT_CATEGORY_OFFICE = 'branch';

	/** @var string */
	const CONTACT_CATEGORY_DEPARTMMENT = 'department';

	/** @var string */
	const CONTACT_CATEGORY_MARRIED_COUPLE = 'marriedCouple';

	/** @var string */
	const CONTACT_CATEGORY_INSTITUTION = 'institution';

	/** @var string */
	const CONTACT_CATEGORY_ASSOCIATION = 'association';

	/** @var string */
	const CONTACT_CATEGORY_OWNER_ASSOCIATION  = 'communityOfOwners';

	/** @var string */
	const CONTACT_CATEGORY_JOINT_HEIRS = 'communityOfHeirs';

	/** @var string */
	const CONTACT_CATEGORY_UMBRELLA_ORGANIZATION = 'umbrellaOrganization';

	/** @var string[] */
	private $_addressParametersForImageAlt = [
		'contactCategory',
		'Vorname',
		'Name',
		'Zusatz1',
		'branch',
		'communityOfHeirs',
		'communityOfOwners',
		'umbrellaOrganization',
		'association',
		'institution',
		'department'
	];

	/** @var string[] */
	private $_specialContactData = [
		'mobile',
		'phoneprivate',
		'phonebusiness',
		'phone',
		'emailprivate',
		'emailbusiness',
		'email',
		'faxprivate',
		'faxbusiness',
		'fax',
	];

	const DEFAULT_FIELDS_REPLACE = [
		'defaultemail' => 'Email',
		'defaultphone' => 'Telefon1',
		'defaultfax'   => 'Telefax1'
	];


	/** @var array */
	private $_addressesById = [];

	/** @var AddressListEnvironment */
	private $_pEnvironment = null;

	/** @var APIClientActionGeneric */
	private $_pApiClientAction = null;

	/** @var DataListViewAddress */
	private $_pDataViewAddress = null;

	/** @var array */
	private $_records = [];
	/** @var array */
	private $_recordsRaw = [];

	/** @var array */
	private $_countEstates = [];

	/** @var FieldsCollection */
	private $_pFieldsCollection = [];

	/** @var AddressDetailUrl */
	private $_pLanguageSwitcher;

	/**
	 *
	 * @param DataViewAddress $pDataViewAddress
	 * @param AddressListEnvironment $pEnvironment
	 *
	 */

	public function __construct(DataViewAddress $pDataViewAddress = null, AddressListEnvironment $pEnvironment = null)
	{
		$this->_pEnvironment = $pEnvironment ?? new AddressListEnvironmentDefault();
		$this->_pDataViewAddress = $pDataViewAddress ?? new DataListViewAddress(0, 'default');
		$pSDKWrapper = $this->_pEnvironment->getSDKWrapper();
		$this->_pApiClientAction = new APIClientActionGeneric
			($pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'address');
		$this->_pLanguageSwitcher = new AddressDetailUrl();
	}

	/**
	 * @param array $addressIds
	 * @param array $fields
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws API\ApiClientException
	 */
	public function loadAddressesById(array $addressIds, array $fields)
	{
		$this->_pEnvironment->getFieldnames()->loadLanguage();
		$parameters = [
			'recordids' => $addressIds,
			'data' => $fields,
			'outputlanguage' => Language::getDefault(),
			'formatoutput' => true,
		];
		$parametersRaw = [
				'recordids' => $addressIds,
				'data' => $this->_addressParametersForImageAlt,
				'outputlanguage' => Language::getDefault(),
				'formatoutput' => false,
		];
		$this->_pApiClientAction->setParameters($parameters);
		$this->_pApiClientAction->addRequestToQueue()->sendRequests();

		$records = $this->_pApiClientAction->getResultRecords();
		$this->fillAddressesById($records);
		$this->_pDataViewAddress->setFields($fields);

		$this->addRawRecordsByAPICall(clone $this->_pApiClientAction, $parametersRaw);
        $this->buildFieldsCollectionForAddressCustomLabel();
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
		$parameters = $pDataListViewToApi->buildParameters($apiOnlyFields, $this->_pDataViewAddress, $newPage, true);

		$this->_pApiClientAction->setParameters($parameters);
		$this->_pApiClientAction->addRequestToQueue();

		$addressParameterRaws = $pDataListViewToApi->buildParameters($this->_addressParametersForImageAlt,
			$this->_pDataViewAddress, $newPage);
		$this->addRawRecordsByAPICall(clone $this->_pApiClientAction, $addressParameterRaws);

		$this->getCountEstateForAddress($this->getAddressIds());

		$this->_records = $this->_pApiClientAction->getResultRecords();
		$this->fillAddressesById($this->_records);

		$resultMeta = $this->_pApiClientAction->getResultMeta();
		$numpages = ceil($resultMeta['cntabsolute']/$this->_pDataViewAddress->getRecordsPerPage());

		$multipage = $numpages > 1;
		$more = true;
		$page = $newPage;
		$this->buildFieldsCollectionForAddressCustomLabel();
	}

	/**
	 * @param string $modifier
	 * @return ViewFieldModifierHandler
	 */
	private function generateRecordModifier(string $modifier = AddressViewFieldModifierTypes::MODIFIER_TYPE_DEFAULT): ViewFieldModifierHandler
	{
		$fields = $this->_pDataViewAddress->getFields();

		if ($this->getDataViewAddress() instanceof DataListViewAddress && $this->_pDataViewAddress->getShowPhoto()) {
			$fields []= 'imageUrl';
		}

		if ($this->getDataViewAddress() instanceof DataListViewAddress && $this->_pDataViewAddress->getBildWebseite()) {
			$fields []= 'bildWebseite';
		}

		// only active fields
		$fields = array_intersect($fields,
			array_keys($this->_pEnvironment->getFieldnames()->getFieldList(onOfficeSDK::MODULE_ADDRESS)));

		$pAddressFieldModifierHandler = $this->_pEnvironment->getViewFieldModifierHandler($fields, $modifier);
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
			$this->_addressesById[$address['id']] = array_merge($elements, $additionalContactData);
		}
	}

		/**
	 * @param array $addressIds
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws API\ApiClientException
	 */
	private function getCountEstateForAddress(array $addressIds)
	{
		$pSDKWrapper = $this->_pEnvironment->getSDKWrapper();

		$parameters = [
			'childids' => array_values($addressIds),
			'relationtype' => onOfficeSDK::RELATION_TYPE_CONTACT_BROKER,
		];
		$pAPIClientAction = new APIClientActionGeneric
			($pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'idsfromrelation');
		$pAPIClientAction->setParameters($parameters);
		$pAPIClientAction->addRequestToQueue()->sendRequests();
		$this->collectCountEstates($pAPIClientAction->getResultRecords(), $addressIds);
	}

	/**
	 * @param array $responseArrayContacts
	 * @param array $addressIds
     */
	private function collectCountEstates(array $responseArrayContacts, array $addressIds)
	{
		$records = $responseArrayContacts[0]['elements'] ?? [];

		foreach ($addressIds as $index => $addressId)
		{
			if(!array_key_exists($addressId,$records) || count($records[$addressId]) == 0) {
				$this->_countEstates[$addressId] = 0;
				continue;
			}

			$pSDKWrapper = $this->_pEnvironment->getSDKWrapper();
			$parameters = [
				"filter" => [
					"Id" => [["op" => "IN", "val" => $records[$addressId]]],
					"verkauft" => [["op" => "=", "val" => "0"]],
					"veroeffentlichen" => [["op" => "=", "val" => "1"]],
					"status" => [["op" => "=", "val" => "1"]]
				],
				"listlimit" => 500
			];
			$pAPIClientAction = new APIClientActionGeneric
				($pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'estate');
			$pAPIClientAction->setParameters($parameters);
			$pAPIClientAction->addRequestToQueue()->sendRequests();
			$responseMeta = $pAPIClientAction->getResultMeta();
			$this->_countEstates[$addressId] = (array_key_exists("cntabsolute",$responseMeta)) ? intval($responseMeta["cntabsolute"]) : 0;
		}
	}

    /**
     * @param int $addressId
     * @return int
     */
	public function getCountEstates(int $addressId)
	{
		return intval($this->_countEstates[$addressId]);
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
			if ( isset( $elements[ $replaceField ] ) ) {
				$additionalContactData[ $defaultField ] = $elements[ $replaceField ];
			}
		}

		return $additionalContactData;
	}

	/**
	 * @param int $id
	 * @return array
	 */
	public function getAddressById($id): array
	{
		return $this->_addressesById[$id] ?? [];
	}

	/**
	 * @return int
	 * @throws API\ApiClientException
	 */
	public function getAddressOverallCount()
	{
		return $this->_pApiClientAction->getResultMeta()['cntabsolute'];
	}

	/**
	 * @param string $modifier
	 * @param bool $raw
	 * @return array
	 */
	public function getRows(string $modifier = AddressViewFieldModifierTypes::MODIFIER_TYPE_DEFAULT, bool $raw = false): array
	{
		$pAddressFieldModifier = $this->generateRecordModifier($modifier);
		return array_map(function($values) use ($pAddressFieldModifier, $raw): ArrayContainer {
			$valuesNew = $pAddressFieldModifier->processRecord($values);

			if (!empty($valuesNew['bildWebseite'])) {
				$valuesNew['imageUrl'] = $valuesNew['bildWebseite'];
				unset($valuesNew['bildWebseite']);
			}

			return $this->getArrayContainerByRow($raw, $valuesNew);
		}, $this->_addressesById);
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
	/** @return DataViewAddress */
	public function getDataViewAddress(): DataViewAddress
		{ return $this->_pDataViewAddress; }
	/**
	 * @param string $field
	 * @param bool $raw
	 * @return string
	 */
	public function getFieldLabel(string $field, bool $raw = false): string
	{
		$recordType = onOfficeSDK::MODULE_ADDRESS;

		try {
			$label = $this->_pFieldsCollection->getFieldByModuleAndName($recordType, $field)->getLabel();
		} catch (UnknownFieldException $pE) {
			$label = $this->_pEnvironment->getFieldnames()->getFieldLabel($field, $recordType);
		}
		if ($this->_pDataViewAddress instanceof DataAddressDetailView) {
            try {
                $pLanguage = $this->_pEnvironment->getContainer()->get(Language::class)->getLocale();
            } catch (DependencyException | NotFoundException $e) {
                return $raw ? $label : esc_html($label);
            }
            $dataView = $this->_pDataViewAddress->getCustomLabels();
			if (!empty( $dataView[ $field ][ $pLanguage ])) {
				$label = $dataView[ $field ][ $pLanguage ];
			}
		}

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
	 *
	 */
	private function buildFieldsCollectionForAddressCustomLabel()
	{
		$this->_pFieldsCollection = new FieldsCollection();
		$pFieldBuilderShort = $this->_pEnvironment->getFieldsCollectionBuilderShort();
		$pFieldBuilderShort->addFieldsAddressEstate($this->_pFieldsCollection);
		$pFieldBuilderShort->addFieldsEstateDecoratorReadAddressBackend($this->_pFieldsCollection);

		if ($this->_pDataViewAddress instanceof DataListViewAddress && !empty($this->_pDataViewAddress->getName())) {
			$pFieldBuilderShort->addCustomLabelFieldsAddressFrontend($this->_pFieldsCollection, $this->_pDataViewAddress->getName());
		}
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
		if (!empty($this->getDataViewAddress()->getConvertInputTextToSelectForField())) {
			$pBuilderShort->addFieldAddressCityValues($pFieldsCollection);
		}
		$fieldsValues = $pFilterableFields->getVisibleFilterableFields
			($this->_pDataViewAddress, $pFieldsCollection, new GeoPositionFieldHandlerEmpty);
		$result = [];
		foreach ($fieldsValues as $field => $value) {
			$result[$field] = $pFieldsCollection->getFieldByKeyUnsafe($field)
				->getAsRow();
			$result[$field]['name'] = $field;
			$result[$field]['value'] = $value;
			$result[$field]['label'] = $this->getFieldLabel($field);
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

	/**
	 * @param int $addressId
	 *
	 * @return string
	 */
	public function generateImageAlt(int $addressId): string
	{
		$addressRawElements = $this->_recordsRaw[$addressId]['elements'] ?? [];
		if (empty($addressRawElements)) {
			return '';
		}

		$contactCategory = $addressRawElements['contactCategory'];

		switch ($contactCategory) {
			case AddressList::CONTACT_CATEGORY_ASSOCIATION:
				$imageAlt = $addressRawElements['association'] ?? '';
				break;
			case AddressList::CONTACT_CATEGORY_INSTITUTION:
				$imageAlt = $addressRawElements['institution'] ?? '';
				break;
			case AddressList::CONTACT_CATEGORY_BUSINESS_CUSTOMER:
			case AddressList::CONTACT_CATEGORY_PRIVATE_CUSTOMER:
				$firstName = $addressRawElements['Vorname'] ?? '';
				$name = $addressRawElements['Name'] ?? '';
				$imageAlt = $firstName . ' ' . $name;
				break;
			case AddressList::CONTACT_CATEGORY_COMPANY:
				$imageAlt = $addressRawElements['Zusatz1'] ?? '';
				break;
			case AddressList::CONTACT_CATEGORY_OFFICE:
				$imageAlt = $addressRawElements['branch'] ?? '';
				break;
			case AddressList::CONTACT_CATEGORY_DEPARTMMENT:
				$imageAlt = $addressRawElements['department'] ?? '';
				break;
			case AddressList::CONTACT_CATEGORY_JOINT_HEIRS:
				$imageAlt = $addressRawElements['communityOfHeirs'] ?? '';
				break;
			case AddressList::CONTACT_CATEGORY_MARRIED_COUPLE:
				$imageAlt = $addressRawElements['Name'] ?? '';
				break;
			case AddressList::CONTACT_CATEGORY_OWNER_ASSOCIATION:
				$imageAlt = $addressRawElements['communityOfOwners'] ?? '';
				break;
			case AddressList::CONTACT_CATEGORY_UMBRELLA_ORGANIZATION:
				$imageAlt = $addressRawElements['umbrellaOrganization'] ?? '';
				break;
			default:
				$imageAlt = '';
		}

		return $imageAlt;
	}

	/**
	 * @param string|null $firstName
	 * @param string|null $lastName
	 * @param string|null $company
	 * @return string
	 */
	public static function createAddressTitle(string $firstName, string $lastName, string $company): string
	{
		$parts = [];
		if (!empty($firstName)) {
			$parts[] = strtolower($firstName);
		}
		if (!empty($lastName)) {
			$parts[] = strtolower($lastName);
		}
		if (!empty($company)) {
			$parts[] = strtolower($company);
		}

		return implode(' ', $parts);
	}

	/**
	 * @param string $addressId
	 * @return string
	 */
	public function getAddressLink(string $addressId): string
	{
		$pageId = $this->_pEnvironment->getDataAddressDetailViewHandler()
			->getAddressDetailView()->getPageId();
		if($pageId == 0) {
			return "";
		}
		$currentAddress = $this->getAddressById($addressId);
		$firstName = $currentAddress['Vorname'] ?? '';
		$lastName = $currentAddress['Name'] ?? '';
		$company = $currentAddress['Zusatz1'] ?? '';
		$parts = [];
		if (!empty($firstName)) {
			$parts[] = strtolower($firstName);
		}
		if (!empty($lastName)) {
			$parts[] = strtolower($lastName);
		}
		if (!empty($company)) {
			$parts[] = strtolower($company);
		}
		$addressTitle = implode(' ', $parts);

		$url      = get_page_link( $pageId );
		$fullLink = $this->_pLanguageSwitcher->createAddressDetailLink( $url, $addressId, $addressTitle );

		$fullLinkElements = parse_url( $fullLink );
		if ( empty( $fullLinkElements['query'] ) ) {
				$fullLink .= '/';
		}

		return $fullLink;
	}

	/**
	 * @return void
	 */
	public function resetAddressesIterator(): void
    {
		reset($this->_records);
	}

	/**
	 * @return mixed
	 */
	public function getListViewId()
	{
		return $this->_pDataViewAddress->getId();
	}

	/**
	 * @return bool
	 */
	public function getShowMapConfig(): bool
	{
		return $this->_pDataViewAddress->getShowMap();
	}

		/**
	 * @return array
	 */
	public function getAddressIds(): array
	{
		return array_column($this->_recordsRaw, 'id');
	}
		/**
	 * @return array
	 */
	public function getCurrentAddress(): array
	{
		return $this->_addressesById;
	}

    /**
     * @param APIClientActionGeneric $addressApiCall
     * @param array $parameters
     * @throws API\ApiClientException
     */
    private function addRawRecordsByAPICall(APIClientActionGeneric $addressApiCall, array $parameters) {
        $addressApiCall->setParameters($parameters);
        $addressApiCall->addRequestToQueue()->sendRequests();
        $recordsRaw = $addressApiCall->getResultRecords();

        $this->_recordsRaw = array_combine(array_column($recordsRaw, 'id'), $recordsRaw);

    }
}
