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

use onOffice\WPlugin\SDKWrapper;
use onOffice\SDK\onOfficeSDK;

/**
 *
 */

class Fieldnames {

	/**
	 *
	 * @var array
	 * "label" key needs to be translated
	 *
	 */

	private static $_apiReadOnlyFields = array(
		onOfficeSDK::MODULE_ADDRESS => array(
			// parameter => label (english)
			'imageUrl' => array(
				'type' => 'freetext',
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'Image',
			),
			'phone' => array(
				'type' => 'freetext',
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'Phone',
			),
			'email' => array(
				'type' => 'freetext',
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'E-Mail',
			),
			'fax' => array(
				'type' => 'freetext',
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'Fax',
			),
			'mobile' => array(
				'type' => 'freetext',
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'Mobile',
			),
			'defaultphone' => array(
				'type' => 'freetext',
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'Phone',
			),
			'defaultemail' => array(
				'type' => 'freetext',
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'E-Mail',
			),
			'defaultfax' => array(
				'type' => 'freetext',
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'Fax',
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
	);

	/** @var array */
	private static $_defaultSortByFields = array(
		'kaufpreis',
		'kaltmiete',
		'pacht',
		'wohnflaeche',
		'anzahl_zimmer',
		'ort',
		'grundstuecksflaeche',
		'gesamtflaeche',
	);

	/** @var array */
	private $_fieldList = array();

	/** @var array */
	private $_searchcriteriaRangeInfos = array();

	/** @var array */
	private $_umkreisFields = array();

	/** @var string */
	private $_language = null;

	/**
	 *
	 * @param string $language
	 *
	 */

	public function __construct($language = null)
	{
		if ($language == null)
		{
			$this->_language = Language::getDefault();
		}
		else
		{
			$this->_language = $language;
		}
	}


	/**
	 *
	 * @param bool $showOnlyInactive
	 *
	 */

	public function loadLanguage( $showOnlyInactive = false ) {

		$parametersGetFieldList = array(
			'labels' => 1,
			'showContent' => 1,
			'showTable' => 1,
			'language' => $this->_language,
			'modules' => array
				(
					'address',
					'estate',
				),
		);

		if ($showOnlyInactive)
		{
			$parametersGetFieldList['showOnlyInactive'] = true;
		}

		$pSDKWrapper = new SDKWrapper();
		$handleGetFields = $pSDKWrapper->addRequest(
			onOfficeSDK::ACTION_ID_GET, 'fields', $parametersGetFieldList );
		$pSDKWrapper->sendRequests();

		$responseArrayFieldList = $pSDKWrapper->getRequestResponse( $handleGetFields );
		$fieldList = $responseArrayFieldList['data']['records'];

		$this->createFieldList( $fieldList );
		$this->completeFieldListWithSearchcriteria();
	}


	/**
	 *
	 */

	private function completeFieldListWithSearchcriteria( ) {
		$pSDKWrapper = new SDKWrapper();
		$requestParameter = array
			(
				'language' => $this->_language,
				'additionalTranslations' => true,
			);

		$handle = $pSDKWrapper->addRequest(
				onOfficeSDK::ACTION_ID_GET, 'searchCriteriaFields', $requestParameter);
		$pSDKWrapper->sendRequests();

		$response = $pSDKWrapper->getRequestResponse( $handle );

		foreach ($response['data']['records'] as $tableValues)
		{
			$fields = $tableValues['elements'];

			foreach ($fields['fields'] as $field)
			{
				$fieldProperties = array();
				$fieldProperties['type'] = $field['type'];
				$fieldProperties['label'] = $field['name'];
				$fieldProperties['default'] = null;
				$fieldProperties['permittedvalues'] = array();
				$fieldProperties['content'] = __('Search Criteria', 'onoffice');
				$fieldProperties['module'] = onOfficeSDK::MODULE_SEARCHCRITERIA;

				if (array_key_exists('default', $field))
				{
					$fieldProperties['default'] = $field['default'];
				}

				if (array_key_exists('values', $field))
				{
					$fieldProperties['permittedvalues'] = $field['values'];
				}

				if (array_key_exists('rangefield', $field) &&
					$field['rangefield'] == true &&
					array_key_exists('additionalTranslations', $field))
				{
					$this->_searchcriteriaRangeInfos[$field['id']] = array();

					foreach ($field['additionalTranslations'] as $key => $value)
					{
						$this->_searchcriteriaRangeInfos[$field['id']][$key] = $value;
					}
				}

				if ($fields['name'] == 'Umkreis')
				{
					$this->_umkreisFields[$field['id']] = $fieldProperties;
				}

				$this->_fieldList[$this->_language]['searchcriteria'][$field['id']] = $fieldProperties;
			}
		}
	}


	/**
	 *
	 * @param string $field
	 * @return bool
	 *
	 */

	public function inRangeSearchcriteriaInfos($field)	{
		return array_key_exists($field, $this->_searchcriteriaRangeInfos);
	}


	/**
	 *
	 * @param string $field
	 * @return bool
	 *
	 */

	public function isUmkreisField($field){
		return array_key_exists($field, $this->_umkreisFields);
	}


	/**
	 *
	 * @param atring $field
	 * @return array
	 *
	 */

	public function getUmkreisValuesForField($field){

		$infos = array();

		if (array_key_exists($field, $this->_umkreisFields))
		{
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

	public function getRangeSearchcriteriaInfosForField($field)	{
		$infos = array();

		if (array_key_exists($field, $this->_searchcriteriaRangeInfos))
		{
			$infos = $this->_searchcriteriaRangeInfos[$field];
		}

		return $infos;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getUmkreisFields(){
		return $this->_umkreisFields;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getSearchcriteriaRangeInfos() {
		return $this->_searchcriteriaRangeInfos;
	}


	/**
	 *
	 * @param array $fieldResult
	 * @return null
	 *
	 */

	private function createFieldList($fieldResult) {
		if ( count( $fieldResult ) == 0 ) {
			return;
		}

		foreach ( $fieldResult as $moduleProperties ) {
			if ( ! array_key_exists( 'elements', $moduleProperties ) ) {
				continue;
			}

			foreach ( $moduleProperties['elements'] as $fieldName => $fieldProperties ) {
				if ( 'label' == $fieldName ) {
					continue;
				}

				$fieldProperties['module'] = $moduleProperties['id'];
				$fieldProperties['content'] = $moduleProperties['elements'][$fieldName]['content'];
				$this->_fieldList[$this->_language][$moduleProperties['id']][$fieldName] = $fieldProperties;
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

	public function getModuleContainsField($fieldname, $module) {
		return isset($this->_fieldList[$this->_language][$module][$fieldname]);
	}


	/**
	 *
	 * @param string $field
	 * @param string $module recordType
	 *
	 * @return string
	 *
	 */

	public function getFieldLabel( $field, $module) {
		$fieldNewName = $field;

		if ( isset( $this->_fieldList[$this->_language][$module] ) &&
			array_key_exists( $field, $this->_fieldList[$this->_language][$module] ) ) {
			$fieldNewName = $this->_fieldList[$this->_language][$module][$field]['label'];
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

	public function getFieldList( $module, $addApiOnlyFields = false, $annotated = false ) {
		$fieldList = array();
		if ( isset( $this->_fieldList[$this->_language][$module] ) ) {
			$fieldList = $this->_fieldList[$this->_language][$module];
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

		return $fieldList;
	}


	/**
	 *
	 * @param string $module
	 * @param string $annotated
	 * @return array
	 *
	 */

	private function getExtraFields($module, $annotated) {
		$extraFields = array();
		$hasApiFields = array_key_exists($module, self::$_apiReadOnlyFields);

		if ($hasApiFields) {
			$extraFields = self::$_apiReadOnlyFields[$module];
		}

		if ($annotated && array_key_exists($module, self::$_readOnlyFieldsAnnotations)) {
			$annotatedFields = array();
			foreach ($extraFields as $field => $option) {
				if (array_key_exists($field, self::$_readOnlyFieldsAnnotations[$module])) {
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

	public function getType( $fieldName, $module) {
		return $this->_fieldList[$this->_language][$module][$fieldName]['type'];
	}


	/**
	 *
	 * @param string $inputField
	 * @param string $module
	 * @return string
	 *
	 */

	public function getPermittedValues( $inputField, $module ) {
		return $this->_fieldList[$this->_language][$module][$inputField]['permittedvalues'];
	}


	/**
	 *
	 * @param string $language
	 * @return bool
	 *
	 */

	public function hasLanguageCached( $language ) {
		return array_key_exists( $language, $this->_fieldList );
	}


	/**
	 *
	 * @return array
	 *
	 */

	static public function getDefaultSortByFields()
	{
		return self::$_defaultSortByFields;
	}
}
