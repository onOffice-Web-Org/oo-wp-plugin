<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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

namespace onOffice\WPlugin\Model\FormModelBuilder;

use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigEstate;
use onOffice\WPlugin\TemplateCall;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

abstract class FormModelBuilderEstate
	extends FormModelBuilder
{
	/** @var InputModelDBFactory */
	private $_pInputModelDBFactory = null;

	/**
	 *
	 * @param string $pageSlug
	 *
	 */

	public function __construct($pageSlug)
	{
		parent::__construct($pageSlug);
		$pConfig = new InputModelDBFactoryConfigEstate();
		$this->_pInputModelDBFactory = new InputModelDBFactory($pConfig);
	}


	/**
	 *
	 * @return array
	 *
	 */

	protected function readExposes()
	{
		$pTemplateCall = new TemplateCall(TemplateCall::TEMPLATE_TYPE_EXPOSE);
		return $pTemplateCall->getTemplates();
	}


	/**
	 *
	 * @return InputModelDBFactory
	 *
	 */

	protected function getInputModelDBFactory() {
		return $this->_pInputModelDBFactory;
	}
}
