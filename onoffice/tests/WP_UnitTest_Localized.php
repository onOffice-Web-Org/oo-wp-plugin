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

namespace onOffice\tests;

use WP_Locale_Switcher;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

abstract class WP_UnitTest_Localized
	extends WP_UnitTestCase
{
	/** @var string */
	private $_localeBackup = null;


	/**
	 *
	 */

	public function setUp()
	{
		parent::setUp();
		$this->_localeBackup = get_locale();

		$this->switchLocale('de_DE');
	}


	/**
	 *
	 * @param string $locale
	 * @return bool
	 *
	 */

	protected function switchLocale(string $locale)
	{
		$pLocaleSwitcher = new WP_Locale_Switcher();
		$pLocaleSwitcher->init();
		return $pLocaleSwitcher->switch_to_locale($locale);
	}


	/**
	 *
	 */

	public function tearDown()
	{
		parent::tearDown();
		$this->switchLocale($this->_localeBackup);
	}
}
