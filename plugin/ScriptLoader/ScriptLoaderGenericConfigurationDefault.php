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
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\Form;
use DI\ContainerBuilder;
use onOffice\WPlugin\Template;

/**
 *
 */

class ScriptLoaderGenericConfigurationDefault
	implements ScriptLoaderGenericConfiguration
{
	/** */
	const ESTATE_TAG = 'oo_estate';

	/** */
	const ADDRESS_TAG = 'oo_address';

	/** */
	const FORM_TAG = 'oo_form';

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
		$async = IncludeFileModel::LOAD_ASYNC;

		$values = [
			(new IncludeFileModel($script, 'select2', plugins_url('/vendor/select2/select2/dist/js/select2.min.js', $pluginPath)))
				->setLoadInFooter(true)
				->setLoadAsynchronous($defer),
			new IncludeFileModel($style, 'onoffice-default', plugins_url('/css/onoffice-default.css', $pluginPath)),
			new IncludeFileModel($style, 'onoffice-multiselect', plugins_url('/css/onoffice-multiselect.css', $pluginPath)),
			new IncludeFileModel($style, 'onoffice-forms', plugins_url('/css/onoffice-forms.css', $pluginPath)),
			new IncludeFileModel($style, 'select2', plugins_url('/vendor/select2/select2/dist/css/select2.min.css', $pluginPath))
		];

		$onOfficeStyleUri = $this->getStyleUriByVersion();
		$values []= (new IncludeFileModel($style, 'onoffice_style', $onOfficeStyleUri));
		if (Favorites::isFavorizationEnabled()) {
			$values []= (new IncludeFileModel($script, 'onoffice-favorites', plugins_url('/dist/favorites.min.js', $pluginPath)))
				->setDependencies(['jquery']);
		}

		return $values;
	}

	/**
	 * @param string $formType
	 * @param bool $recaptcha
	 */

	public function addFormScripts(string $formType, bool $recaptcha) 
	{
		$pluginPath = ONOFFICE_PLUGIN_DIR.'/index.php';
		$scriptType = IncludeFileModel::TYPE_SCRIPT;
		$async = IncludeFileModel::LOAD_ASYNC;

		$scripts = $this->renderScriptForForm([], $pluginPath, $scriptType, $async, $formType, $recaptcha);
		$this->enqueueScripts($scripts);
	}

	/**
	 * @param string $key
	 */

	public function addEstateScripts(string $key) 
	{
		$pluginPath = ONOFFICE_PLUGIN_DIR.'/index.php';
		$scriptType = IncludeFileModel::TYPE_SCRIPT;
		$async = IncludeFileModel::LOAD_ASYNC;
		$style = IncludeFileModel::TYPE_STYLE;
		$scripts = [];
		$styles = [];

		if ($key === Template::KEY_ESTATELIST) {
			$scripts = $this->renderScriptForEstateListPage([], $pluginPath, $scriptType, $async);
		}
		if ($key === Template::KEY_ESTATEDETAIL) {
			$scripts = $this->renderScriptForEstateDetailPage([], $pluginPath, $scriptType, $async);
			$styles = $this->renderStyleForEstateDetailPage($pluginPath, $style);
		}
		$this->enqueueScripts($scripts);
		$this->enqueueStyles($styles);
	}

	/**
	 * @param string $key
	 */

	public function addAddressScripts(string $key) 
	{
		$pluginPath = ONOFFICE_PLUGIN_DIR.'/index.php';
		$scriptType = IncludeFileModel::TYPE_SCRIPT;
		$style = IncludeFileModel::TYPE_STYLE;
		$scripts = [];
		$styles = [];
		
		if ($key === Template::KEY_ADDRESSLIST) {
			$scripts = $this->renderScriptForAddressListPage([], $pluginPath, $scriptType);
		}
		if ($key === Template::KEY_ADDRESSDETAIL) {
			$styles = $this->renderStyleForAddressDetail($style, $pluginPath);
		}
		$this->enqueueScripts($scripts);
		$this->enqueueStyles($styles);
	}
 
	/**
	 * @param array $scripts
	 * @return void
	 */
	private function enqueueScripts(array $scripts)
	{
		foreach ($scripts as $script) {
			if (!wp_script_is($script->getIdentifier())) {
				wp_enqueue_script($script->getIdentifier(), $script->getFilePath(), $script->getDependencies(), false, $script->getLoadInFooter());
				wp_script_add_data($script->getIdentifier(), $script->getLoadAsynchronous(), true);
				$this->localizeScript($script->getIdentifier());
			}
		}
	}
 
	/**
	 * @param array $styles
	 * @return void
	 */
	private function enqueueStyles(array $styles)
	{
		foreach ($styles as $style) {
			if (!wp_style_is($style->getIdentifier())) {
				wp_enqueue_style($style->getIdentifier(), $style->getFilePath(), $style->getDependencies());
			}
		}
	}

	/**
	 * @param array $scripts
	 * @param string $pluginPath
	 * @param string $script
	 * @param string $async
	 * @param string $formType
	 * @param bool $recaptcha
	 * @return array
	 */
	private function renderScriptForForm(array $scripts, string $pluginPath, string $script, string $async, string $formType, bool $recaptcha): array
	{
		$scripts[] = (new IncludeFileModel($script, 'onoffice-custom-select', plugins_url('/dist/onoffice-custom-select.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true);
		$scripts[] = (new IncludeFileModel($script, 'onoffice-multiselect', plugins_url('/dist/onoffice-multiselect.min.js', $pluginPath)))
				->setLoadInFooter(true)
				->setLoadAsynchronous($async);
		$scripts[] = (new IncludeFileModel($script, 'onoffice-estatetype', plugins_url('/dist/onoffice-estatetype.min.js', $pluginPath)))
				->setDependencies(['onoffice-multiselect'])
				->setLoadInFooter(true)
				->setLoadAsynchronous($async);
		$scripts[] = (new IncludeFileModel($script, 'onoffice-leadform', plugins_url('/dist/onoffice-leadform.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true);
		$scripts[] = (new IncludeFileModel($script, 'onoffice-prevent-double-form-submission', plugins_url('/dist/onoffice-prevent-double-form-submission.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true);
		if (!empty(get_option('onoffice-settings-thousand-separator'))) {
			$scripts[] = (new IncludeFileModel($script, 'onoffice-apply-thousand-separator', plugins_url('dist/onoffice-apply-thousand-separator.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true);
		}

		if (get_option('onoffice-settings-captcha-sitekey') !== '' && $recaptcha) {
			$scripts[] = (new IncludeFileModel($script, 'onoffice-captchacontrol', plugins_url('/dist/onoffice-captchacontrol.min.js', $pluginPath)))
					->setDependencies(['jquery'])
					->setLoadInFooter(false);
		}

		if ($formType === Form::TYPE_APPLICANT_SEARCH) {
			$scripts[] = (new IncludeFileModel($script, 'onoffice-form-preview', plugins_url('/dist/onoffice-form-preview.min.js', $pluginPath)))
					->setDependencies(['jquery'])
					->setLoadInFooter(true);
		}

		if (in_array($formType, [Form::TYPE_CONTACT, Form::TYPE_OWNER, Form::TYPE_INTEREST])) {
			if (get_option('onoffice-settings-honeypot')) {
				$scripts[] = (new IncludeFileModel($script, 'onoffice-honeypot', plugins_url('/dist/onoffice-honeypot.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true);
			}
		}

		return $scripts;
	}

	/**
	 * @param string $style
	 * @param string $pluginPath
	 * @return array
	 */
	private function renderStyleForAddressDetail(string $style, string $pluginPath): array
	{
		$styles = [
			new IncludeFileModel($style, 'onoffice-address-detail', plugins_url('/css/onoffice-address-detail.css', $pluginPath))
		];
        return $styles;
    }

	/**
	 * @param array $scripts
	 * @param string $pluginPath
	 * @param string $script
	 * @param string $defer
	 * @return array
	 */

	private function renderScriptForAddressDetailPage(array $scripts, string $pluginPath, string $script, string $defer): array
	{
		$scripts[] = (new IncludeFileModel($script, 'slick', plugins_url('/third_party/slick/slick.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true)
				->setLoadAsynchronous($defer);
		$scripts[] = (new IncludeFileModel($script, 'onoffice_defaultview', plugins_url('/dist/onoffice_defaultview.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true);

		return $scripts;
	}

	/**
	 * @param array $scripts
	 * @param string $pluginPath
	 * @param string $script
	 * @param string $defer
	 * @return array
	 */

	private function renderScriptForEstateDetailPage(array $scripts, string $pluginPath, string $script, string $defer): array
	{
		$scripts[] = (new IncludeFileModel($script, 'slick', plugins_url('/third_party/slick/slick.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true)
				->setLoadAsynchronous($defer);
		$scripts[] = (new IncludeFileModel($script, 'onoffice_defaultview', plugins_url('/dist/onoffice_defaultview.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true);

		return $scripts;
	}

    /**
     * @param string $pluginPath
     * @param string $style
     * @return array
     */

	private function renderStyleForEstateDetailPage(string $pluginPath, string $style): array 
	{
		$styles = [
			new IncludeFileModel($style, 'slick', plugins_url('/third_party/slick/slick.css', $pluginPath)),
			new IncludeFileModel($style, 'slick-theme', plugins_url('/third_party/slick/slick-theme.css', $pluginPath))
		];
		return $styles;
	}

	/**
	 * @param array $scripts
	 * @param string $pluginPath
	 * @param string $script
	 * @return array
	 */

	private function renderScriptForAddressListPage(array $scripts, string $pluginPath, string $script): array
	{
		$scripts = [
			(new IncludeFileModel($script, 'onoffice-custom-select', plugins_url('/dist/onoffice-custom-select.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true),
		];

		return $scripts;
	}

	/**
	 * @param array $scripts
	 * @param string $pluginPath
	 * @param string $script
	 * @param string $style
	 * @param string $defer
	 * @return array
	 */

	private function renderScriptForEstateListPage(array $scripts, string $pluginPath, string $script, string $async): array
	{
		$scripts[] = (new IncludeFileModel($script, 'onoffice-sort-list-selector', plugins_url('/dist/onoffice-sort-list-selector.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true);
		$scripts[] = (new IncludeFileModel($script, 'onoffice-form-preview', plugins_url('/dist/onoffice-form-preview.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true);
		$scripts[] = (new IncludeFileModel($script, 'onoffice-custom-select', plugins_url('/dist/onoffice-custom-select.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true);
		$scripts[] = (new IncludeFileModel($script, 'onoffice-multiselect', plugins_url('/dist/onoffice-multiselect.min.js', $pluginPath)))
				->setLoadInFooter(true)
				->setLoadAsynchronous($async);
		$scripts[] = (new IncludeFileModel($script, 'onoffice-estatetype', plugins_url('/dist/onoffice-estatetype.min.js', $pluginPath)))
				->setDependencies(['onoffice-multiselect'])
				->setLoadInFooter(true)
				->setLoadAsynchronous($async);
		$scripts[] = (new IncludeFileModel($script, 'onoffice-apply-thousand-separator', plugins_url('dist/onoffice-apply-thousand-separator.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true);

		return $scripts;
	}

	/**
	 * @param string $scriptIdentifier
	 * @return void
	 */
	private function localizeScript(string $scriptIdentifier)
	{
		switch ($scriptIdentifier) {
			case 'onoffice-form-preview':
				wp_localize_script('onoffice-form-preview', 'onoffice_form_preview_strings', [
					'amount_none' => __('0 matches', 'onoffice-for-wp-websites'),
					'amount_one' => __('Show exact match', 'onoffice-for-wp-websites'),
					'amount_other' => __('Show %s matches', 'onoffice-for-wp-websites'),
					'nonce_estate' => wp_create_nonce('onoffice-estate-preview'),
					'nonce_applicant_search' => wp_create_nonce('onoffice-applicant-search-preview'),
				]);
				break;
			case 'onoffice-apply-thousand-separator':
				wp_localize_script('onoffice-apply-thousand-separator', 'onoffice_apply_thousand_separator', [
					'thousand_separator_format' => get_option('onoffice-settings-thousand-separator')
				]);
				break;
		}
	}

    /**
     * @return string
     */
    public function getStyleUriByVersion(): string
    {
        $pluginPath = ONOFFICE_PLUGIN_DIR . '/index.php';
        $styleFileTemplatePath = '/onoffice-theme/templates/onoffice-style.css';
        $styleFileInTheme = get_stylesheet_directory() . $styleFileTemplatePath;
        $styleFileInParentTheme = get_template_directory() . $styleFileTemplatePath;

        if (file_exists($styleFileInTheme))
        {
            return get_stylesheet_directory_uri() . $styleFileTemplatePath;
        }

        if (file_exists($styleFileInParentTheme)) {
            return get_template_directory_uri() . $styleFileTemplatePath;
        }

        $styleFilePluginPath = 'onoffice-personalized/templates/onoffice-style.css';
        $styleFileInPersonalizedPlugin = plugin_dir_path( ONOFFICE_PLUGIN_DIR ) . $styleFilePluginPath;

        if (file_exists($styleFileInPersonalizedPlugin))
        {
            return plugins_url($styleFilePluginPath, '');
        }

        return plugins_url('templates.dist/onoffice-style.css', $pluginPath);
    }
}
