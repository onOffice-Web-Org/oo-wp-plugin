<?php

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

	/** @var array */
	private $_fieldList = array();


	/**
	 *
	 */

	public function __construct() {}


	/**
	 *
	 * @param string $language
	 *
	 */

	public function loadLanguage( $language ) {
		$parametersGetFieldList = array(
			'labels' => 1,
			'language' => $language,
		);

		$pSDKWrapper = new SDKWrapper();
		$handleGetFields = $pSDKWrapper->addRequest(
			onOfficeSDK::ACTION_ID_GET, 'fields', $parametersGetFieldList );
		$pSDKWrapper->sendRequests();

		$responseArrayFieldList = $pSDKWrapper->getRequestResponse( $handleGetFields );
		$fieldList = $responseArrayFieldList['data']['records'];

		$this->createFieldList( $fieldList, $language );
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
