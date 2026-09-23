<?php

/**
 *
 *    Copyright (C) 2026 onOffice GmbH
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

declare(strict_types=1);

namespace onOffice\WPlugin\Utility;

/**
 * Knows which themes are the onOffice parent themes.
 *
 * Several features are only offered when one of them is active - ALTCHA, the ALTCHA default in
 * DatabaseChanges, the applicant search form, the supervisor option of the owner form. The list
 * lives here rather than in one of those features so that asking "is an onOffice theme active?"
 * doesn't mean importing a captcha class.
 */
class ThemeSupport
{
	/** The four onOffice parent themes, by template (parent theme) slug. */
	const SUPPORTED_THEMES = [
		'onoffice-pure',
		'onoffice-classic',
		'onoffice-timeless',
		'onoffice-modern',
	];

	/**
	 * Check whether the active theme is one of the onOffice WP-Websites themes. Compares the
	 * template, not the stylesheet, so child themes count as well.
	 */
	public static function isOnOfficeTheme(): bool
	{
		return in_array(wp_get_theme()->get_template(), self::SUPPORTED_THEMES, true);
	}
}
