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

use DateTime;
use onOffice\WPlugin\Gui\DateTimeFormatter;
use onOffice\WPlugin\Types\FieldTypes;

class InputVariableReaderFormatter
{
	/**
	 * @param $value
	 * @param string $type
	 * @return array|string
	 */
	public function formatValue($value, string $type)
	{
		if (is_float($value)) {
			return $this->formatFloatValue($value);
		} elseif (is_array($value)) {
			$value = array_map(function($val) use ($type) {
				return $this->formatValue($val, $type);
			}, $value);
		} elseif (FieldTypes::isDateOrDateTime($type) && $value != '') {
			$value = $this->formatDateOrDateTimeValue($value, $type);
		}
		return $value;
	}

	/**
	 * @param float $value
	 * @return string
	 */
	public function formatFloatValue(float $value): string
	{
		return number_format_i18n($value, 2);
	}

	/**
	 * @param string $value
	 * @param string $type
	 * @return string
	 */
	public function formatDateOrDateTimeValue(string $value, string $type): string
	{
		$format = DateTimeFormatter::SHORT|DateTimeFormatter::ADD_DATE;
		if ($type === FieldTypes::FIELD_TYPE_DATETIME) {
			$format |= DateTimeFormatter::ADD_TIME;
		}

		$pDate = new DateTime($value.' Europe/Berlin');
		$pDateTimeFormatter = new DateTimeFormatter();
		return $pDateTimeFormatter->formatByTimestamp($format, $pDate->getTimestamp());
	}
}