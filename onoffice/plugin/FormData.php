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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;

/**
 *
 */

class FormData {
	/** @var int */
	private $_formNo = null;

	/** @var array */
	private $_requiredFields = array();

	/** @var array */
	private $_values = array();

	/** @var string */
	private $_status = null;

	/** @var bool */
	private $_formSent = false;

	/** @var string */
	private $_formtype = null;

	/** @var array */
	private $_configFields = array();

	/** @var array */
	private $_responseFieldsValues = array();

	/** @var DataFormConfiguration */
	private $_pDataFormConfiguration = null;


	/**
	 *
	 * @param DataFormConfiguration $pDataFormConfiguration
	 * @param int $formNo
	 *
	 */

	public function __construct( DataFormConfiguration $pDataFormConfiguration, $formNo ) {
		$this->_pDataFormConfiguration = $pDataFormConfiguration;
		$this->_formNo = $formNo;
		$this->_configFields = $this->_pDataFormConfiguration->getInputs();
	}


	/**
	 *
	 * @param array $configFields
	 *
	 */

	public function setConfigFields($configFields)	{
		$this->_configFields = $configFields;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getMissingFields() {
		$missing = array();

		if ( $this->_formSent ) {
			$filledFormData = array_filter( $this->_values );
			$requiredFields = array_flip( $this->_requiredFields );
			$filled = array_intersect_key( $filledFormData, $requiredFields );
			$missing = array_diff_key( $requiredFields, $filled );
			$missing = array_keys( $missing );
		}

		return $missing;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getAddressData() {
		$inputs = $this->_configFields;
		$addressData = array();

		foreach ($this->_values as $input => $value) {
			if ('address' === $inputs[$input]) {
				$addressData[$input] = $value;
			}
		}
		return $addressData;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getAddressDataForApiCall() {
		$inputs = $this->_configFields;
		$addressData = array();

		foreach ($this->_values as $input => $value) {
			if ('address' === $inputs[$input]) {

				switch ($input)
				{
					case 'Telefon1':
						$input = 'phone';
						break;

					case 'Email':
						$input = 'email';
						break;

					case 'Telefax1':
						$input = 'fax';
						break;
				}

				$addressData[$input] = $value;
			}
		}

		return $addressData;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getEstateData() {
		$inputs = $this->_configFields;
		$estateData = array();

		foreach ($this->_values as $input => $value) {
			if ('estate' === $inputs[$input]) {
				$estateData[$input] = $value;
			}
		}
		return $estateData;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getSearchcriteriaData() {
		$inputs = $this->_configFields;
		$searchcriteriaData = array();

		foreach ($this->_values as $input => $value) {
			if (onOfficeSDK::MODULE_SEARCHCRITERIA === $inputs[$input]) {
				$searchcriteriaData[$input] = $value;
			}
		}

		return $searchcriteriaData;
	}


	/** @param string[] $missingFields */
	public function setRequiredFields( array $missingFields )
		{ $this->_requiredFields = $missingFields; }

	/** @return array */
	public function getRequiredFields()
		{ return $this->_requiredFields; }

	/** @param array $values */
	public function setValues( array $values )
		{ $this->_values = $values; }

	/** @return array */
	public function getValues()
		{ return $this->_values; }

	/** @param string $status */
	public function setStatus( $status )
		{ $this->_status = $status; }

	/** @return string */
	public function getStatus()
		{ return $this->_status; }

	/**	@param bool $formSent Whether the Form was sent using GET or POST yet */
	public function setFormSent( $formSent )
		{ $this->_formSent = (bool) $formSent; }

	/** @return bool */
	public function getFormSent()
		{ return $this->_formSent; }

	/** @param string $formtype */
	public function setFormtype($formtype)
		{ $this->_formtype = $formtype; }

	/** @return string */
	public function getFormtype()
		{ return $this->_formtype; }

	/** @param array $values */
	public function setResponseFieldsValues($values)
		{ $this->_responseFieldsValues = $values; }

	/** @return array */
	public function getResponseFieldsValues()
		{ return $this->_responseFieldsValues; }

	/** @return DataFormConfiguration */
	public function getDataFormConfiguration()
		{ return $this->_pDataFormConfiguration; }

	/** @return int */
	public function getFormNo()
		{ return $this->_formNo; }
}
