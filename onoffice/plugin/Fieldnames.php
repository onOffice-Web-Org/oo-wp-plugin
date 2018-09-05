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
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 */

class Fieldnames
{

	/**
	 *
	 * @var array
	 * "label" key needs to be translated
	 *
	 */

	private static $_apiReadOnlyFields = array(
		onOfficeSDK::MODULE_ADDRESS => array(
			'imageUrl' => array(
				'type' => FieldTypes::FIELD_TYPE_TEXT,
				'length' => null,
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'Image',
			),
			'phone' => array(
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 40,
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'Phone',
			),
			'email' => array(
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 80,
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'E-Mail',
			),
			'fax' => array(
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 40,
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'Fax',
			),
			'mobile' => array(
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 40,
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'Mobile',
			),
			'defaultphone' => array(
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 40,
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'Phone',
			),
			'defaultemail' => array(
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 80,
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'E-Mail',
			),
			'defaultfax' => array(
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 40,
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'Fax',
			),
		),
		onOfficeSDK::MODULE_SEARCHCRITERIA => array(
			'krit_bemerkung_oeffentlich' => array(
				'type' => FieldTypes::FIELD_TYPE_TEXT,
				'length' => null,
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'Comment',
			),
			GeoPosition::FIELD_GEO_POSITION => array(
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 250,
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'Geo Position',
				'content' => 'Search Criteria',
			),
		),
		onOfficeSDK::MODULE_ESTATE => array(
			GeoPosition::FIELD_GEO_POSITION => array(
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 250,
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'Geo Position',
				'content' => 'Geografische-Angaben',
			),

			GeoPosition::ESTATE_LIST_SEARCH_COUNTRY => array(
				'type' => FieldTypes::FIELD_TYPE_SINGLESELECT,
				'length' => 250,
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'Land',
				'content' => 'Geografische-Angaben',
				'module' => onOfficeSDK::MODULE_ESTATE,
			),
			GeoPosition::ESTATE_LIST_SEARCH_RADIUS => array(
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 3,
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'Radius (km)',
				'content' => 'Geografische-Angaben',
				'module' => onOfficeSDK::MODULE_ESTATE,
			),
			GeoPosition::ESTATE_LIST_SEARCH_STREET => array(
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 250,
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'Strasse',
				'content' => 'Geografische-Angaben',
				'module' => onOfficeSDK::MODULE_ESTATE,
			),
			GeoPosition::ESTATE_LIST_SEARCH_ZIP => array(
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 10,
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'PLZ',
				'content' => 'Geografische-Angaben',
				'module' => onOfficeSDK::MODULE_ESTATE,
			),
		),
	);

	/** @var array */
	private static $_readOnlyFieldsAnnotations = array(
		onOfficeSDK::MODULE_ADDRESS => array(
			'defaultphone' => 'Phone (Marked as default in onOffice)',
			'defaultemail' => 'E-Mail (Marked as default in onOffice)',
			'defaultfax' => 'Fax (Marked as default in onOffice)',
		),
		onOfficeSDK::MODULE_SEARCHCRITERIA => array(
			'krit_bemerkung_oeffentlich' => 'Search Criteria Comment (external)',
		),
	);

	/** @var array */
	private static $_defaultSortByFields = array(
		onOfficeSDK::MODULE_ADDRESS => array(
			'KdNr',
			'Eintragsdatum',
			'Name',
		),
		onOfficeSDK::MODULE_ESTATE => array(
			'kaufpreis',
			'kaltmiete',
			'pacht',
			'wohnflaeche',
			'anzahl_zimmer',
			'ort',
			'grundstuecksflaeche',
			'gesamtflaeche',
		),
	);

	/** @var array */
	private $_fieldList = array();

	/** @var array */
	private $_searchcriteriaRangeInfos = array();

	/** @var array */
	private $_umkreisFields = array();

	/** @var string */
	private $_language = null;

	/** @var SDKWrapper */
	private $_pSDKWrapper = null;


	/**
	 *
	 * @param string $language
	 *
	 */

	public function __construct($language = null)
	{
		if ($language == null) {
			$this->_language = Language::getDefault();
		} else {
			$this->_language = $language;
		}

		$this->_pSDKWrapper = new SDKWrapper();
	}


	/**
	 *
	 * @param bool $showOnlyInactive
	 *
	 */

	public function loadLanguage($showOnlyInactive = false)
	{
		$parametersGetFieldList = array(
			'labels' => true,
			'showContent' => true,
			'showTable' => true,
			'language' => $this->_language,
			'modules' => array(
				onOfficeSDK::MODULE_ADDRESS,
				onOfficeSDK::MODULE_ESTATE,
			),
		);

		if ($showOnlyInactive) {
			$parametersGetFieldList['showOnlyInactive'] = true;
		}

		$pSDKWrapper = $this->_pSDKWrapper;
		$handleGetFields = $pSDKWrapper->addRequest(
			onOfficeSDK::ACTION_ID_GET, 'fields', $parametersGetFieldList);

		$requestParamsSearchCriteria = array(
			'language' => $this->_language,
			'additionalTranslations' => true,
		);

		$handleSearchCriteria = $pSDKWrapper->addRequest
			(onOfficeSDK::ACTION_ID_GET, 'searchCriteriaFields', $requestParamsSearchCriteria);

		$pSDKWrapper->sendRequests();

		$responseArrayFieldList = $pSDKWrapper->getRequestResponse($handleGetFields);
		$fieldList = $responseArrayFieldList['data']['records'];

		$this->createFieldList( $fieldList );
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

		foreach ($geoPositionSearchFields as $field) {
			$this->_fieldList[onOfficeSDK::MODULE_ESTATE][$field] =
				self::$_apiReadOnlyFields[onOfficeSDK::MODULE_ESTATE][$field];
		}
	}



	/**
	 *
	 */
	private function setPermittedValuesForEstateSearchFields()
	{
		$permittedValuesLand = null;

		if (array_key_exists('range_land', $this->_fieldList[onOfficeSDK::MODULE_SEARCHCRITERIA])) {
			$permittedValuesLand =
					$this->_fieldList[onOfficeSDK::MODULE_SEARCHCRITERIA]['range_land']['permittedvalues'];

		}
		elseif (array_key_exists('land', $this->_fieldList[onOfficeSDK::MODULE_ESTATE])) {
			$permittedValuesLand =
					$this->_fieldList[onOfficeSDK::MODULE_ESTATE]['land']['permittedvalues'];
		}

		self::$_apiReadOnlyFields[onOfficeSDK::MODULE_ESTATE][GeoPosition::ESTATE_LIST_SEARCH_COUNTRY]['permittedvalues'] =
				$permittedValuesLand;
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

				$fieldProperties = array();
				$fieldProperties['type'] = $field['type'];
				$fieldProperties['label'] = $field['name'];
				$fieldProperties['default'] = null;
				$fieldProperties['permittedvalues'] = array();
				$fieldProperties['content'] = __('Search Criteria', 'onoffice');
				$fieldProperties['module'] = onOfficeSDK::MODULE_SEARCHCRITERIA;

				if (isset($field['default'])) {
					$fieldProperties['default'] = $field['default'];
				}

				if (isset($field['values'])) {
					$fieldProperties['permittedvalues'] = $field['values'];
				}

				if (isset($field['rangefield']) &&
					$field['rangefield'] == true &&
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

	public function inRangeSearchcriteriaInfos($field)
	{
		return isset($this->_searchcriteriaRangeInfos[$field]);
	}


	/**
	 *
	 * @param string $field
	 * @return bool
	 *
	 */

	public function isUmkreisField($field)
	{
		return isset($this->_umkreisFields[$field]);
	}


	/**
	 *
	 * @param atring $field
	 * @return array
	 *
	 */

	public function getUmkreisValuesForField($field)
	{
		$infos = array();

		if (isset($this->_umkreisFields[$field])) {
			$infos = $this->_umkreisFields[$field];
		}

		return $infos;
	}


	/**
	 *
	 * @param string $field
	 * @return array
	 *
	 */

	public function getRangeSearchcriteriaInfosForField($field)
	{
		$infos = array();

		if (isset($this->_searchcriteriaRangeInfos[$field])) {
			$infos = $this->_searchcriteriaRangeInfos[$field];
		}

		return $infos;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getUmkreisFields()
	{
		return $this->_umkreisFields;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getSearchcriteriaRangeInfos()
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

	public function getModuleContainsField($fieldname, $module)
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

	public function getFieldLabel($field, $module)
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
	 *
	 * @return array
	 *
	 */

	public function getFieldList($module, $addApiOnlyFields = false, $annotated = false, $modus = null)
	{
		$fieldList = array();
		if (isset( $this->_fieldList[$module])) {
			$fieldList = $this->_fieldList[$module];
		}

		if ($addApiOnlyFields) {
			$extraFields = array();
			$extraFieldsUntranslated = $this->getExtraFields($module, $annotated);

			foreach ($extraFieldsUntranslated as $field => $options) {
				$newOptions = $options;
				$newOptions['label'] = __($options['label'], 'onoffice');
				$extraFields[$field] = $newOptions;
			}

			$fieldList = array_merge($fieldList, $extraFields);
		}

		if ($modus != null)	{
			$pGeoPosition = new GeoPosition();
			$fieldList = $pGeoPosition->transform($fieldList, $modus);
		}

		return $fieldList;
	}


	/**
	 *
	 * @param string $module
	 * @param string $annotated
	 * @return array
	 *
	 */

	private function getExtraFields($module, $annotated)
	{
		$extraFields = array();
		$hasApiFields = isset(self::$_apiReadOnlyFields[$module]);

		if ($hasApiFields) {
			$extraFields = self::$_apiReadOnlyFields[$module];
			array_walk($extraFields, function(&$array) {
				$newContent = $array['content'] ?? 'Form Specific Fields';
				$array['content'] = __($newContent, 'onoffice');
			});
		}

		if ($annotated && isset(self::$_readOnlyFieldsAnnotations[$module])) {
			$annotatedFields = array();
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

	public function getPermittedValues($inputField, $module)
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

	public function getFieldInformation($field, $module)
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
		if (isset($this->_fieldList[$module][$field])) {
			return $this->_fieldList[$module][$field];
		} elseif (isset(self::$_apiReadOnlyFields[$module][$field])) {
			return self::$_apiReadOnlyFields[$module][$field];
		}
	}


	/**
	 *
	 * @return array
	 *
	 */

	static public function getDefaultSortByFields($module)
	{
		if (isset(self::$_defaultSortByFields[$module])) {
			return self::$_defaultSortByFields[$module];
		}

		return array();
	}


	/** @return string */
	public function getLanguage()
		{ return $this->_language; }

	/** @return SDKWrapper */
	public function getSDKWrapper()
		{ return $this->_pSDKWrapper; }

	/** @param SDKWrapper $pSDKWrapper */
	public function setSDKWrapper(SDKWrapper $pSDKWrapper)
		{ $this->_pSDKWrapper = $pSDKWrapper; }
}
