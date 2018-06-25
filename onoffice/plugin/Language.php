<?php

/**
 *
 *    Copyright (C) 2017 onOffice Software AG
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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

/**
 *
 */

class Language
{
	/** @var array */
	private $_config = null;


	/**
	 *
	 */

	public function __construct()
	{
		$this->reloadConfig();
	}


	/**
	 *
	 * @param string $configIndex
	 * @param string $view
	 * @return string
	 *
	 */

	public function getLanguageForEstateSingle($configIndex, $view)
	{
		$estateLang = $this->_config['estate'][$configIndex]['views'][$view]['language'];
		return $this->convertLanguage($estateLang);
	}


	/**
	 *
	 * @param string $configIndex
	 * @return string
	 *
	 */

	public function getLanguageForEstateList($configIndex)
	{
		$estateLang = $this->_config['estate'][$configIndex]['views']['list']['language'];
		return $this->convertLanguage($estateLang);
	}


	/**
	 *
	 * @param string $formName
	 * @return string
	 *
	 */

	public function getLanguageForForm($formName)
	{
		$estateLang = $this->_config['forms'][$formName]['language'];
		return $this->convertLanguage($estateLang);
	}


	/**
	 *
	 * @param string $language
	 * @return string
	 *
	 */

	public function convertLanguage($language)
	{
		if ($language == 'auto')
		{
			$languageMapping = $this->_config['localemap'];
			$currentLocale = get_locale();

			if (array_key_exists($currentLocale, $languageMapping))
			{
				$language = $languageMapping[$currentLocale];
			}
			else
			{
				$language = $languageMapping['fallback'];
			}
		}
		return $language;
	}


	/**
	 *
	 */

	public function reloadConfig()
	{
		$this->_config = ConfigWrapper::getInstance()->getConfig();
	}
}
