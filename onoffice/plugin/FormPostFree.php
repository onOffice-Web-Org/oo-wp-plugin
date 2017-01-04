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