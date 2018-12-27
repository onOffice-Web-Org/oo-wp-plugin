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

namespace onOffice\WPlugin\Types;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Fieldnames;

/**
 *
 */

class EstateStatusLabel
{
	/** @var Fieldnames */
	private $_pFieldnames = null;

	/** @var array */
	private $_estateValues = [];

	/** @var array */
	private $_fieldsByPrio = [
		'reserviert',
		'verkauft',
		'top_angebot',
		'preisreduktion',
		'courtage_frei',
		'objekt_des_tages',
		'neu',
	];


	/**
	 *
	 * @param array $estateValues
	 * @param Fieldnames $pFieldnames
	 *
	 */

	public function __construct(array $estateValues, Fieldnames $pFieldnames = null)
	{
		$this->_estateValues = $estateValues;
		$this->_pFieldnames = $pFieldnames ?? new Fieldnames(new FieldsCollection());
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getLabel(): string
	{
		$this->_pFieldnames->loadLanguage();
		foreach ($this->_fieldsByPrio as $key) {
			if ($this->getBoolValue($key)) {
				return $this->_pFieldnames->getFieldLabel($key, onOfficeSDK::MODULE_ESTATE);
			}
		}

		return '';
	}


	/**
	 *
	 * @param string $key
	 * @return bool
	 *
	 */

	private function getBoolValue(string $key): bool
	{
		return (bool) ($this->_estateValues[$key] ?? false);
	}


	/** @return Fieldnames */
	public function getFieldnames(): Fieldnames
		{ return $this->_pFieldnames; }

	/** @return array */
	public function getEstateValues(): array
		{ return $this->_estateValues; }

	/** @return array */
	public function getFieldsByPrio(): array
		{ return $this->_fieldsByPrio; }
}
