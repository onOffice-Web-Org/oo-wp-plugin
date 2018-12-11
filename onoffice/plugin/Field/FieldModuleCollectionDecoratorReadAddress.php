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

class FieldModuleCollectionDecoratorReadAddress
	extends FieldModuleCollectionDecoratorAbstract
{
	/** @var array */
	private $_addressFields = [
		'imageUrl' => [
			'type' => FieldTypes::FIELD_TYPE_TEXT,
			'length' => null,
			'label' => 'Image',
		],
		'phone' => [
			'type' => FieldTypes::FIELD_TYPE_VARCHAR,
			'length' => 40,
			'label' => 'Phone',
		],
		'email' => [
			'type' => FieldTypes::FIELD_TYPE_VARCHAR,
			'length' => 80,
			'label' => 'E-Mail',
		],
		'fax' => [
			'type' => FieldTypes::FIELD_TYPE_VARCHAR,
			'length' => 40,
			'label' => 'Fax',
		],
		'mobile' => [
			'type' => FieldTypes::FIELD_TYPE_VARCHAR,
			'length' => 40,
			'label' => 'Mobile',
		],
		'defaultphone' => [
			'type' => FieldTypes::FIELD_TYPE_VARCHAR,
			'length' => 40,
			'label' => 'Phone',
		],
		'defaultemail' => [
			'type' => FieldTypes::FIELD_TYPE_VARCHAR,
			'length' => 80,
			'label' => 'E-Mail',
		],
		'defaultfax' => [
			'type' => FieldTypes::FIELD_TYPE_VARCHAR,
			'length' => 40,
			'label' => 'Fax',
		],
	];


	/**
	 *
	 * @return array
	 *
	 */

	public function getAllFields(): array
	{
		$newFields = [];
		foreach ($this->_addressFields as $name => $row) {
			$row['module'] = onOfficeSDK::MODULE_ADDRESS;
			$newFields []= Field::createByRow($name, $row);
		}
		return array_merge($this->getFieldModuleCollection()->getAllFields(), $newFields);
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
		if ($module === onOfficeSDK::MODULE_ADDRESS) {
			$row = $this->_addressFields[$name] ?? null;
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
		return ($module === onOfficeSDK::MODULE_ADDRESS &&
			isset($this->_addressFields[$name])) ||
			parent::containsFieldByModule($module, $name);
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getNewAddressFields(): array
	{
		return $this->_addressFields;
	}
}
