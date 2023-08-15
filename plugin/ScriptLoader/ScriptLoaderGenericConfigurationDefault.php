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
use onOffice\WPlugin\Template\TemplateCall;
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
		$defer = IncludeFileModel::LOAD_DEFER;

		$values = [
			(new IncludeFileModel($script, 'select2', plugins_url('/vendor/select2/select2/dist/js/select2.min.js', $pluginPath)))
				->setLoadInFooter(true)
				->setAsynchronous($defer),
			(new IncludeFileModel($script, 'onoffice-custom-select', plugins_url('/js/onoffice-custom-select.js', $pluginPath)))
				->setLoadInFooter(true),
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
				->setLoadInFooter(true)
				->setAsynchronous($defer),
			(new IncludeFileModel($script, 'onoffice_defaultview', plugins_url('/js/onoffice_defaultview.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true),
			(new IncludeFileModel($script, 'onoffice-estatetype', plugins_url('/js/onoffice-estatetype.js', $pluginPath)))
				->setDependencies(['onoffice-multiselect'])
				->setLoadInFooter(true),
			(new IncludeFileModel($script, 'onoffice-form-preview', plugins_url('/js/onoffice-form-preview.js', $pluginPath)))
				->setLoadInFooter(true),

			new IncludeFileModel($style, 'onoffice-default', plugins_url('/css/onoffice-default.css', $pluginPath)),
			new IncludeFileModel($style, 'onoffice-multiselect', plugins_url('/css/onoffice-multiselect.css', $pluginPath)),
			new IncludeFileModel($style, 'onoffice-forms', plugins_url('/css/onoffice-forms.css', $pluginPath)),
			new IncludeFileModel($style, 'slick', plugins_url('/third_party/slick/slick.css', $pluginPath)),
			new IncludeFileModel($style, 'slick-theme', plugins_url('/third_party/slick/slick-theme.css', $pluginPath)),
			new IncludeFileModel($style, 'select2', plugins_url('/vendor/select2/select2/dist/css/select2.min.css', $pluginPath))
		];
		$styleVersion = $this->getOnOfficeStyleVersion();
		$onOfficeStyleUri = $this->getStyleUriByVersion($styleVersion);
        $values []= (new IncludeFileModel($style, $styleVersion, $onOfficeStyleUri));
		if (Favorites::isFavorizationEnabled()) {
			$values []= (new IncludeFileModel($script, 'onoffice-favorites', plugins_url('/js/favorites.js', $pluginPath)))
				->setDependencies(['jquery']);
		}

		wp_localize_script('onoffice-form-preview', 'onoffice_form_preview_strings', [
			'amount_none' => __('0 matches', 'onoffice-for-wp-websites'),
			'amount_one' => __('Show exact match', 'onoffice-for-wp-websites'),
			/* translators: %s is the amount of results */
			'amount_other' => __('Show %s matches', 'onoffice-for-wp-websites'),
			'nonce_estate' => wp_create_nonce('onoffice-estate-preview'),
			'nonce_applicant_search' => wp_create_nonce('onoffice-applicant-search-preview'),
		]);

		return $values;
	}

    /**
     * @return string
     */
    public function getStyleUriByVersion($styleVersion): string
    {
        $pluginPath = ONOFFICE_PLUGIN_DIR . '/index.php';

        if ($styleVersion == 'onoffice_defaultview')
        {
            return plugins_url('/css/onoffice_defaultview.css', $pluginPath);
        }

        $onofficeCssStyleFilePath = get_stylesheet_directory() . '/onoffice-theme/templates/onoffice-style.css';
        if (file_exists($onofficeCssStyleFilePath))
        {
            return get_template_directory_uri() . '/onoffice-theme/templates/onoffice-style.css';
        }

        $onofficeCssStyleFilePath = plugin_dir_path( ONOFFICE_PLUGIN_DIR ) . 'onoffice-personalized/templates/onoffice-style.css';
        if (file_exists($onofficeCssStyleFilePath))
        {
            return plugins_url('onoffice-personalized/templates/onoffice-style.css', '');
        }

        return plugins_url('templates.dist/onoffice-style.css', $pluginPath);
    }

    /**
     * @return string
     */
    public function getOnOfficeStyleVersion()
    {
        $folderTemplates[ TemplateCall::TEMPLATE_FOLDER_PLUGIN ] = glob( plugin_dir_path( ONOFFICE_PLUGIN_DIR )
            . 'onoffice-personalized' );
        $folderTemplates[ TemplateCall::TEMPLATE_FOLDER_THEME ]  = glob( get_stylesheet_directory()
            . '/onoffice-theme' );

        $defaultview = 'onoffice_defaultview';
        $newstyle = 'onoffice_style';

        $onofficeCssStyleVersion = $defaultview;
        if ( ! empty( $folderTemplates[ TemplateCall::TEMPLATE_FOLDER_THEME ] ) ) {
            $onofficeCssStyleVersion = ! empty( glob( get_stylesheet_directory()
                . '/onoffice-theme/templates/onoffice-style.css' ) )
                ? $newstyle
                : $defaultview;
        } elseif ( ! empty( $folderTemplates[ TemplateCall::TEMPLATE_FOLDER_PLUGIN ] ) ) {
            $onofficeCssStyleVersion = ! empty( glob( plugin_dir_path( ONOFFICE_PLUGIN_DIR )
                . 'onoffice-personalized/templates/onoffice-style.css' ) )
                ? $newstyle
                : $defaultview;
        } elseif ( ! empty( glob( ONOFFICE_PLUGIN_DIR . '/templates.dist/onoffice-style.css' ) ) ) {
            $onofficeCssStyleVersion = $newstyle;
        }
        return $onofficeCssStyleVersion;
    }
}
