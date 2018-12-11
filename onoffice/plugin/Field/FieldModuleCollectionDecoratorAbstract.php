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

declare(strict_types=1);

namespace onOffice\WPlugin\Field;

use onOffice\WPlugin\Types\Field;
use function __;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

abstract class FieldModuleCollectionDecoratorAbstract implements FieldModuleCollection
{
	/** @var FieldModuleCollection */
	private $_pFieldModuleCollection = null;


	/**
	 *
	 * @param FieldModuleCollection $pFieldModuleCollection
	 *
	 */

	public function __construct(FieldModuleCollection $pFieldModuleCollection)
	{
		$this->_pFieldModuleCollection = $pFieldModuleCollection;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getAllFields(): array
	{
		return $this->_pFieldModuleCollection->getAllFields();
	}


	/**
	 *
	 * @param string $module
	 * @param string $name
	 * @return bool
	 *
	 */

	public function containsFieldByModule(string $module, string $name): bool
	{
		return $this->_pFieldModuleCollection->containsFieldByModule($module, $name);
	}


	/**
	 *
	 * @param Field $pField
	 *
	 */

	public function addField(Field $pField)
	{
		$this->_pFieldModuleCollection->addField($pField);
	}


	/**
	 *
	 * @param string $module
	 * @param string $name
	 * @return Field
	 *
	 */

	public function getFieldByModuleAndName(string $module, string $name): Field
	{
		return $this->_pFieldModuleCollection->getFieldByModuleAndName($module, $name);
	}


	/**
	 *
	 * @param string $module
	 * @param array $fields
	 *
	 */

	protected function addFields(string $module, array $fields)
	{
		foreach ($fields as $fieldName => $fieldData) {
			if ($this->containsFieldByModule($module, $fieldName)) {
				continue;
			}

			$pField = new Field($fieldName, $module, __($fieldData['label'], 'onoffice'));
			$pField->setDefault($fieldData['default'] ?? null);
			$pField->setLength($fieldData['length'] ?? 0);
			$pField->setPermittedvalues($fieldData['permittedvalues'] ?? []);
			$pField->setType($fieldData['type']);

			$this->addField($pField);
		}
	}


	/**
	 *
	 * @param array $fieldsByModule
	 * @return array
	 *
	 */

	protected function generateListOfMergedFieldsByModule(array $fieldsByModule): array
	{
		$newFields = [];

		foreach ($fieldsByModule as $module => $fieldsByModule) {
			foreach ($fieldsByModule as $name => $row) {
				$row['module'] = $module;
				$newFields []= Field::createByRow($name, $row);
			}
		}

		return $newFields;
	}


	/**
	 *
	 * @return FieldModuleCollection
	 *
	 */

	protected function getFieldModuleCollection(): FieldModuleCollection
	{
		return $this->_pFieldModuleCollection;
	}
}
