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

use onOffice\WPlugin\FormPost;
use onOffice\WPlugin\FormPostFree;
use onOffice\WPlugin\FormPostInterest;
use onOffice\WPlugin\FormPostOwner;

/**
 *
 * Description of FormPostHandler
 *
 */
class FormPostHandler
{
	/** @var array */
	static private $_instances = array();


	/**
	 *
	 * @return FormPost
	 *
	 */

	static public function getInstance()
	{
		$configByPrefix = \onOffice\WPlugin\Form::TYPE_FREE;

		if ( array_key_exists( 'oo_formid', $_POST ) )
		{
			$formNo = null;

			if ( array_key_exists( 'oo_formno', $_POST ) )
			{
				$formNo = $_POST['oo_formno'];
			}

			$formId = $_POST['oo_formid'];
			$formConfig = ConfigWrapper::getInstance()->getConfigByKey( 'forms' );
			$configByPrefix = $formConfig[$formId]['formtype'];
		}

		if (!array_key_exists($configByPrefix, self::$_instances))
		{
			self::create($configByPrefix);
		}

		return self::$_instances[$configByPrefix];
	}


	/**
	 *
	 * @param string $configByPrefix
	 *
	 */
	static private function create($configByPrefix)
	{
		switch ($configByPrefix)
		{
			case \onOffice\WPlugin\Form::TYPE_CONTACT:
				self::$_instances[Form::TYPE_CONTACT] = FormPostInterest::getInstance();
				break;

			case \onOffice\WPlugin\Form::TYPE_OWNER:
				self::$_instances[Form::TYPE_OWNER] = FormPostOwner::getInstance();
				break;

			default:
				self::$_instances[Form::TYPE_FREE] = FormPostFree::getInstance();
				break;
		}
	}
}

?>