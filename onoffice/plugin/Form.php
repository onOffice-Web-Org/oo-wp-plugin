<?php

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

	/** @var Fieldnames */
	private $_pFieldnames = null;

	/** @var string */
	private $_formId = '';

	/** @var int */
	private $_formNo = null;

	/** @var FormData */
	private $_pFormData = null;


	/**
	 *
	 * @param string $formId
	 * @param string $language
	 *
	 */

	public function __construct( $formId, $language ) {
		$this->_pFieldnames = new Fieldnames();
		$this->_pFieldnames->loadLanguage($language);
		$this->_formId = $formId;
		$pFormPost = FormPost::getInstance();
		$pFormPost->incrementFormNo();
		$this->_formNo = $pFormPost->getFormNo();
		$this->_pFormData = $pFormPost->getFormDataInstance( $formId, $this->_formNo );

		// no form sent
		if ( is_null( $this->_pFormData ) ) {
			$this->_pFormData = new FormData( $formId, $this->_formNo );
		}
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
}
