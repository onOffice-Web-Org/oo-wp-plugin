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

class Language
{
	/**
	 * @var array
	 * @see https://make.wordpress.org/polyglots/teams/
	 */
	const LOCALE_MAPPING = [
		'de' => 'DEU',
		'de_DE' => 'DEU',
		'de_DE_formal' => 'DEU',
		'de_CH' => 'CHE',
		'de_CH_informal' => 'CHE',
		'en' => 'ENG',
		'en_GB' => 'ENG',
		'en_US' => 'ENG',
		'nl_BE' => 'BEL',
		'nl_NL' => 'NLD',
		'nl_NL_formal' => 'NLD',
		'fr_BE' => 'FRA',
		'fr_CA' => 'FRA',
		'fr_FR' => 'FRA',
		'el' => 'GRC',
		'it_IT' => 'ITA',
		'lb_LU' => 'LUX',
		'pl_PL' => 'POL',
		'pt_PT' => 'PRT',
		'ro_RO' => 'ROU',
		'ru_RU' => 'RUS',
		'sl_SI' => 'SVN',
		'es_AR' => 'ESP',
		'es_CL' => 'CHI',
		'es_CO' => 'ESP',
		'es_MX' => 'ESP',
		'es_PE' => 'ESP',
		'es_PR' => 'ESP',
		'es_ES' => 'ESP',
		'es_VE' => 'ESP',
		'ca' => 'CAT',
		'sv_SE' => 'SWE',
		'tr_TR' => 'TUR',
		'fi' => 'FIN',
		'cs_CZ' => 'CZE',
		'hr' => 'HRV',
		'zh_CN' => 'CHN',
		'bg_BG' => 'BGR',
		'ar' => 'SAU',
		'da_DK' => 'DNK',
		'nn_NO' => 'NOR',
	];

	/**
	 * @return string
	 */
	static public function getDefault(): string
	{
		$languageMapping = self::LOCALE_MAPPING;
		$currentLocale = get_locale();
		return $languageMapping[$currentLocale] ?? 'DEU';
	}

	/**
	 * @return string
	 */
	public function getLocale(): string
	{
		return get_locale();
	}

	/**
	 * @return string
	 */
	public function getOnOfficeLanguage(): string
	{
		return self::getDefault();
	}
}
