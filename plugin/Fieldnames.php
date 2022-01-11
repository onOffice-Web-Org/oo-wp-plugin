<?php

/**
 *
 *    Copyright (C) 2019 onOffice(R) GmbH
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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Field\FieldModuleCollection;
use onOffice\WPlugin\Field\FieldnamesEnvironment;
use onOffice\WPlugin\Field\FieldnamesEnvironmentDefault;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use function __;

/**
 *
 * @deprecated use FieldsCollection + builders instead
 *
 */

class Fieldnames
{
	/** @var FieldnamesEnvironment */
	private $_pEnvironment = null;

	/** @var array */
	private $_fieldList = [];

	/** @var bool */
	private $_inactiveOnly = false;

	/** @var FieldModuleCollection */
	private $_pExtraFieldsCollection = null;


	/**
	 *
	 * @param FieldModuleCollection $pExtraFieldsCollection
	 * @param bool $inactiveOnly
	 * @param FieldnamesEnvironment $pEnvironment
	 *
	 */

	public function __construct(
		FieldModuleCollection $pExtraFieldsCollection,
		bool $inactiveOnly = false,
		FieldnamesEnvironment $pEnvironment = null)
	{
		$this->_pExtraFieldsCollection = $pExtraFieldsCollection;
		$this->_inactiveOnly = $inactiveOnly;
		$this->_pEnvironment = $pEnvironment ?? new FieldnamesEnvironmentDefault();
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
			'language' => $this->_pEnvironment->getLanguage(),
			'modules' => [onOfficeSDK::MODULE_ADDRESS, onOfficeSDK::MODULE_ESTATE],
			'realDataTypes' => true
		];

		if ($this->_inactiveOnly) {
			$parametersGetFieldList['showOnlyInactive'] = true;
		}

		$pSDKWrapper = $this->_pEnvironment->getSDKWrapper();

		$pApiClientActionFields = new APIClientActionGeneric
			($pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'fields');
		$pApiClientActionFields->setParameters($parametersGetFieldList);
		$pApiClientActionFields->addRequestToQueue();
		$pSDKWrapper->sendRequests();

		$this->createFieldList($pApiClientActionFields);
		$this->setPermittedValuesForEstateSearchFields();
		$this->mergeFieldLists();
	}


	/**
	 *
	 */

	private function setPermittedValuesForEstateSearchFields()
	{
		$pCollection = $this->_pExtraFieldsCollection;
		$this->_pExtraFieldsCollection = new FieldsCollection();
		array_map([$this->_pExtraFieldsCollection, 'addField'], $pCollection->getAllFields());

		try {
			$pField = $this->_pExtraFieldsCollection->getFieldByModuleAndName
				(onOfficeSDK::MODULE_ESTATE, GeoPosition::ESTATE_LIST_SEARCH_COUNTRY);
			$countryField =
				$this->_fieldList[onOfficeSDK::MODULE_SEARCHCRITERIA]['range_land'] ??
				$this->_fieldList[onOfficeSDK::MODULE_ESTATE]['land'] ?? [];

			$pField->setPermittedvalues($countryField['permittedvalues'] ?? []);
		} catch (UnknownFieldException $pException) {}
	}


	/**
	 *
	 */

	private function mergeFieldLists()
	{
		$newFieldsByModule = $this->getExtraFields();

		foreach ($newFieldsByModule as $module => $newFields) {
			$this->_fieldList[$module] = array_merge($this->_fieldList[$module] ?? [], $newFields);
		}
	}

	/**
	 * @param APIClientActionGeneric $pApiClientAction
	 * @throws API\APIEmptyResultException
	 */

	private function createFieldList(APIClientActionGeneric $pApiClientAction)
	{
		$fieldResult = $pApiClientAction->getResultRecords();

		foreach ($fieldResult as $moduleProperties) {
			$module = $moduleProperties['id'];
			$fieldArray = $moduleProperties['elements'];

			if (isset($fieldArray['label'])) {
				unset($fieldArray['label']);
			}

			foreach ($fieldArray as $fieldName => $fieldProperties) {
				$fieldProperties['module'] = $module;
				if (($fieldProperties['label'] ?? '') === '') {
					$fieldProperties['label'] = sprintf('(%s)', $fieldName);
				}

				$this->_fieldList[$module][$fieldName] = $fieldProperties;
			}
		}
	}


	/**
	 *
	 * @param string $field
	 * @param string $module recordType
	 * @return string
	 *
	 */

	public function getFieldLabel(string $field, $module): string
	{
		try {
			$row = $this->getRow($module, $field);
			$fieldNewName = $row['label'];
		} catch (UnknownFieldException $pE) {
			$fieldNewName = $field;
		}

		return $fieldNewName;
	}


	/**
	 *
	 * @param string $module record type
	 * @return array
	 *
	 */

	public function getFieldList($module): array
	{
		$fieldList = $this->_fieldList[$module] ?? [];
		return $fieldList;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getExtraFields(): array
	{
		$extraFields = [];
		$extraFieldsObject = $this->_pExtraFieldsCollection->getAllFields();

		foreach ($extraFieldsObject as $pField) {
			$newContent = $pField->getCategory() !== '' ?
				$pField->getCategory() : __('Form Specific Fields', 'onoffice-for-wp-websites');
			$pField->setCategory($newContent);
			$extraFields[$pField->getModule()][$pField->getName()] = $pField->getAsRow();
		}

		return $extraFields;
	}

	/**
	 * @param string $fieldName
	 * @param string $module
	 * @return string
	 * @throws UnknownFieldException
	 */
	public function getType(string $fieldName, string $module): string
	{
		$row = $this->getRow($module, $fieldName);
		return $row['type'];
	}

	/**
	 *
	 * @param string $field
	 * @param string $module
	 * @return array
	 * @throws UnknownFieldException
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
	 * @throws UnknownFieldException
	 *
	 */

	private function getRow($module, $field): array
	{
		if (isset($this->_fieldList[$module][$field])) {
			return $this->_fieldList[$module][$field];
		}

		throw new UnknownFieldException;
	}


	/** @return bool */
	public function getInactiveOnly(): bool
		{ return $this->_inactiveOnly; }
}
