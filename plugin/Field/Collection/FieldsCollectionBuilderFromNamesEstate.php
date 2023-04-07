<?php

/**
 *
 *    Copyright (C) 2023 onOffice GmbH
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

namespace onOffice\WPlugin\Field\Collection;

use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Types\FieldsCollection;

/**
 *
 */
class FieldsCollectionBuilderFromNamesEstate
{
	/**
	 * @param array $fieldList
	 * @param FieldsCollection $pBase
	 * @return FieldsCollection
	 * @throws UnknownFieldException
	 */
	public function buildFieldsCollectionFromBaseCollection(array $fieldList, FieldsCollection $pBase): FieldsCollection
	{
		$pFieldsCollectionTarget = new FieldsCollection;
		foreach ($fieldList as $field) {
			$this->copyField($pBase, $field, $pFieldsCollectionTarget);
		}
		return $pFieldsCollectionTarget;
	}

	/**
	 * @param FieldsCollection $pCollectionSource
	 * @param string $field
	 * @param FieldsCollection $pCollectionTarget
	 * @throws UnknownFieldException
	 */
	private function copyField(FieldsCollection $pCollectionSource, string $field, FieldsCollection $pCollectionTarget)
	{
		$pField = $pCollectionSource->getFieldByKeyUnsafe($field);
		$pCollectionTarget->addField($pField);
	}
}