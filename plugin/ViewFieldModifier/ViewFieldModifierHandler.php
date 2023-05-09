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

use Exception;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class ViewFieldModifierHandler
{
	/** @var array */
	private $_viewFields = [];

	/** @var ViewFieldModifierTypeBase */
	private $_pModifier = null;

	/** @var ViewFieldModifierFactory */
	private $_pViewFieldModifierFactory = null;


	/**
	 *
	 * @param array $viewFields
	 * @param string $module
	 * @param string $modifier
	 *
	 */

	public function __construct(array $viewFields, string $module, string $modifier = '')
	{
		$this->_viewFields = $viewFields;
		$this->_pViewFieldModifierFactory = new ViewFieldModifierFactory($module);

		if ($modifier !== '') {
			$this->_pModifier = $this->_pViewFieldModifierFactory->create($modifier, $viewFields);
		}
	}


	/**
	 *
	 * @param array $record
	 * @return array
	 * @throws Exception
	 *
	 */

	public function processRecord(array $record): array
	{
		if ($this->_pModifier === null) {
			throw new Exception('Unknown Modifier');
		}

		$newRecord = $this->_pModifier->reduceRecord($record);

		$visibleFieldNames = $this->_pModifier->getVisibleFields();

		if(!empty($newRecord['preisAufAnfrage'])){
			$visibleFieldNames[] = 'preisAufAnfrage';
		}
		$visibleFields = array_flip($visibleFieldNames);


		$visibleFields = array_map(
			function($value) {	return '';},
			$visibleFields);

		$intersection = array_intersect_key($newRecord, $visibleFields);

		$fields = array_merge($visibleFields, $intersection);

		return $fields;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getAllAPIFields(): array
	{
		$apiFields = [];

		foreach ($this->_pViewFieldModifierFactory->getMapping() as $class) {
			$pInstance = new $class($this->_viewFields);
			/* @var $pInstance ViewFieldModifierTypeBase */
			$apiFields = array_merge($apiFields, $pInstance->getAPIFields());
		}

		$fields = array_values(array_unique($apiFields));
		if (($key = array_search('vermarktungsstatus', $fields)) !== false) {
			unset($fields[$key]);
		}
		return array_diff($fields, $this->_pViewFieldModifierFactory->getForbiddenAPIFields());
	}


	/** @param ViewFieldModifierFactory $pFactory */
	public function setViewFieldModifierFactory(ViewFieldModifierFactory $pFactory)
		{ $this->_pViewFieldModifierFactory = $pFactory; }
}
