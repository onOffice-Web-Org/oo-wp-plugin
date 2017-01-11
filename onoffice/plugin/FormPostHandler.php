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

use onOffice\WPlugin\Form;
use onOffice\WPlugin\FormPost;
use onOffice\WPlugin\FormPostFree;
use onOffice\WPlugin\FormPostInterest;
use onOffice\WPlugin\FormPostApplicant;
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
			case Form::TYPE_CONTACT:
				self::$_instances[Form::TYPE_CONTACT] = FormPostInterest::getInstance();
				break;

			case Form::TYPE_OWNER:
				self::$_instances[Form::TYPE_OWNER] = FormPostOwner::getInstance();
				break;

			case Form::TYPE_INTEREST:
				self::$_instances[Form::TYPE_INTEREST] = FormPostApplicant::getInstance();
				break;

			default:
				self::$_instances[Form::TYPE_FREE] = FormPostFree::getInstance();
				break;
		}
	}
}

?>