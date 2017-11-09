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
	/** @var Fieldnames */
	static private $_pInstance = null;

	/** @var array */
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
	private $_fieldList = array();

	/** @var array */
	private $_searchcriteriaRangeInfos = array();

	/** @var array */
	private $_umkreisFields = array();

	/**
	 *
	 */

	private function __construct() {}


	/**
	 *
	 */

	private function __clone() {}


	/**
	 *
	 * @return Fieldnames
	 *
	 */

	static public function getInstance() {
		if (is_null(self::$_pInstance)) {
			self::$_pInstance = new static();
		}

		return self::$_pInstance;
	}


	/**
	 *
	 * @param string $language
	 *
	 */

	public function loadLanguage( $language ) {
		if ( $this->hasLanguageCached( $language ) ) {
			return;
		}

		$parametersGetFieldList = array(
			'labels' => 1,
			'language' => $language,
			'modules' => array
				(
					'address',
					'estate',
				),
		);

		$pSDKWrapper = new SDKWrapper();
		$handleGetFields = $pSDKWrapper->addRequest(
			onOfficeSDK::ACTION_ID_GET, 'fields', $parametersGetFieldList );
		$pSDKWrapper->sendRequests();

		$responseArrayFieldList = $pSDKWrapper->getRequestResponse( $handleGetFields );
		$fieldList = $responseArrayFieldList['data']['records'];

		$this->createFieldList( $fieldList, $language );
		$this->completeFieldListWithSearchcriteria( $language );
	}


	/**
	 *
	 * @param string $language
	 *
	 */

	private function completeFieldListWithSearchcriteria( $language ) {
		$pSDKWrapper = new SDKWrapper();
		$pSDKWrapper->removeCacheInstances();
		$requestParameter = array
			(
				'language' => $language,
				'additionalTranslations' => true,
			);

		$handle = $pSDKWrapper->addRequest(
				onOfficeSDK::ACTION_ID_GET, 'searchCriteriaFields', $requestParameter);
		$pSDKWrapper->sendRequests();

		$response = $pSDKWrapper->getRequestResponse( $handle );

		foreach ($response['data']['records'] as $tableValues)
		{
			$felder = $tableValues['elements'];

			foreach ($felder['fields'] as $field)
			{
				$fieldProperties = array();
				$fieldProperties['type'] = $field['type'];
				$fieldProperties['label'] = $field['name'];
				$fieldProperties['default'] = null;
				$fieldProperties['permittedvalues'] = array();

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

				if ($felder['name'] == 'Umkreis')
				{
					$this->_umkreisFields[$field['id']] = $fieldProperties;
				}

				$this->_fieldList[$language]['searchcriteria'][$field['id']] = $fieldProperties;
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
	 * @param string $language
	 *
	 */

	public function loadLanguageIfNotCached( $language ) {
		if ( ! $this->hasLanguageCached( $language ) ) {
			$this->loadLanguage( $language );
		}
	}


	/**
	 *
	 * @param array $fieldResult
	 * @param string $language
	 * @return null
	 *
	 */

	private function createFieldList( $fieldResult, $language ) {
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

				$this->_fieldList[$language][$moduleProperties['id']][$fieldName] = $fieldProperties;
			}
		}
	}


	/**
	 *
	 * @param string $field
	 * @param string $module recordType
	 * @param string $language
	 *
	 * @return string
	 *
	 */

	public function getFieldLabel( $field, $module, $language ) {
		$fieldNewName = $field;

		if ( isset( $this->_fieldList[$language][$module] ) &&
			array_key_exists( $field, $this->_fieldList[$language][$module] ) ) {
			$fieldNewName = $this->_fieldList[$language][$module][$field]['label'];
		}

		return $fieldNewName;
	}


	/**
	 *
	 * @param string $module recordType
	 * @param string $language
	 *
	 * @return array
	 *
	 */

	public function getFieldList( $module, $language, $addApiOnlyFields = false, $annotated = false ) {
		$fieldList = array();
		if ( isset( $this->_fieldList[$language][$module] ) ) {
			$fieldList = $this->_fieldList[$language][$module];
		}

		if ($addApiOnlyFields) {
			$extraFields = $this->getExtraFields($module, $annotated);
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
	 * @param string $language
	 * @return string
	 *
	 */

	public function getType( $fieldName, $module, $language ) {
		return $this->_fieldList[$language][$module][$fieldName]['type'];
	}


	/**
	 *
	 * @param string $inputField
	 * @param string $module
	 * @param string $language
	 * @return string
	 *
	 */

	public function getPermittedValues( $inputField, $module, $language ) {
		return $this->_fieldList[$language][$module][$inputField]['permittedvalues'];
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
}
