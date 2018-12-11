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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class FieldModuleCollectionDecoratorSearchcriteria
	extends FieldModuleCollectionDecoratorAbstract
{
	/** @var array */
	private $_searchcriteriaFields = [
		'krit_bemerkung_oeffentlich' => [
			'type' => FieldTypes::FIELD_TYPE_TEXT,
			'length' => null,
			'permittedvalues' => [],
			'default' => null,
			'label' => 'Comment',
		],
	];


	/**
	 *
	 * @return Field[]
	 *
	 */

	public function getAllFields(): array
	{
		$allFields = parent::getAllFields();
		foreach ($this->_searchcriteriaFields as $fieldName => $row)
		{
			$row['module'] = onOfficeSDK::MODULE_SEARCHCRITERIA;
			$allFields []= Field::createByRow($fieldName, $row);
		}

		return $allFields;
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
		if ($module === onOfficeSDK::MODULE_SEARCHCRITERIA) {
			$row = $this->_searchcriteriaFields[$name] ?? null;
			if ($row !== null) {
				$row['module'] = onOfficeSDK::MODULE_ADDRESS;
				return Field::createByRow($name, $row);
			}
		}
		return parent::getFieldByModuleAndName($module, $name);
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
		return
			($module === onOfficeSDK::MODULE_SEARCHCRITERIA &&
				isset($this->_searchcriteriaFields[$name])) ||
				parent::containsFieldByModule($module, $name);
	}
}
