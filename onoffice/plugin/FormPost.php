<?php

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

class FormPost {
	/** */
	const MESSAGE_SUCCESS = 'success';

	/** */
	const MESSAGE_REQUIRED_FIELDS_MISSING = 'fieldmissing';

	/** */
	const MESSAGE_ERROR = 'error';


	/** @var FormPost */
	private static $_pInstance = null;

	/** @var int */
	private $_formNo = 0;

	/** @var array */
	private $_formDataInstances = array();


	/**
	 *
	 * @return FormPost
	 *
	 */

	public static function getInstance() {
		if ( is_null( self::$_pInstance ) ) {
			self::$_pInstance = new static;
		}

		return self::$_pInstance;
	}


	/**
	 *
	 */

	private function __construct() { }


	/**
	 *
	 */

	private function __clone() { }


	/**
	 *
	 * @param string $postvar
	 * @param int $validate one or more of the PHP FILTER_* constants
	 * @return mixed
	 *
	 */

	public static function getPostValue($postvar, $validate = FILTER_DEFAULT) {
		$value = filter_input( INPUT_POST, $postvar, $validate );

		if ( null === $value || false === $value) {
			return null;
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

	private function analyseFormContentByPrefix( $prefix, $formNo = null ) {
		$formConfig = ConfigWrapper::getInstance()->getConfigByKey( 'forms' );
		$recipient = null;
		$subject = null;

		$configByPrefix = $formConfig[$prefix];
		$formFields = $configByPrefix['inputs'];

		$formData = array_intersect_key( $_POST, $formFields );
		$pFormData = new FormData( $prefix, $formNo );
		$pFormData->setRequiredFields( $configByPrefix['required'] );
		$pFormData->setFormtype( $configByPrefix['formtype'] );

		if ( isset( $configByPrefix['recipient'] ) ) {
			$recipient = $configByPrefix['recipient'];
		}

		if ( isset( $configByPrefix['subject'] ) ) {
			$subject = $configByPrefix['subject'];
		}

		$this->_formDataInstances[$prefix][$formNo] = $pFormData;
		$pFormData->setValues( $formData );

		if ( $configByPrefix['formtype'] !== Form::TYPE_CONTACT) {
			// don't do form handling and API call if not wanted
			return;
		}

		$missingFields = $pFormData->getMissingFields();

		if ( count( $missingFields ) > 0 ) {
			$pFormData->setStatus( self::MESSAGE_REQUIRED_FIELDS_MISSING );
		} else {
			$pFormData->setFormSent( true );
			$response = $this->sendContactRequest( $pFormData, $recipient, $subject );

			if ( array_key_exists( 'createaddress', $configByPrefix ) &&
				$configByPrefix['createaddress'] ) {

				$checkDuplicate = true;
				if (array_key_exists( 'checkduplicate', $configByPrefix ) &&
					!$configByPrefix['checkduplicate']) {
					$checkDuplicate = false;
				}

				$responseNewAddress = $this->createOrCompleteAddress( $pFormData, $checkDuplicate );
				$response = $response && $responseNewAddress;
			}

			if ( true === $response ) {
				$pFormData->setStatus( self::MESSAGE_SUCCESS );
			} else {
				$pFormData->setStatus( self::MESSAGE_ERROR );
			}
		}
	}


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
	 * @param FormData $pFormData
	 * @return bool
	 *
	 */

	private function sendContactRequest( FormData $pFormData, $recipient = null, $subject = null ) {
		$addressData = $pFormData->getAddressData();
		$values = $pFormData->getValues();
		$estateId = isset( $values['Id'] ) ? $values['Id'] : null;
		$message = isset( $values['message'] ) ? $values['message'] : null;

		$requestParams = array(
			'addressdata' => $addressData,
			'estateid' => $estateId,
			'message' => $message,
			'subject' => $subject,
			'referrer' => filter_input( INPUT_SERVER, 'REQUEST_URI' ),
			'formtype' => $pFormData->getFormtype(),
		);

		if ( null != $recipient ) {
			$requestParams['recipient'] = $recipient;
		}

		$pSDKWrapper = new SDKWrapper();
		$pSDKWrapper->removeCacheInstances();

		$handle = $pSDKWrapper->addRequest(
				onOfficeSDK::ACTION_ID_DO, 'contactaddress', $requestParams );
		$pSDKWrapper->sendRequests();

		$response = $pSDKWrapper->getRequestResponse( $handle );

		$result = isset( $response['data']['records'][0]['elements']['success'] ) &&
			'success' == $response['data']['records'][0]['elements']['success'];
		return $result;
	}


	/**
	 *
	 * @param FormData $pFormData
	 * @param bool $mergeExisting
	 * @return bool
	 *
	 */

	private function createOrCompleteAddress( FormData $pFormData, $mergeExisting = false )
	{
		$requestParams = $pFormData->getAddressData();
		$requestParams['checkDuplicate'] = $mergeExisting;

		$pSDKWrapper = new SDKWrapper();
		$pSDKWrapper->removeCacheInstances();

		$handle = $pSDKWrapper->addRequest(
				onOfficeSDK::ACTION_ID_CREATE, 'address', $requestParams );
		$pSDKWrapper->sendRequests();

		$response = $pSDKWrapper->getRequestResponse( $handle );

		$result = isset( $response['data']['records'] ) &&
				count( $response['data']['records'] ) > 0;
		return $result;
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
}
