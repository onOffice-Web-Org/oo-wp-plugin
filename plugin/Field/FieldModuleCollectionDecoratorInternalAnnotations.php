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

use function __;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class FieldModuleCollectionDecoratorInternalAnnotations
	extends FieldModuleCollectionDecoratorAbstract
{

	/**
	 * @return array
	 */
	public function getNewFields(): array {
		return [
			onOfficeSDK::MODULE_SEARCHCRITERIA => [
				'krit_bemerkung_oeffentlich' => __('Search Criteria Comment (external)', 'onoffice-for-wp-websites'),
			],
		];
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
		$pField = parent::getFieldByModuleAndName($module, $name);
		$newLabel = $this->getNewFields()[$module][$name] ?? null;
		if ($newLabel !== null) {
			$pField->setLabel( $newLabel );
		}
		return $pField;
	}


	/**
	 *
	 * @return Field[]
	 *
	 */

	public function getAllFields(): array
	{
		$fields = parent::getAllFields();

		foreach ($fields as $pField) {
			$module = $pField->getModule();
			$name = $pField->getName();
			$label = $this->getNewFields()[$module][$name] ?? null;

			if ($label !== null) {
				$pField->setLabel( $label );
			}
		}

		return $fields;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getFieldAnnotations(): array
	{
		return $this->getNewFields();
	}
}
