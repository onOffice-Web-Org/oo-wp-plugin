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

namespace onOffice\WPlugin\Translation;

use onOffice\SDK\onOfficeSDK;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class ModuleTranslation
{
	/** @var array */
	private static $_moduleTranslationSingular = array(
		onOfficeSDK::MODULE_ADDRESS => 'Address',
		onOfficeSDK::MODULE_ESTATE => 'Estate',
		onOfficeSDK::MODULE_SEARCHCRITERIA => 'Search Criteria',
	);


	/**
	 *
	 * @param string $module
	 * @return string
	 *
	 */

	public static function getLabelSingular($module)
	{
		$result = null;
		if (array_key_exists($module, self::$_moduleTranslationSingular)) {
			$result = self::$_moduleTranslationSingular[$module];
		}
		return $result;
	}


	/**
	 *
	 * @param bool $translate
	 * @return array
	 *
	 */

	public static function getAllLabelsSingular($translate = false)
	{
		$result = null;
		if ($translate) {
			$result = array_map(__CLASS__.'::translateValue', self::$_moduleTranslationSingular);
		} else {
			$result = self::$_moduleTranslationSingular;
		}
		return $result;
	}


	/**
	 *
	 * @internal for callback in method getAllLabelsSingular() only
	 * @param string $value
	 *
	 */

	public static function translateValue($value) {
		return __($value, 'onoffice');
	}
}
