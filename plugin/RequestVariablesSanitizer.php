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

namespace onOffice\WPlugin;


/**
 *
 */

class RequestVariablesSanitizer
{
	/**
	 *
	 * @param string $name
	 * @param int $filter
	 * @param int|array $option
	 * @return mixed
	 *
	 */

	public function getFilteredGet(string $name, int $filter = FILTER_DEFAULT, $option = null)
	{
		return $this->getFiltered($_GET, $name, $filter, $option);
	}

	/**
	 *
	 * @param string $name
	 * @param int $filter
	 * @param int|array $option
	 * @return mixed
	 *
	 */

	public function getFilteredPost(string $name, int $filter = FILTER_DEFAULT, $option = null)
	{
		return $this->getFiltered($_POST, $name, $filter, $option);
	}


	/**
	 *
	 * @param array $inputVariable
	 * @param string $name
	 * @param int $filter
	 * @param int|array $option
	 * @return mixed
	 *
	 */

	private function getFiltered(array $inputVariable, string $name, int $filter, $option = null)
	{
		$variable = stripslashes_deep($inputVariable[$name] ?? null);

		if($filter == FILTER_SANITIZE_FULL_SPECIAL_CHARS){
			return self::sanitizeFilterString($inputVariable[$name] ?? null);
		}

		return filter_var($variable, $filter, $option ?? 0);
	}

	public static function filterInputString(int $type, string $var_name): string{
		return self::sanitizeFilterString(filter_input($type, $var_name, FILTER_DEFAULT));
	}

    /**
     * Sanitize Strings based on the deprecated FILTER_SANITIZE_STRING filter.
     *
     * @param $value
     * @param array $flags
     * @return string
     */
	public static function sanitizeFilterString($value, array $flags = []): string
	{
		if ($value === null) {
			return '';
		}
		$noQuotes = in_array(FILTER_FLAG_NO_ENCODE_QUOTES, $flags);
		$options = ($noQuotes ? ENT_NOQUOTES : ENT_QUOTES) | ENT_SUBSTITUTE;
		$optionsDecode = ($noQuotes ? ENT_QUOTES : ENT_NOQUOTES) | ENT_SUBSTITUTE;

		$value = stripslashes($value);
		$value = strip_tags($value);
		$value = htmlspecialchars($value, $options);

		// Fix that HTML entities are converted to entity numbers instead of entity name (e.g. ' -> &#34; and not ' -> &quote;)
		$value = str_replace(["&quot;", "&#039;"], ["&#34;", "&#39;"], $value);
		return html_entity_decode($value, $optionsDecode);
	}
}