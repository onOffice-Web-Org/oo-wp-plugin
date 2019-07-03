<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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

namespace onOffice\WPlugin\Record;

use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigBase;
use stdClass;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class BooleanValueToFieldList
{
	/** @var stdClass */
	private $_pValues = null;

	/** @var InputModelDBFactory */
	private $_pInputModelFactory = null;


	/**
	 *
	 * @param InputModelDBFactoryConfigBase $pInputModelDBFactoryConfig
	 * @param stdClass $values
	 *
	 */

	public function __construct(
		InputModelDBFactoryConfigBase $pInputModelDBFactoryConfig,
		stdClass $values)
	{
		$this->_pInputModelFactory = new InputModelDBFactory($pInputModelDBFactoryConfig);
		$this->_pValues = $values;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function readFieldsArray(): array
	{
		$pInputModelFieldName = $this->_pInputModelFactory->create
			(InputModelDBFactory::INPUT_FIELD_CONFIG, '', true);
		$identifierFieldName = $pInputModelFieldName->getIdentifier();

		if (!property_exists($this->_pValues, $identifierFieldName)) {
			return [];
		}
		return $this->_pValues->$identifierFieldName;
	}


	/**
	 *
	 * @param string $type The constant from InputModelDBFactoryConfigX
	 *
	 */

	public function fillCheckboxValues(string $type)
	{
		$fieldsArray = $this->readFieldsArray();
		$pInputModel = $this->_pInputModelFactory->create($type, '', true);
		$identifier = $pInputModel->getIdentifier();

		if (property_exists($this->_pValues, $identifier)) {
			$fieldList = (array)$this->_pValues->$identifier;
			$newFieldList = array_fill_keys(array_keys($fieldsArray), '0');

			foreach ($fieldList as $hiddenField) {
				$keyIndex = array_search($hiddenField, $fieldsArray);
				$newFieldList[$keyIndex] = '1';
			}

			$this->_pValues->$identifier = $newFieldList;
		} else {
			$this->_pValues->$identifier = [];
		}
	}


	/** @return stdClass */
	public function getValues(): stdClass
		{ return $this->_pValues; }

	/** @return InputModelDBFactory */
	public function getInputModelFactory(): InputModelDBFactory
		{ return $this->_pInputModelFactory; }
}
