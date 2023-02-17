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

namespace onOffice\WPlugin\Controller;

use onOffice\WPlugin\Filesystem\Filesystem;
use onOffice\WPlugin\Language;
use Parsedown;


/**
 *
 */

class MainPage
{
	/** @var Language */
	private $_pLanguage = null;

	/** @var MainPageFileMapping */
	private $_pFileMapping = null;

	/** @var Filesystem */
	private $_pFilesystem = null;


	/**
	 *
	 * @param Language $pLanguage
	 *
	 */

	public function __construct(Language $pLanguage, MainPageFileMapping $pFileMapping, Filesystem $pFilesystem)
	{
		$this->_pLanguage = $pLanguage;
		$this->_pFileMapping = $pFileMapping;
		$this->_pFilesystem = $pFilesystem;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function render(): string
	{
		return '<img src="'.plugins_url('/plugin/Gui/resource/mainPage/logo.png', ONOFFICE_PLUGIN_DIR.'/index').'" class="logo">'
			.'<div class="card">'
			.$this->includeHtml()
			.'</div>'
			.'<div id="madeby">Made with <span class="heart">♥</span> by onOffice</span>';
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function includeHtml(): string
	{
		$parsedown = new Parsedown;
		$fileMapping = $this->_pFileMapping->getMapping();
		$locale = $this->_pLanguage->getLocale();
		$file = $fileMapping[$locale] ?? $fileMapping['en_US'] ??
				$fileMapping['en_GB'] ?? $fileMapping['de_DE'] ?? '';
		$markdown = $this->_pFilesystem->getContents($file);
		$html = $parsedown->text($markdown);
		return $html;
	}
}
