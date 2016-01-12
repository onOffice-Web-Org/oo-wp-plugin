<?php

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

/**
 *
 */

class Form {

	/** @var Fieldnames */
	private $_pFieldnames = null;

	/** @var string */
	private $_formId = '';


	/**
	 *
	 * @param string $formId
	 *
	 */

	public function __construct( $formId ) {
		$this->_pFieldnames = new Fieldnames();
		$this->_formId = $formId;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function render() {
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
	 * @param string $field
	 *
	 * @return string
	 *
	 */

	public function getFieldLabel( $field ) {
		$config = $this->getConfigByFormId( $this->_formId );
		$language = $config['language'];
		$module = array_search($field, $config['inputs']);

		$this->_pFieldnames->getFieldLabel($field, $module, $language);
	}
}
