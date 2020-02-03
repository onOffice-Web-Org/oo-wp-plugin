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

namespace onOffice\WPlugin\WP;

class InstalledLanguageReader
{
	/**
	 *
	 */

	public function __construct()
	{
		require_once(ABSPATH.'wp-admin/includes/translation-install.php');
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function readAvailableLanguageNamesUsingNativeName(): array
	{
		$languagesKeys = $this->getAvailableLanguageKeys();
		$translations = wp_get_available_translations() + $this->createEnglishUsElement();
		$languages = array_combine($languagesKeys, array_map(function(string $key) use ($translations): string {
			$fallback = explode('_', $key)[0];
			return $translations[$key]['native_name'] ?? $translations[$fallback]['native_name'] ?? '';
		}, $languagesKeys));
		natcasesort($languages);
		return $languages;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getAvailableLanguageKeys(): array
	{
		return array_merge(['en_US'], get_available_languages());
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function createEnglishUsElement(): array
	{
		$labelEnglish = 'English (United States)';
		return ['en_US' => [
			'native_name' => $labelEnglish,
			'english_name' => $labelEnglish,
		]];
	}
}