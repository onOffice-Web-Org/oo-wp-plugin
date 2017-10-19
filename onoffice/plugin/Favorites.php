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
		$jsonIds = isset($_COOKIE[self::COOKIE_NAME]) ? $_COOKIE[self::COOKIE_NAME] : null;
		$favorites = array(0);

		if ($jsonIds !== null) {
			$favorites = json_decode($jsonIds);
		}

		return $favorites;
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
	 * @return int value of KEY_SETTING_* constant
	 *
	 */

	static public function getFavorizationLabel() {
		if (!self::isFavorizationEnabled()) {
			return null;
		}

		return get_option('onoffice-favorization-favButtonLabelFav', 0);
	}


	/**
	 * 
	 */

	static public function registerScripts() {
		if (self::isFavorizationEnabled()) {
			wp_register_script( 'onoffice-favorites', plugins_url( '/js/favorites.js', ONOFFICE_PLUGIN_DIR ) );
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
