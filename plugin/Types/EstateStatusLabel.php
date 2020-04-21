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
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Fieldnames;
use function __;

/**
 *
 */

class EstateStatusLabel
{
	/** @var Fieldnames */
	private $_pFieldnamesActive = null;

	/** @var Fieldnames */
	private $_pFieldnamesInactive = null;

	/** @var array */
	private $_estateValues = [];

	/** @var array */
	private $_fieldsByPrio = [
		'referenz',
		'reserviert',
		'verkauft',
		'exclusive',
		'neu',
		'top_angebot',
		'preisreduktion',
		'courtage_frei',
		'objekt_des_tages',
	];


	/**
	 *
	 * @param Fieldnames $pFieldnamesActive for testing
	 * @param Fieldnames $pFieldnamesInactive for testing
	 *
	 */

	public function __construct(Fieldnames $pFieldnamesActive = null, Fieldnames $pFieldnamesInactive = null)
	{
		$this->_pFieldnamesActive = $pFieldnamesActive ?? new Fieldnames(new FieldsCollection());
		$this->_pFieldnamesInactive = $pFieldnamesInactive ?? new Fieldnames(new FieldsCollection(), true);
	}

	/**
	 *
	 * @param array $estateValues
	 * @return string
	 * @throws UnknownFieldException
	 */
	public function getLabel(array $estateValues): string
	{
		$this->_estateValues = $estateValues;

		foreach ($this->_fieldsByPrio as $key) {
			if ($this->getBoolValue($key)) {
				return $this->processRecord($key);
			}
		}

		return '';
	}

	/**
	 *
	 * @param string $key
	 * @return string
	 * @throws UnknownFieldException
	 */
	private function processRecord(string $key): string
	{
		$this->_pFieldnamesActive->loadLanguage();
		$this->_pFieldnamesInactive->loadLanguage();
		$label = $this->getFieldLabel($key);

		if ($key === 'verkauft') {
			if ($this->_estateValues['vermarktungsart'] === 'miete') {
				$label = __('rented', 'onoffice');
			} elseif ($this->_estateValues['vermarktungsart'] === 'kauf') {
				$label = __('sold', 'onoffice');
			}
		}

		return $label;
	}

	/**
	 * @param string $key
	 * @return string
	 * @throws UnknownFieldException
	 */
	private function getFieldLabel(string $key): string
	{
		$this->_pFieldnamesActive->loadLanguage();
		$this->_pFieldnamesInactive->loadLanguage();

		// those fields are usually disabled but some of them don't have to be
		try {
			$info = $this->_pFieldnamesInactive->getFieldInformation
				($key, onOfficeSDK::MODULE_ESTATE);
		} catch (UnknownFieldException $pE) {
			$info = $this->_pFieldnamesActive->getFieldInformation
				($key, onOfficeSDK::MODULE_ESTATE);
		}
		return $info['label'];
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
	public function getFieldnamesActive(): Fieldnames
		{ return $this->_pFieldnamesActive; }

	/** @return Fieldnames */
	public function getFieldnamesInActive(): Fieldnames
		{ return $this->_pFieldnamesInactive; }

	/** @return array */
	public function getFieldsByPrio(): array
		{ return $this->_fieldsByPrio; }
}
