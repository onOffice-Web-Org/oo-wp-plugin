<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

declare (strict_types=1);

namespace onOffice\WPlugin\Model\InputModel;

use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelOption;

/**
 *
 */

class InputModelDBBuilderGeneric
{
	/** @var InputModelDBFactory */
	private $_pInputModelDBFactory = null;

	/** @var InputModelConfiguration */
	private $_pInputModelConfiguration = null;

	/** @var array */
	private $_values = [];


	/**
	 *
	 * @param InputModelDBFactory $pInputModelDBFactory
	 * @param InputModelConfiguration $pInputModelConfiguration
	 *
	 */

	public function __construct(InputModelDBFactory $pInputModelDBFactory, InputModelConfiguration $pInputModelConfiguration)
	{
		$this->_pInputModelDBFactory = $pInputModelDBFactory;
		$this->_pInputModelConfiguration = $pInputModelConfiguration;
	}


	/**
	 *
	 * @param string $fieldname
	 * @return InputModelDB
	 * @throws UnknownFieldException
	 *
	 */

	public function build(string $fieldname): InputModelDB
	{
		$config = $this->_pInputModelConfiguration->getConfig();
		$fieldConfig = $config[$fieldname] ?? null;

		if ($fieldConfig === null) {
			throw new UnknownFieldException();
		}

		$label = $fieldConfig[InputModelConfiguration::KEY_LABEL];
		$pInputModel = $this->_pInputModelDBFactory->create($fieldname, $label);
		$pInputModel->setHtmlType($fieldConfig[InputModelConfiguration::KEY_HTMLTYPE]);
		$this->configureForHtmlType($pInputModel, $fieldConfig);
		return $pInputModel;
	}


	/**
	 *
	 * @param InputModelDB $pInputModel
	 * @param array $fieldConfig
	 *
	 */

	private function configureForHtmlType(InputModelDB $pInputModel, array $fieldConfig)
	{
		if ($fieldConfig[InputModelConfiguration::KEY_HTMLTYPE] === InputModelOption::HTML_TYPE_CHECKBOX) {
			$pInputModel->setValuesAvailable(1);
			$pInputModel->setValue((int)($this->_values[$pInputModel->getField()] ?? 0));
		}
	}


	/** @return array */
	public function getValues(): array
		{ return $this->_values; }

	/** @param array $values */
	public function setValues(array $values)
		{ $this->_values = $values; }
}