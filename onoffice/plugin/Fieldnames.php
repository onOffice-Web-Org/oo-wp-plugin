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
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\SDKWrapper;

/**
 *
 */

class Fieldnames
{
	/** @var array */
	private static $_readOnlyFieldsAnnotations = [
		onOfficeSDK::MODULE_ADDRESS => [
			'defaultphone' => 'Phone (Marked as default in onOffice)',
			'defaultemail' => 'E-Mail (Marked as default in onOffice)',
			'defaultfax' => 'Fax (Marked as default in onOffice)',
		],
		onOfficeSDK::MODULE_SEARCHCRITERIA => [
			'krit_bemerkung_oeffentlich' => 'Search Criteria Comment (external)',
		],
	];

	/** @var array */
	private static $_defaultSortByFields = [
		onOfficeSDK::MODULE_ADDRESS => [
			'KdNr',
			'Eintragsdatum',
			'Name',
		],
		onOfficeSDK::MODULE_ESTATE => [
			'kaufpreis',
			'kaltmiete',
			'pacht',
			'wohnflaeche',
			'anzahl_zimmer',
			'ort',
			'grundstuecksflaeche',
			'gesamtflaeche',
		],
	];

	/** @var array */
	private $_fieldList = [];

	/** @var array */
	private $_searchcriteriaRangeInfos = [];

	/** @var array */
	private $_umkreisFields = [];

	/** @var string */
	private $_language = null;

	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var Types\FieldsCollection[] */
	private $_apiReadOnlyFieldCollections = [];

	/** @var bool */
	private $_addApiOnlyFields = false;

	/** @var bool */
	private $_addInternalAnnotations = false;

	/** @var bool */
	private $_inactiveOnly = false;


	/**
	 *
	 * @param bool $addApiOnlyFields Adds special fields for API
	 * @param bool $internalAnnotations Adds a more descriptive field label for admin page
	 * @param bool $inactiveOnly
	 *
	 */

	public function __construct(
		bool $addApiOnlyFields = false,
		bool $internalAnnotations = false,
		bool $inactiveOnly = false)
	{
		$this->_language = Language::getDefault();
		$this->_pSDKWrapper = new SDKWrapper();
		$pCollectionFactory = new Types\LocalFieldsCollectionFactory();
		$this->_addApiOnlyFields = $addApiOnlyFields;
		$this->_addInternalAnnotations = $internalAnnotations;
		$this->_inactiveOnly = $inactiveOnly;

		$modules = [
			onOfficeSDK::MODULE_ADDRESS,
			onOfficeSDK::MODULE_ESTATE,
			onOfficeSDK::MODULE_SEARCHCRITERIA,
		];

		foreach ($modules as $module) {
			$this->_apiReadOnlyFieldCollections[$module] =
				$pCollectionFactory->produceCollection($module);
		}
	}


	/**
	 *
	 */

	public function loadLanguage()
	{
		$parametersGetFieldList = [
			'labels' => true,
			'showContent' => true,
			'showTable' => true,
			'language' => $this->_language,
			'modules' => [
				onOfficeSDK::MODULE_ADDRESS,
				onOfficeSDK::MODULE_ESTATE,
			],
		];

		if ($this->_inactiveOnly) {
			$parametersGetFieldList['showOnlyInactive'] = true;
		}

		$pSDKWrapper = $this->_pSDKWrapper;
		$handleGetFields = $pSDKWrapper->addRequest
			(onOfficeSDK::ACTION_ID_GET, 'fields', $parametersGetFieldList);

		$requestParamsSearchCriteria = [
			'language' => $this->_language,
			'additionalTranslations' => true,
		];

		$handleSearchCriteria = $pSDKWrapper->addRequest
			(onOfficeSDK::ACTION_ID_GET, 'searchCriteriaFields', $requestParamsSearchCriteria);

		$pSDKWrapper->sendRequests();

		$responseArrayFieldList = $pSDKWrapper->getRequestResponse($handleGetFields);
		$fieldList = $responseArrayFieldList['data']['records'];

		$this->createFieldList($fieldList);
		$this->completeFieldListWithSearchcriteria($handleSearchCriteria);
		$this->setPermittedValuesForEstateSearchFields();
	}


	/**
	 *
	 */

	public function loadEstateSearchGeoPositionFields()
	{
		$pGeoPosition = new GeoPosition();
		$geoPositionSearchFields = $pGeoPosition->getEstateSearchFields();
		$module = onOfficeSDK::MODULE_ESTATE;
		$pCollection = $this->_apiReadOnlyFieldCollections[$module];

		foreach ($geoPositionSearchFields as $field) {
			$this->_fieldList[$module][$field] = $pCollection->getByName($field)->getAsRow();
		}
	}


	/**
	 *
	 */

	private function setPermittedValuesForEstateSearchFields()
	{
		$permittedValuesLand = [];

		if (isset($this->_fieldList[onOfficeSDK::MODULE_SEARCHCRITERIA]['range_land'])) {
			$permittedValuesLand =
				$this->_fieldList[onOfficeSDK::MODULE_SEARCHCRITERIA]['range_land']['permittedvalues'];
		} elseif (isset($this->_fieldList[onOfficeSDK::MODULE_ESTATE]['land'])) {
			$permittedValuesLand =
				$this->_fieldList[onOfficeSDK::MODULE_ESTATE]['land']['permittedvalues'];
		}

		$pCollection = $this->_apiReadOnlyFieldCollections[onOfficeSDK::MODULE_ESTATE];
		$pCollection->getByName(GeoPosition::ESTATE_LIST_SEARCH_COUNTRY)->setPermittedvalues
			($permittedValuesLand);
	}


	/**
	 *
	 * @param int $handle
	 *
	 */

	private function completeFieldListWithSearchcriteria($handle)
	{
		$response = $this->_pSDKWrapper->getRequestResponse($handle);

		foreach ($response['data']['records'] as $tableValues) {
			$fields = $tableValues['elements'];

			foreach ($fields['fields'] as $field) {
				$fieldId = $field['id'];

				$fieldProperties = [
					'type' => $field['type'],
					'label' => $field['name'],
					'default' => $field['default'] ?? null,
					'permittedvalues' => $field['values'] ?? [],
					'content' => __('Search Criteria', 'onoffice'),
					'module' => onOfficeSDK::MODULE_SEARCHCRITERIA,
				];

				if (($field['rangefield'] ?? false) &&
					isset($field['additionalTranslations'])) {
					$this->_searchcriteriaRangeInfos[$fieldId] = $field['additionalTranslations'];
				}

				if ($fields['name'] == 'Umkreis') {
					$this->_umkreisFields[$fieldId] = $fieldProperties;
				}

				$this->_fieldList[onOfficeSDK::MODULE_SEARCHCRITERIA][$fieldId] = $fieldProperties;
			}
		}
	}


	/**
	 *
	 * @param string $field
	 * @return bool
	 *
	 */

	public function inRangeSearchcriteriaInfos(string $field): bool
	{
		return isset($this->_searchcriteriaRangeInfos[$field]);
	}


	/**
	 *
	 * @param string $field
	 * @return bool
	 *
	 */

	public function isUmkreisField(string $field): bool
	{
		return isset($this->_umkreisFields[$field]);
	}


	/**
	 *
	 * @param atring $field
	 * @return array
	 *
	 */

	public function getUmkreisValuesForField(string $field): array
	{
		return $this->_umkreisFields[$field] ?? [];
	}


	/**
	 *
	 * @param string $field
	 * @return array
	 *
	 */

	public function getRangeSearchcriteriaInfosForField(string $field): array
	{
		return $this->_searchcriteriaRangeInfos[$field] ?? [];
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getUmkreisFields(): array
	{
		return $this->_umkreisFields;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getSearchcriteriaRangeInfos(): array
	{
		return $this->_searchcriteriaRangeInfos;
	}


	/**
	 *
	 * @param array $fieldResult
	 *
	 */

	private function createFieldList(array $fieldResult)
	{
		foreach ($fieldResult as $moduleProperties) {
			if (!isset($moduleProperties['elements'])) {
				continue;
			}

			$module = $moduleProperties['id'];

			foreach ($moduleProperties['elements'] as $fieldName => $fieldProperties) {
				if ( 'label' == $fieldName ) {
					continue;
				}

				$fieldProperties['module'] = $module;
				$this->_fieldList[$module][$fieldName] = $fieldProperties;
			}
		}
	}


	/**
	 *
	 * @param string $fieldname
	 * @param string $module
	 * @return bool
	 *
	 */

	public function getModuleContainsField(string $fieldname, $module): bool
	{
		return isset($this->_fieldList[$module][$fieldname]);
	}


	/**
	 *
	 * @param string $field
	 * @param string $module recordType
	 *
	 * @return string
	 *
	 */

	public function getFieldLabel(string $field, $module): string
	{
		$fieldNewName = $field;
		$row = $this->getRow($module, $field);

		if (!is_null($row)) {
			$fieldNewName = $row['label'];
		}

		return $fieldNewName;
	}


	/**
	 *
	 * @param string $module recordType
	 * @param string $mode
	 * @return array
	 *
	 */

	public function getFieldList($module, string $mode = ''): array
	{
		$fieldList = [];
		if (isset($this->_fieldList[$module])) {
			$fieldList = $this->_fieldList[$module];
		}

		if ($this->_addApiOnlyFields) {
			$extraFields = $this->getExtraFields($module);
			$fieldList = array_merge($fieldList, $extraFields);
		}

		if ($mode !== '')	{
			$pGeoPosition = new GeoPosition();
			$fieldList = $pGeoPosition->transform($fieldList, $mode);
		}

		return $fieldList;
	}


	/**
	 *
	 * @param string $module
	 * @return array
	 *
	 */

	private function getExtraFields($module): array
	{
		$extraFields = [];
		$pFieldCollection = $module != '' ? $this->_apiReadOnlyFieldCollections[$module] : null;

		if ($pFieldCollection !== null) {
			$extraFieldsObject = $pFieldCollection->getAllFields();
			foreach ($extraFieldsObject as $pField) {
				$newContent = $pField->getCategory() !== '' ?
					$pField->getCategory() : __('Form Specific Fields', 'onoffice');
				$pField->setCategory($newContent);
				$extraFields[$pField->getName()] = $pField->getAsRow();
			}
		}

		if ($this->_addInternalAnnotations && isset(self::$_readOnlyFieldsAnnotations[$module])) {
			$annotatedFields = [];
			foreach ($extraFields as $field => $option) {
				if (isset(self::$_readOnlyFieldsAnnotations[$module][$field])) {
					$option['label'] = self::$_readOnlyFieldsAnnotations[$module][$field];
					$annotatedFields[$field] = $option;
				}
			}
			$extraFields = array_merge($extraFields, $annotatedFields);
		}

		return $extraFields;
	}


	/**
	 *
	 * @param string $fieldName
	 * @param string $module
	 * @return string
	 *
	 */

	public function getType($fieldName, $module)
	{
		$row = $this->getRow($module, $fieldName);
		if (!is_null($row)) {
			return $row['type'];
		}
	}


	/**
	 *
	 * @param string $inputField
	 * @param string $module
	 * @return string
	 *
	 */

	public function getPermittedValues(string $inputField, string $module)
	{
		$row = $this->getRow($module, $inputField);
		if (!is_null($row)) {
			return $row['permittedvalues'];
		}
	}


	/**
	 *
	 * @param string $field
	 * @param string $module
	 * @return array
	 *
	 */

	public function getFieldInformation(string $field, string $module): array
	{
		return $this->getRow($module, $field);
	}


	/**
	 *
	 * @param string $module
	 * @param string $field
	 * @return array
	 *
	 */

	private function getRow($module, $field)
	{
		$pModuleCollection = $this->_apiReadOnlyFieldCollections[$module] ?? null;

		if (isset($this->_fieldList[$module][$field])) {
			return $this->_fieldList[$module][$field];
		} elseif ($pModuleCollection !== null && $pModuleCollection->containsField($field)) {
			return $pModuleCollection->getByName($field)->getAsRow();
		}
	}


	/**
	 *
	 * @return array
	 *
	 */

	static public function getDefaultSortByFields(string $module): array
	{
		if (isset(self::$_defaultSortByFields[$module])) {
			return self::$_defaultSortByFields[$module];
		}

		return [];
	}


	/** @return SDKWrapper */
	public function getSDKWrapper(): SDKWrapper
		{ return $this->_pSDKWrapper; }

	/** @param SDKWrapper $pSDKWrapper */
	public function setSDKWrapper(SDKWrapper $pSDKWrapper)
		{ $this->_pSDKWrapper = $pSDKWrapper; }
}
