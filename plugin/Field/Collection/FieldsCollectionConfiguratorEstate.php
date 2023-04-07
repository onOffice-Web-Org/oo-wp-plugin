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

use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;

class FieldsCollectionConfiguratorEstate
{
	/**
	 * @param FieldsCollection $pFieldsCollection
	 * @return FieldsCollection
	 */
	public function configureFoEstate(FieldsCollection $pFieldsCollection): FieldsCollection
	{
		$pFieldsCollectionNew = new FieldsCollection;
		foreach ($pFieldsCollection->getAllFields() as $pField) {
			$pFieldClone = clone $pField;
			if ($pFieldClone->getType() === FieldTypes::FIELD_TYPE_SINGLESELECT)
			{
				$pFieldClone->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
			}
			$pFieldsCollectionNew->addField($pFieldClone);
		}
		return $pFieldsCollectionNew;
	}

	/**
	 * @param FieldsCollection $pFieldsCollection
	 * @return FieldsCollection
	 */
	public function buildForEstateType(FieldsCollection $pFieldsCollection): FieldsCollection
	{
		return $this->configureFoEstate($pFieldsCollection);
	}
}