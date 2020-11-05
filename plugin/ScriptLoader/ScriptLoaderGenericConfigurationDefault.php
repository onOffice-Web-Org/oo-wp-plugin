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

namespace onOffice\WPlugin\ScriptLoader;

use onOffice\WPlugin\Favorites;
use const ONOFFICE_PLUGIN_DIR;
use function plugins_url;

/**
 *
 */

class ScriptLoaderGenericConfigurationDefault
	implements ScriptLoaderGenericConfiguration
{
	/**
	 *
	 * @return array
	 *
	 */

	public function getScriptLoaderGenericConfiguration(): array
	{
		$pluginPath = ONOFFICE_PLUGIN_DIR.'/index.php';
		$script = IncludeFileModel::TYPE_SCRIPT;
		$style = IncludeFileModel::TYPE_STYLE;

		$values = [
			(new IncludeFileModel($script, 'onoffice-multiselect', plugins_url('/js/onoffice-multiselect.js', $pluginPath)))
				->setLoadInFooter(true),
			(new IncludeFileModel($script, 'onoffice-leadform', plugins_url('/js/onoffice-leadform.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true),
			(new IncludeFileModel($script, 'onoffice-sort-list-selector', plugins_url('/js/onoffice-sort-list-selector.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true),
			(new IncludeFileModel($script, 'slick', plugins_url('/third_party/slick/slick.js', $pluginPath)))
			->setDependencies(['jquery'])
			->setLoadInFooter(true),
			(new IncludeFileModel($script, 'onoffice_defaultview', plugins_url('/js/onoffice_defaultview.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true),
			(new IncludeFileModel($script, 'onoffice-estatetype', plugins_url('/js/onoffice-estatetype.js', $pluginPath)))
				->setDependencies(['onoffice-multiselect'])
				->setLoadInFooter(true),
			(new IncludeFileModel($script, 'onoffice-form-preview', plugins_url('/js/onoffice-form-preview.js', $pluginPath)))
				->setLoadInFooter(true),

			new IncludeFileModel($style, 'onoffice', plugins_url('/css/build/oo-wp-plugin.css', $pluginPath)),
		];

		if (Favorites::isFavorizationEnabled()) {
			$values []= (new IncludeFileModel($script, 'onoffice-favorites', plugins_url('/js/favorites.js', $pluginPath)))
				->setDependencies(['jquery']);
		}

		wp_localize_script('onoffice-form-preview', 'onoffice_form_preview_strings', [
			'amount_none' => __('0 matches', 'onoffice'),
			'amount_one' => __('Show exact match', 'onoffice'),
			/* translators: %s is the amount of results */
			'amount_other' => __('Show %s matches', 'onoffice'),
			'nonce_estate' => wp_create_nonce('onoffice-estate-preview'),
			'nonce_applicant_search' => wp_create_nonce('onoffice-applicant-search-preview'),
		]);

		return $values;
	}
}
