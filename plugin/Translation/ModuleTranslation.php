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

declare (strict_types=1);

namespace onOffice\WPlugin\Translation;

use onOffice\SDK\onOfficeSDK;


/**
 *
 */

class ModuleTranslation
{
	/**
	 *
	 * @param string $module
	 * @return string
	 *
	 */

	public static function getLabelSingular(string $module): string
	{
		$noopedPlural = self::getModuleTranslations()[$module] ?? [];

		if ($noopedPlural !== []) {
			return translate_nooped_plural($noopedPlural, 1, 'onoffice');
		}

		return '';
	}


	/**
	 *
	 * @return array
	 *
	 */

	public static function getAllLabelsSingular(): array
	{
		$result = array_map(function(array $value): string {
			return translate_nooped_plural($value, 1, 'onoffice');
		}, self::getModuleTranslations());

		return $result;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private static function getModuleTranslations(): array
	{
		return [
			onOfficeSDK::MODULE_ADDRESS => _nx_noop('Address', 'Addresses', 'modules', 'onoffice'),
			onOfficeSDK::MODULE_ESTATE => _nx_noop('Estate', 'Estates', 'modules', 'onoffice'),
			onOfficeSDK::MODULE_SEARCHCRITERIA => _nx_noop('Search Criteria', 'Search Criteria', 'modules', 'onoffice'),
		];
	}
}
