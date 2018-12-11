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
	/** @var array */
	private $_fieldAnnotations = [
		onOfficeSDK::MODULE_ADDRESS => [
			'defaultphone' => 'Phone (Marked as default in onOffice)',
			'defaultemail' => 'E-Mail (Marked as default in onOffice)',
			'defaultfax' => 'Fax (Marked as default in onOffice)',
		],
		onOfficeSDK::MODULE_SEARCHCRITERIA => [
			'krit_bemerkung_oeffentlich' => 'Search Criteria Comment (external)',
		],
	];


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
		$newLabel = $this->_fieldAnnotations[$module][$name] ?? null;
		if ($newLabel !== null) {
			$pField->setLabel(__($newLabel, 'onoffice'));
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
			$label = $this->_fieldAnnotations[$module][$name] ?? null;

			if ($label !== null) {
				$pField->setLabel(__($label, 'onoffice'));
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
		return $this->_fieldAnnotations;
	}
}
