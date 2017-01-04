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
use onOffice\WPlugin\FormPost;
use onOffice\WPlugin\FormData;

/**
 *
 * Description of FormPostFree
 *
 */
class FormPostFree
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


	/**
	 *
	 */

	private function __construct() { }


	/** @return string */
	protected function getFormType()
	{ return Form::TYPE_FREE; }


	/**
	 *
	 * @param string $prefix
	 * @param int $formNo
	 *
	 */

	protected function analyseFormContentByPrefix( $prefix, $formNo = null )
	{
		$formConfig = ConfigWrapper::getInstance()->getConfigByKey( 'forms' );

		$configByPrefix = $formConfig[$prefix];
		$formFields = $configByPrefix['inputs'];

		$formData = array_intersect_key( $_POST, $formFields );
		$pFormData = new FormData( $prefix, $formNo );
		$pFormData->setRequiredFields( $configByPrefix['required'] );
		$pFormData->setFormtype( $this->getFormType() );

		$this->setFormDataInstances($prefix, $formNo, $pFormData);
		$pFormData->setValues( $formData );
	}
}
?>