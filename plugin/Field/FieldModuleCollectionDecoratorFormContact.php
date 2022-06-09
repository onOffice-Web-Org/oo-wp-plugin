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
 */

class FieldModuleCollectionDecoratorFormContact
	extends FieldModuleCollectionDecoratorAbstract
{
	/**
	 * @return array
	 */
	public function getNewFields(): array
	{
		return [
			onOfficeSDK::MODULE_ADDRESS => [
				'newsletter' => [
					'type' => FieldTypes::FIELD_TYPE_BOOLEAN,
					'length' => 0,
					'permittedvalues' => [],
					'default' => false,
					'label' => 'Newsletter',
				],
			],
			'' => [
				'message' => [
					'type' => FieldTypes::FIELD_TYPE_TEXT,
					'length' => null,
					'permittedvalues' => [],
					'default' => null,
					'label' => __( 'Message', 'onoffice-for-wp-websites' )
				],
			],
		];
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getAllFields(): array
	{
		$newFields = $this->generateListOfMergedFieldsByModule($this->getNewFields());
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
		$row = $this->getNewFields()[$module][$name] ?? null;
		if ($row !== null) {
			$row['module'] = $module;
			return Field::createByRow($name, $row);
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
		return isset($this->getNewFields()[$module][$name]) ||
			parent::containsFieldByModule($module, $name);
	}
}
