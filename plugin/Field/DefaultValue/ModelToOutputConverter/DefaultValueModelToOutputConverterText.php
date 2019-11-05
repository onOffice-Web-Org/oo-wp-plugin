<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

namespace onOffice\WPlugin\Field\DefaultValue\ModelToOutputConverter;

use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelText;
use onOffice\WPlugin\Language;

/**
 *
 */

class DefaultValueModelToOutputConverterText
	implements DefaultValueModelToOutputConverterBase
{
	/** @var Language */
	private $_pLanguage;


	/**
	 *
	 * @param Language $pLanguage
	 *
	 */

	public function __construct(Language $pLanguage)
	{
		$this->_pLanguage = $pLanguage;
	}


	/**
	 *
	 * @param DefaultValueModelText $pDefaultValueModel
	 * @return array
	 *
	 */

	public function convert(DefaultValueModelText $pDefaultValueModel): array
	{
		$valuesByLocale = $pDefaultValueModel->getValuesByLocale();
		$currentLocale = $this->_pLanguage->getLocale();

		if (isset($valuesByLocale[$currentLocale])) {
			$valuesByLocale['native'] = $valuesByLocale[$currentLocale];
			unset($valuesByLocale[$currentLocale]);
		}
		return $valuesByLocale;
	}
}
