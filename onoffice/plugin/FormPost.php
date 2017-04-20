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

use onOffice\WPlugin\FormData;
use onOffice\SDK\onOfficeSDK;

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
	private $_formNo = 0;

	/** @var array */
	private $_formDataInstances = array();

	/** @var array */
	private $_searchcriteriaRangeFields = array();


	/**
	 *
	 */

	private function __clone() { }


	/**
	 */
	abstract protected function getFormType();

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
	 */

	public function initialCheck() {
		if ( array_key_exists( 'oo_formid', $_POST ) ) {
			$formNo = null;

			if ( array_key_exists( 'oo_formno', $_POST ) ) {
				$formNo = $_POST['oo_formno'];
			}

			$this->analyseFormContentByPrefix( $_POST['oo_formid'], $formNo );
		}
	}


	/**
	 *
	 * @param string $prefix
	 * @param int $formNo
	 *
	 */

	abstract protected function analyseFormContentByPrefix( $prefix, $formNo = null );


	/**
	 *
	 * @param string $prefix
	 * @param int $formNo
	 * @return FormData
	 *
	 */

	public function getFormDataInstance( $prefix, $formNo ) {
		if ( isset( $this->_formDataInstances[$prefix][$formNo] ) ) {
			return $this->_formDataInstances[$prefix][$formNo];
		}

		return null;
	}



	/**
	 *
	 * @param string $prefix
	 * @param string $formNo
	 * @param FormData $pFormData
	 *
	 */
	public function setFormDataInstances( $prefix, $formNo, $pFormData ){
		$this->_formDataInstances[$prefix][$formNo] = $pFormData;
	}

	/**
	 *
	 */

	public function incrementFormNo() {
		$this->_formNo++;
	}


	/**
	 *
	 * @return int
	 *
	 */

	public function getFormNo() {
		return $this->_formNo;
	}


	/**
	 *
	 */

	public function resetFormNo() {
		$this->_formNo = 0;
	}


	/**
	 *
	 * @param array $inputFormFields
	 * @return array
	 *
	 */

	protected function getFormFieldsConsiderSearchcriteria($inputFormFields) {
		$pSDKWrapper = new SDKWrapper();
		$pSDKWrapper->removeCacheInstances();

		$handle = $pSDKWrapper->addRequest(
				onOfficeSDK::ACTION_ID_GET, 'searchCriteriaFields');
		$pSDKWrapper->sendRequests();

		$response = $pSDKWrapper->getRequestResponse( $handle );

		foreach ($response['data']['records'] as $tableValues)
		{
			$felder = $tableValues['elements'];

			// new
			if ($felder['name'] == 'Umkreis' &&
				array_key_exists('Umkreis', $inputFormFields))
			{
				unset($inputFormFields['Umkreis']);

				foreach ($felder['fields'] as $field)
				{
					$inputFormFields[$field['id']] = 'searchcriteria';
				}
			}
			else
			{
				foreach ($felder['fields'] as $field)
				{
					if (array_key_exists('rangefield', $field) &&
						$field['rangefield'] == true)
					{
						if (array_key_exists($field['id'], $inputFormFields))
						{
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
		if (array_key_exists($fieldVonBis, $this->_searchcriteriaRangeFields))
		{
			return true;
		}

		return false;
	}


	/**
	 *
	 * @param string $field
	 * @return null|string
	 *
	 */

	protected function getVonRangeFieldname($field)
	{
		if (in_array($field, $this->_searchcriteriaRangeFields))
		{
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
		if (in_array($field, $this->_searchcriteriaRangeFields))
		{
			return $field.self::RANGE_BIS;
		}

		return null;
	}

	
	/**
	 *
	 * @return array
	 *
	 */

	protected function getSearchcriteriaRangeFields()
	{ return $this->_searchcriteriaRangeFields; }
}
