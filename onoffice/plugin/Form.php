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

use onOffice\WPlugin\FormData;

/**
 *
 */

class Form {
	/** choose this to create a contact form */
	const TYPE_CONTACT = 'contact';

	/** choose this if you'd like to control by yourself */
	const TYPE_FREE = 'free';

	/** choose this to create an owner form */
	const TYPE_OWNER = 'owner';

	/** @var Fieldnames */
	private $_pFieldnames = null;

	/** @var string */
	private $_formId = '';

	/** @var int */
	private $_formNo = null;

	/** @var FormData */
	private $_pFormData = null;

	/** @var string */
	private $_language = null;

	/** @var int */
	private $_pages = 1;

	/**
	 *
	 * @param string $formId
	 * @param string $language
	 *
	 */

	public function __construct( $formId, $language ) {
		$this->_language = $language;
		$this->_pFieldnames = Fieldnames::getInstance();
		$this->_pFieldnames->loadLanguage($language);
		$this->_formId = $formId;
		$pFormPost = FormPostHandler::getInstance();
		$pFormPost->incrementFormNo();
		$this->_formNo = $pFormPost->getFormNo();
		$this->_pFormData = $pFormPost->getFormDataInstance( $formId, $this->_formNo );

		// no form sent
		if ( is_null( $this->_pFormData ) ) {
			$this->_pFormData = new FormData( $formId, $this->_formNo );
		}
		$this->setPages();
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getInputFields() {
		$formConfigs = ConfigWrapper::getInstance()->getConfigByKey( 'forms' );
		$config = $formConfigs[$this->_formId];
		return $config['inputs'];
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getRequiredFields() {
		$formConfigs = ConfigWrapper::getInstance()->getConfigByKey( 'forms' );
		$config = $formConfigs[$this->_formId];
		return $config['required'];
	}


	/**
	 *
	 * @param string $field
	 * @return bool
	 *
	 */

	public function isRequiredField( $field ) {
		$requiredFields = $this->getRequiredFields();
		return in_array($field, $requiredFields);
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getConfigByFormId() {
		$formConfigs = ConfigWrapper::getInstance()->getConfigByKey( 'forms' );
		return $formConfigs[$this->_formId];
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getFormStatus() {
		return $this->_pFormData->getStatus();
	}


	/**
	 *
	 * @param string $field
	 * @param bool $raw
	 *
	 * @return string
	 *
	 */

	public function getFieldLabel( $field, $raw = false ) {
		$config = $this->getConfigByFormId( $this->_formId );
		$language = $config['language'];
		$module = $config['inputs'][$field];

		$label = $this->_pFieldnames->getFieldLabel( $field, $module, $language );

		if (false === $raw) {
			$label = esc_html($label);
		}

		return $label;
	}


	/**
	 *
	 * @param string $field
	 * @param bool $raw
	 *
	 * @return string
	 *
	 */

	public function getPermittedValues( $field, $raw = false ) {
		$config = $this->getConfigByFormId( $this->_formId );
		$language = $config['language'];
		$module = $config['inputs'][$field];

		$fieldType = $this->getFieldType( $field );
		$isMultiselectOrSingleselect = in_array( $fieldType,
			array(FieldType::FIELD_TYPE_MULTISELECT, FieldType::FIELD_TYPE_SINGLESELECT), true );

		$result = null;

		if ( $isMultiselectOrSingleselect ) {
			$result = $this->_pFieldnames->getPermittedValues( $field, $module, $language );

			if ( false === $raw ) {
				$result = $this->escapePermittedValues($result);
			}
		}

		return $result;
	}


	/**
	 *
	 * @param string $field
	 * @return string
	 *
	 */

	public function getFieldType( $field ) {
		$config = $this->getConfigByFormId( $this->_formId );
		$language = $config['language'];
		$module = $config['inputs'][$field];

		$fieldType = $this->_pFieldnames->getType( $field, $module, $language );
		return $fieldType;
	}


	/**
	 *
	 * @param array $keyValues
	 * @return array
	 *
	 */

	private function escapePermittedValues( array $keyValues ) {
		$result = array();

		foreach ( $keyValues as $key => $value ) {
			$result[esc_html( $key )] = esc_html( $value );
		}

		return $result;
	}


	/**
	 *
	 * @param string $field
	 * @param bool $raw
	 * @return string
	 *
	 */

	public function getFieldValue( $field, $raw = false, $forceEvenIfSuccess = false ) {
		$values = $this->_pFormData->getValues();
		$fieldValue = isset( $values[$field] ) ? $values[$field] : '';

		if ( $this->_pFormData->getFormSent() && !$forceEvenIfSuccess ) {
			return '';
		}

		if ( $raw ) {
			return $fieldValue;
		} else {
			return esc_html( $fieldValue );
		}
	}


	/**
	 *
	 * @param string $field
	 * @param string $message
	 * @return string
	 *
	 */

	public function getMessageForField( $field, $message ) {
		if ( in_array($field, $this->_pFormData->getMissingFields(), true ) ) {
			return esc_html($message);
		}
		return null;
	}


	/**
	 *
	 * @param string $field
	 * @return bool
	 *
	 */

	public function isMissingField( $field ) {
		return ! $this->_pFormData->getFormSent() &&
			in_array( $field, $this->_pFormData->getMissingFields(), true );
	}


	/**
	 *
	 * @return int
	 *
	 */

	public function getFormNo() {
		return esc_html($this->_formNo);
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getFormId() {
		return esc_html($this->_formId);
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getLanguage() {
		return $this->_language;
	}


	/**
	 *
	 */

	private function setPages() {
		$config = $this->getConfigByFormId( $this->_formId );

		if (isset($config['pages']))
		{
			if ($config['pages'] > 0)
			{
				$this->_pages = $config['pages'];
			}
		}
	}


	/** @return int */
	public function getPages()
	{ return $this->_pages; }
}