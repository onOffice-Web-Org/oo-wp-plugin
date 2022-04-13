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

namespace onOffice\tests;

use Exception;
use WP_UnitTestCase;
use const ONOFFICE_PLUGIN_DIR;
use function determine_locale;
use function is_textdomain_loaded;
use function load_textdomain;
use function restore_current_locale;
use function switch_to_locale;
use function unload_textdomain;

/**
 *
 * @backupGlobals disabled
 *
 */

abstract class WP_UnitTest_Localized
	extends WP_UnitTestCase
{
	/** @var bool */
	private $_localeSwitched = false;


	/**
	 *
	 */

	public static function setUpBeforeClass()
	{
		parent::set_up_before_class();
	}

	/**
	 *
	 */

	public function setUp()
	{
		parent::set_up();
		$this->switchLocale('de_DE');
	}


	/**
	 *
	 * @global array $l10n
	 * @param string $newLocale
	 * @throws Exception
	 *
	 */

	protected function switchLocale(string $newLocale)
	{
		global $l10n;

		if (!is_textdomain_loaded('onoffice-for-wp-websites') && $newLocale !== 'en_US') {
			load_textdomain('onoffice-for-wp-websites', ONOFFICE_PLUGIN_DIR . '/languages/onoffice-for-wp-websites-' . $newLocale . '.mo');
			if (!array_key_exists('onoffice-for-wp-websites', $l10n ?? [])) {
				throw new Exception('Textdomain not added');
			}
		}

		if (!is_textdomain_loaded('onoffice') && $newLocale !== 'en_US') {
			load_textdomain('onoffice', ONOFFICE_PLUGIN_DIR.'/languages/onoffice-'.$newLocale.'.mo');
			if (!array_key_exists('onoffice', $l10n ?? [])) {
				throw new Exception('Textdomain not added');
			}
		}

		if (determine_locale() !== $newLocale) {
			if (!switch_to_locale($newLocale)) {
				throw new Exception('Failed to switch locale '.$newLocale);
			}
			$this->_localeSwitched = true;
		}
	}


	/**
	 *
	 */

	public function tearDown()
	{
		if ($this->_localeSwitched) {
			restore_current_locale();
		}
		parent::tear_down();
	}

	/**
	 *
	 */

	public static function tearDownAfterClass()
	{
		unload_textdomain('onoffice-for-wp-websites');
		unload_textdomain('onoffice');
		parent::tear_down_after_class();
	}
}
