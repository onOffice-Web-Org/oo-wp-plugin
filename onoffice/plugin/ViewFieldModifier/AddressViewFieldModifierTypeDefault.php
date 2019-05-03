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

namespace onOffice\WPlugin\ViewFieldModifier;

use onOffice\WPlugin\Utility\__String;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class AddressViewFieldModifierTypeDefault
	implements ViewFieldModifierTypeBase
{
	/** @var string[] */
	private $_viewFields = [];


	/**
	 *
	 * @param array $viewFields
	 *
	 */

	public function __construct(array $viewFields)
	{
		$this->_viewFields = $viewFields;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getAPIFields(): array
	{
		$compoundingFields = $this->getCompundingFields();
		$compoundingFieldparts = $this->getCompoundingFieldParts($compoundingFields);

		$viewFields = $this->_viewFields;
		$viewFieldsMinusCompounding = array_diff($viewFields, $compoundingFields);
		$fieldListApiSafe = array_merge($viewFieldsMinusCompounding, $compoundingFieldparts);
		return array_values(array_unique($fieldListApiSafe));
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getVisibleFields(): array
	{
		return $this->_viewFields;
	}


	/**
	 *
	 * @param array $record
	 * @return array
	 *
	 */

	public function reduceRecord(array $record): array
	{
		$compoundingFields = $this->getCompundingFields();
		$compoundingFieldParts = $this->getCompoundingFieldParts($compoundingFields);
		$fieldsToRemove = array_diff($compoundingFieldParts, $this->_viewFields);

		foreach ($compoundingFields as $field) {
			$fieldParts = explode('-', $field);
			$values = [];
			foreach ($fieldParts as $key) {
				$values []= $record[$key];
			}

			$record[$field] = $values;
		}

		foreach ($fieldsToRemove as $fieldForRemoval) {
			unset($record[$fieldForRemoval]);
		}

		return $record;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getCompundingFields(): array
	{
		return array_filter($this->_viewFields, function($field): bool {
			return __String::getNew($field)->contains('-');
		});
	}


	/**
	 *
	 * @param array $compoundingFields
	 * @return array
	 *
	 */

	private function getCompoundingFieldParts(array $compoundingFields): array
	{
		$newFields = [];

		foreach ($compoundingFields as $compoundingField) {
			$compoundingFieldParts = explode('-', $compoundingField);
			$newFields = array_merge($newFields, $compoundingFieldParts);
		}

		return $newFields;
	}
}
