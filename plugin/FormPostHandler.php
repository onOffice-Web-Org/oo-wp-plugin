<?php
/**
 *
 *    Copyright (C) 2016-2019 onOffice GmbH
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

namespace onOffice\WPlugin;

use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationFactory;
use onOffice\WPlugin\DataFormConfiguration\UnknownFormException;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\FormPost;
use const ONOFFICE_DI_CONFIG_PATH;


/**
 *
 */

class FormPostHandler
{
	/** @var array */
	const TYPE_MAPPING = [
		Form::TYPE_CONTACT => FormPostContact::class,
		Form::TYPE_OWNER => FormPostOwner::class,
		Form::TYPE_INTEREST => FormPostInterest::class,
		Form::TYPE_APPLICANT_SEARCH => FormPostApplicantSearch::class,
	];

	/** @var array */
	static private $_instances = [];

	/**
	 *
	 * @param string $type
	 * @return FormPost
	 * @throws UnknownFormException
	 * @throws DependencyException
	 * @throws NotFoundException
	 */

	static public function getInstance(string $type)
	{
		if (!isset(self::TYPE_MAPPING[$type])) {
			throw new UnknownFormException($type);
		}

		if (!isset(self::$_instances[$type])) {
			$pDIBuilder = new ContainerBuilder();
			$pDIBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
			$pDI = $pDIBuilder->build();
			self::$_instances[$type] = $pDI->make(self::TYPE_MAPPING[$type]);
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

		if ($formName !== null && $formNo !== null) {
			$pDataFormConfigFactory = new DataFormConfigurationFactory();
			$pFormConfig = $pDataFormConfigFactory->loadByFormName($formName);
			$pFormPostInstance = self::getInstance($pFormConfig->getFormType());
			$pFormPostInstance->initialCheck($pFormConfig, $formNo);
		}
	}
}