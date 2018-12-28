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

use onOffice\WPlugin\Types\EstateStatusLabel;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class EstateViewFieldModifierTypeDefault
	extends EstateViewFieldModifierTypeEstateGeoBase
{
	/** @var EstateStatusLabel */
	private $_pEstateStatusLabel = null;


	/**
	 *
	 * @param array $viewFields
	 * @param EstateStatusLabel $pEstateStatusLabel
	 *
	 */

	public function __construct(array $viewFields, EstateStatusLabel $pEstateStatusLabel = null)
	{
		parent::__construct($viewFields);
		$this->_pEstateStatusLabel = $pEstateStatusLabel ?? new EstateStatusLabel();
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getAPIFields(): array
	{
		$apiFields = array_merge($this->getViewFields(), [
			'virtualAddress',
			'objektadresse_freigeben',

			// for `vermarktungsstatus`
			'reserviert',
			'verkauft',
			'vermarktungsart',
		], $this->_pEstateStatusLabel->getFieldsByPrio());

		return $this->editViewFieldsForApiGeoPosition($apiFields);
	}


	/**
	 *
	 * @param array $record
	 * @return array
	 *
	 */

	public function reduceRecord(array $record): array
	{
		// do not use isset() since value may be NULL
		if (array_key_exists('mainLangId', $record)) {
			unset($record['mainLangId']);
		}

		$record['vermarktungsstatus'] = $this->buildMarketingStatus($record);

		return parent::reduceRecord($record);
	}


	/**
	 *
	 * @param array $record
	 * @return string
	 *
	 */

	private function buildMarketingStatus(array $record): string
	{
		return $this->_pEstateStatusLabel->getLabel($record);
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getVisibleFields(): array
	{
		return array_merge(parent::getVisibleFields(), ['vermarktungsstatus']);
	}
}
