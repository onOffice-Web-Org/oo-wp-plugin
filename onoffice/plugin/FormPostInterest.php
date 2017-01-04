<?php
/**
 *
 * @version $Id$
 *
 * @author Expression author is undefined on line 7, column 14 in Templates/Scripting/EmptyPHP.php.
 * @url http://www.onoffice.de
 * @copyright 2003-2016, onOffice(R) Software AG
 *
 * @package
 *
 */

namespace onOffice\WPlugin;

use onOffice\WPlugin\Form;
use onOffice\WPlugin\FormData;
use onOffice\WPlugin\FormPost;
use onOffice\SDK\onOfficeSDK;

/**
 *
 * Description of FormPostInterest
 *
 */
class FormPostInterest
	extends FormPost
{
	/** @var FormPost */
	private static $_pInstance = null;

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


	/**	 */
	private function __construct() { }


	/** @return string */
	protected function getFormType()
	{ return Form::TYPE_CONTACT; }


	/**
	 *
	 * @param string $prefix
	 * @param int $formNo
	 *
	 */

	protected function analyseFormContentByPrefix( $prefix, $formNo = null ){
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

		$this->setFormDataInstances($prefix, $formNo, $pFormData);
		$pFormData->setValues( $formData );

		$missingFields = $pFormData->getMissingFields();

		if ( count( $missingFields ) > 0 ) {
			$pFormData->setStatus(FormPost::MESSAGE_REQUIRED_FIELDS_MISSING );
		} else {
			if ( array_key_exists( 'createaddress', $configByPrefix ) &&
				$configByPrefix['createaddress'] ) {
				$checkDuplicate = true;
				if (array_key_exists( 'checkduplicate', $configByPrefix ) &&
					!$configByPrefix['checkduplicate']) {
					$checkDuplicate = false;
				}
				$responseNewAddress = $this->createOrCompleteAddress( $pFormData, $checkDuplicate );
				$response = $responseNewAddress;
			} else	{
				$response = true;
			}

			$pFormData->setFormSent( true );
			$response = $this->sendContactRequest( $pFormData, $recipient, $subject ) && $response;

			if ( true === $response ) {
				$pFormData->setStatus( FormPost::MESSAGE_SUCCESS );
			} else {
				$pFormData->setStatus( FormPost::MESSAGE_ERROR );
			}
		}
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

}

?>