<?php

/**
 *
 *    Copyright (C) 2020 onOffice GmbH
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

namespace onOffice\WPlugin\Controller;

use DateTimeZone;
use DateTime;
use Exception;
use WP_Locale;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Utility\__String;
use function number_format_i18n;

class InputVariableReaderParser
{
	/** @var string */
	private $_timezoneString;

	/**
	 *
	 * @param string $timezoneString
	 */
	public function __construct(string $timezoneString = '')
	{
		$this->setTimezoneString($timezoneString);
	}

	/**
	 * @param string $timezoneString
	 */
	private function setTimezoneString(string $timezoneString)
	{
		$this->_timezoneString = $timezoneString;

		if ($this->_timezoneString == '') {
			$this->_timezoneString = get_option('timezone_string', '');
		}
	}

	/**
	 *
	 * @param mixed $value
	 * @param string $type
	 * @return string
	 *
	 */

	public function parseValue($value, $type)
	{
		if (is_array($value)) {
			return array_map(function($val) use ($type) {
				return $this->parseValue($val, $type);
			}, $value);
		} elseif ($value === null) {
			return null;
		}

		switch ($type) {
			case FieldTypes::FIELD_TYPE_FLOAT:
				$value = $this->parseFloat($value);
				break;

			case FieldTypes::FIELD_TYPE_BOOLEAN:
				$value = $this->parseBool($value);
				break;

			case FieldTypes::FIELD_TYPE_DATE:
			case FieldTypes::FIELD_TYPE_DATETIME:
				$value = $this->parseDate($value);
				break;
		}

		return $value;
	}


	/**
	 *
	 * @global WP_Locale $wp_locale
	 * @param string $floatString
	 * @return float
	 *
	 */

	public function parseFloat(string $floatString)
	{
		if (__String::getNew($floatString)->isEmpty()) {
			return null;
		}

		global $wp_locale;
		$stringThousand = __String::getNew($floatString)->replace
		($wp_locale->number_format['thousands_sep'], '');
		$stringDec = __String::getNew($stringThousand)->replace
		($wp_locale->number_format['decimal_point'], '.');

		$onofficeSettingsThousandSeparator = get_option('onoffice-settings-thousand-separator');
		if ($onofficeSettingsThousandSeparator === InputVariableReaderFormatter::DOT_THOUSAND_SEPARATOR) {
			$stringThousand = str_replace('.', '', $floatString);
			$stringDec = __String::getNew($stringThousand)->replace
			($wp_locale->number_format['thousands_sep'], '.');
		} elseif ($onofficeSettingsThousandSeparator === InputVariableReaderFormatter::COMMA_THOUSAND_SEPARATOR) {
			$stringThousand = str_replace(',', '', $floatString);
			$stringDec = __String::getNew($stringThousand)->replace
			($wp_locale->number_format['decimal_point'], '.');
		}

		return floatval($stringDec);
	}


	/**
	 *
	 * @param string $boolString
	 * @return bool
	 *
	 */

	public function parseBool(string $boolString)
	{
		if (__String::getNew($boolString)->isEmpty() || $boolString === 'u') {
			return null;
		}

		return $boolString === 'y';
	}


	/**
	 *
	 * @param string $dateString
	 * @return int
	 *
	 */

	public function parseDate(string $dateString)
	{
		if (__String::getNew($dateString)->isEmpty()) {
			return null;
		}

		$pTimezoneBerlin = new DateTimeZone('Europe/Berlin');

		try {
			$pDateTime = new DateTime($dateString.' '.$this->_timezoneString);
			$pDateTime->setTimezone($pTimezoneBerlin);
			return $pDateTime->format('Y-m-d H:i:s');
		} catch (Exception $pE) {
			return null;
		}
	}
}