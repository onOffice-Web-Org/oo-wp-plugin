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

class StringValueToFieldList
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
		$pInputModelFieldName = $this->_pInputModelFactory->create(InputModelDBFactory::INPUT_FIELD_CONFIG, '', true);
		$identifierFieldName = $pInputModelFieldName->getIdentifier();

		return $this->_pValues->$identifierFieldName ?? [];
	}


	/**
	 * @param string $type The constant from InputModelDBFactoryFilterableFields
	 * @param string $default Default value if nothing was selected
	 */
	public function fillStringValues(string $type, string $default = 'range')
	{
		$fieldsArray = $this->readFieldsArray();
		$pInputModel = $this->_pInputModelFactory->create($type, '', true);
		if ($pInputModel == null) return;

		$identifier = $pInputModel->getIdentifier();

		if (property_exists($this->_pValues, $identifier))
		{
			$rawPostData = (array)$this->_pValues->$identifier;
			$newFieldList = [];

			foreach ($fieldsArray as $index => $fieldName)
			{
				if (isset($rawPostData[$fieldName])) {
					$newFieldList[$index] = $rawPostData[$fieldName];
				} else {
					$newFieldList[$index] = $default;
				}
			}
			$this->_pValues->$identifier = $newFieldList;
		}
	}


	/** @return stdClass */
	public function getValues(): stdClass
	{
		return $this->_pValues;
	}


	/** @return InputModelDBFactory */
	public function getInputModelFactory(): InputModelDBFactory
	{
		return $this->_pInputModelFactory;
	}
}