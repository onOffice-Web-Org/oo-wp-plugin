<?php

/**
 *
 *    Copyright (C) 2016  onOffice GmbH
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace onOffice\WPlugin;

use Exception;

/**
 *
 */

class Favorites
{
	/** */
	const COOKIE_NAME = 'onoffice_favorites';

	/** Constant for setting: call it "Favorize" */
	const KEY_SETTING_FAVORIZE = 0;

	/** Constant for setting: call it "Memorize" */
	const KEY_SETTING_MEMORIZE = 1;


	/**
	 *
	 * @return array
	 *
	 */

	static public function getAllFavorizedIds() {
		$jsonIds = filter_input(INPUT_COOKIE, self::COOKIE_NAME, FILTER_UNSAFE_RAW);
		$favoriteIds = array(0);

		if ($jsonIds !== null) {
			try
			{
				$favorites = json_decode($jsonIds);
			}
			catch (Exception $pE)
			{
				$favorites = array(0);
			}

			$favoriteIds = array_filter($favorites, 'is_numeric');
		}

		if ($favoriteIds === array()) {
			$favoriteIds = array(0);
		}

		return $favoriteIds;
	}


	/**
	 *
	 * @return bool
	 *
	 */

	static public function isFavorizationEnabled() {
		return get_option('onoffice-favorization-enableFav', false);
	}


	/**
	 *
	 * @return string|null
	 *
	 */

	static public function getFavorizationLabel() {
		if (!self::isFavorizationEnabled()) {
			return null;
		}

		$id = get_option('onoffice-favorization-favButtonLabelFav', self::KEY_SETTING_FAVORIZE);

		switch ($id) {
			case self::KEY_SETTING_FAVORIZE:
				return 'Favorites';
			case self::KEY_SETTING_MEMORIZE:
				return 'Watchlist';
			default:
				return null;
		}
	}


	/**
	 *
	 */

	static public function registerScripts() {
		if (self::isFavorizationEnabled()) {
			wp_register_script( 'onoffice-favorites', plugins_url( 'dist/favorites.min.js', ONOFFICE_PLUGIN_DIR ) );
		}
	}


	/**
	 *
	 */

	public function includeScripts() {
		if (self::isFavorizationEnabled()) {
			wp_enqueue_script( 'onoffice-favorites' );
		}
	}
}
