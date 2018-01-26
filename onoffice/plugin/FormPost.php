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
 * @copyright 2003-2016, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

use Exception;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\FormData;

/**
 *
 *
 * Terminology used in this class:
 *
 * - prefix: the prefix of the input form. Must be the name of a config index for a form
 * - form No.: Every input's name consists of the prefix + form no to make multiple forms on
 *				one page possible.
 *				The Form No must be incremented at every new form output.
 *
 *
 */

abstract class FormPost {
	/** */
	const MESSAGE_SUCCESS = 'success';

	/** */
	const MESSAGE_REQUIRED_FIELDS_MISSING = 'fieldmissing';

	/** */
	const MESSAGE_ERROR = 'error';

	/** */
	const RANGE_VON = '__von';

	/** */
	const RANGE_BIS = '__bis';


	/** @var int */
	private static $_formNo = 0;

	/** @var array */
	private $_formDataInstances = array();

	/** @var array */
	private $_searchcriteriaRangeFields = array();

	/** @var FormPost */
	private static $_pInstances = null;


	/**
	 *
	 * @return FormPost
	 *
	 */

	public static function getInstance() {
		$formType = static::getFormType();
		if ( !isset( self::$_pInstances[$formType] ) ) {
			self::$_pInstances[$formType] = new static;
		}

		return self::$_pInstances[$formType];
	}


	/**
	 *
	 */

	protected function __construct() { }


	/**
	 *
	 */

	protected function __clone() { }


	/**
	 *
	 * Use it like this: static::getFormType()
	 * @throws Exception
	 *
	 */

	static protected function getFormType()
	{
		throw new Exception('getFormType must be overridden');
	}


	/**
	 *
	 * @param string $postvar
	 * @param int $validate one or more of the PHP FILTER_* constants
	 * @return mixed
	 *
	 */

	public static function getPostValue($postvar, $validate = FILTER_DEFAULT) {
		$value = filter_input( INPUT_POST, $postvar, $validate );

		if ( false === $value ) {
			$value = null;
		}

		return $value;
	}


	/**
	 *
	 * @param string $getVar
	 * @param int $validate one or more of the PHP FILTER_* constants
	 * @return mixed
	 *
	 */

	public static function getGetValue($getVar, $validate = FILTER_DEFAULT, $whitelist = true) {
		$value = filter_input( INPUT_GET, $getVar, $validate );

		if ( false === $value ) {
			$value = null;
		}

		if ( $whitelist ) {
			SearchParameters::getInstance()->addAllowedGetParameter( $getVar );
		}

		return $value;
	}


	/**
	 *
	 * @param DataFormConfiguration $pConfig
	 * @param int $formNo
	 *
	 */

	public function initialCheck(DataFormConfiguration $pConfig, $formNo = null)
	{
		$pFormData = $this->buildFormData( $pConfig, $formNo );
		$pFormData->setFormSent(true);
		$this->setFormDataInstances( $pFormData );
		$this->analyseFormContentByPrefix( $pFormData );
	}


	/**
	 *
	 * @param DataFormConfiguration $pFormConfig
	 * @param int $formNo
	 * @return FormData
	 *
	 */

	protected function buildFormData(DataFormConfiguration $pFormConfig, $formNo)
	{
		$formFields = $pFormConfig->getInputs();
		$formData = array_intersect_key( $_POST, $formFields );
		$pFormData = new FormData( $pFormConfig, $formNo );
		$pFormData->setRequiredFields( $pFormConfig->getRequiredFields() );
		$pFormData->setFormtype( $pFormConfig->getFormType() );
		$pFormData->setValues( $formData );

		return $pFormData;
	}


	/**
	 *
	 * @param FormData $pFormData
	 *
	 */

	abstract protected function analyseFormContentByPrefix(FormData $pFormData);


	/**
	 *
	 * @param string $prefix
	 * @param int $formNo
	 * @return FormData
	 *
	 */

	public function getFormDataInstance( $prefix, $formNo )
	{
		if ( isset( $this->_formDataInstances[$prefix][$formNo] ) ) {
			return $this->_formDataInstances[$prefix][$formNo];
		}

		return null;
	}


	/**
	 *
	 * @param FormData $pFormData
	 *
	 */

	public function setFormDataInstances( $pFormData )
	{
		$formNo = $pFormData->getFormNo();
		$prefix = $pFormData->getDataFormConfiguration()->getFormName();
		$this->_formDataInstances[$prefix][$formNo] = $pFormData;
	}


	/**
	 *
	 */

	public static function incrementFormNo()
	{
		self::$_formNo++;
	}


	/**
	 *
	 * @return int
	 *
	 */

	public function getFormNo()
	{
		return self::$_formNo;
	}


	/**
	 *
	 * @param array $inputFormFields
	 * @return array
	 *
	 */

	protected function getFormFieldsConsiderSearchcriteria($inputFormFields)
	{
		$pSDKWrapper = new SDKWrapper();
		$pSDKWrapper->removeCacheInstances();

		$handle = $pSDKWrapper->addRequest(
				onOfficeSDK::ACTION_ID_GET, 'searchCriteriaFields');
		$pSDKWrapper->sendRequests();

		$response = $pSDKWrapper->getRequestResponse( $handle );

		foreach ($response['data']['records'] as $tableValues) {
			$felder = $tableValues['elements'];

			// new
			if ($felder['name'] == 'Umkreis' &&
				array_key_exists('Umkreis', $inputFormFields)) {
				unset($inputFormFields['Umkreis']);

				foreach ($felder['fields'] as $field) {
					$inputFormFields[$field['id']] = 'searchcriteria';
				}
			} else {
				foreach ($felder['fields'] as $field)
				{
					if (array_key_exists('rangefield', $field) &&
						$field['rangefield'] == true) {
						if (array_key_exists($field['id'], $inputFormFields)) {
							unset($inputFormFields[$field['id']]);

							$inputFormFields[$field['id'].self::RANGE_VON] = 'searchcriteria';
							$inputFormFields[$field['id'].self::RANGE_BIS] = 'searchcriteria';

							$this->_searchcriteriaRangeFields[$field['id'].self::RANGE_VON] = $field['id'];
							$this->_searchcriteriaRangeFields[$field['id'].self::RANGE_BIS] = $field['id'];
						}
					}
				}
			}
		}

		return $inputFormFields;
	}


	/**
	 *
	 * @param string $fieldVonBis
	 * @return boolean
	 *
	 */

	protected function isSearchcriteriaRangeField($fieldVonBis)
	{
		return array_key_exists($fieldVonBis, $this->_searchcriteriaRangeFields);
	}


	/**
	 *
	 * @param string $field
	 * @return null|string
	 *
	 */

	protected function getVonRangeFieldname($field)
	{
		if (in_array($field, $this->_searchcriteriaRangeFields)) {
			return $field.self::RANGE_VON;
		}

		return null;
	}


	/**
	 *
	 * @param string $field
	 * @return null|string
	 *
	 */

	protected function getBisRangeFieldname($field)
	{
		if (in_array($field, $this->_searchcriteriaRangeFields)) {
			return $field.self::RANGE_BIS;
		}

		return null;
	}


	/** @return array */
	protected function getSearchcriteriaRangeFields()
		{ return $this->_searchcriteriaRangeFields; }
}
