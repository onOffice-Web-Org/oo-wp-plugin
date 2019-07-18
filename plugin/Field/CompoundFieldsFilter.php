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

namespace onOffice\WPlugin\Field;

use onOffice\WPlugin\Types\FieldsCollection;

/**
 *
 * finds all compound fields
 *
 */

class CompoundFieldsFilter
{
	/**
	 *
	 * @return array
	 *
	 */

	public function buildCompoundFields(FieldsCollection $pFieldsCollection): array
	{
		$fields = $pFieldsCollection->getAllFields();
		$result = [];

		foreach ($fields as $pField) {
			if ($pField->getCompoundFields() !== []) {
				$result[$pField->getName()] = $pField->getCompoundFields();
			}
		}

		return $result;
	}


	/**
	 *
	 * @param FieldsCollection $pFieldsCollection
	 * @param array $fields
	 * @return array
	 *
	 */

	public function mergeFields(FieldsCollection $pFieldsCollection, array $fields): array
	{
		$compoundFields = $this->buildCompoundFields($pFieldsCollection);
		$result = $fields;

		foreach ($fields as $field) {
			if (isset($compoundFields[$field])) {
				$index = array_search($field, $result);
				unset($result[$index]);
				$result = array_merge($result, $compoundFields[$field]);
			}
		}

		return $result;
	}


	/**
	 *
	 * @param FieldsCollection $pFieldsCollection
	 * @param array $fields
	 * @return array
	 *
	 */

	public function mergeAssocFields(FieldsCollection $pFieldsCollection, array $fields): array
	{
		$compoundFields = $this->buildCompoundFields($pFieldsCollection);
		$result = [];

		foreach ($fields as $fieldname => $module) {
			if (isset($compoundFields[$fieldname])) {
				$result = $this->createNew($result, $compoundFields[$fieldname], $module);
			} else {
				$result[$fieldname] = $module;
			}
		}

		return $result;
	}


	/**
	 *
	 * @param array $result
	 * @param array $compoundFields
	 * @param string $module
	 * @return array
	 *
	 */

	private function createNew(array $result, array $compoundFields, string $module): array
	{
		foreach ($compoundFields as $name) {
			$result[$name] = $module;
		}
		return $result;
	}
}