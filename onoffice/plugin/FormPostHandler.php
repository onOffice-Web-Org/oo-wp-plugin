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

use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationFactory;
use onOffice\WPlugin\DataFormConfiguration\UnknownFormException;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\FormPost;

/**
 *
 */

class FormPostHandler
{
	/** @var array */
	static private $_formPostClassesByType = array(
		Form::TYPE_CONTACT => FormPostContact::class,
		Form::TYPE_OWNER => FormPostOwner::class,
		Form::TYPE_INTEREST => FormPostInterest::class,
		Form::TYPE_APPLICANT_SEARCH => FormPostApplicantSearch::class,
	);


	/** @var array */
	static private $_instances = array();


	/**
	 *
	 * @return FormPost
	 *
	 */

	static public function getInstance($type)
	{
		if (!array_key_exists($type, Form::getFormTypesLabeled())) {
			throw new UnknownFormException($type);
		}

		if (!array_key_exists($type, self::$_instances)) {
			self::create($type);
		}

		return self::$_instances[$type];
	}


	/**
	 *
	 */

	static public function initialCheck()
	{
		$formName = filter_input(INPUT_POST, 'oo_formid', FILTER_SANITIZE_STRING);
		$formNo = filter_input(INPUT_POST, 'oo_formno', FILTER_SANITIZE_NUMBER_INT);

		if ( ! is_null( $formName ) ) {
			$pDatatFormConfigFactory = new DataFormConfigurationFactory();
			$pFormConfig = $pDatatFormConfigFactory->loadByFormName($formName);
			$formType = $pFormConfig->getFormType();

			$pFormPostInstance = self::getInstance($formType);
			$pFormPostInstance->initialCheck($pFormConfig, $formNo);
		}
	}


	/**
	 *
	 * @param string $formType
	 *
	 */

	static private function create($formType)
	{
		if (isset(self::$_formPostClassesByType[$formType])) {
			$class = self::$_formPostClassesByType[$formType];
			self::$_instances[$formType] = new $class;
		} else {
			throw new Exception('Unknown Form Type');
		}
	}
}