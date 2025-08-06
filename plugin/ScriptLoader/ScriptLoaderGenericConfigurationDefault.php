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

	private $_pConfiguration = [];


    public function __construct()
    {
        $this->_pConfiguration = $this->generateScriptLoaderGenericConfiguration();
    }

    /**
     *
     * @return array
     *
     */

    public function getScriptLoaderGenericConfiguration(): array {
        return $this->_pConfiguration;
    }

	/**
	 *
	 * @return array
	 *
	 */

	private function generateScriptLoaderGenericConfiguration(): array
	{
		$pluginPath = ONOFFICE_PLUGIN_DIR.'/index.php';
		$script = IncludeFileModel::TYPE_SCRIPT;
		$style = IncludeFileModel::TYPE_STYLE;
		$defer = IncludeFileModel::LOAD_DEFER;
		$async = IncludeFileModel::LOAD_ASYNC;

		$values = [
			(new IncludeFileModel($script, 'select2', plugins_url('/vendor/select2/select2/dist/js/select2.min.js', $pluginPath)))
				->setLoadInFooter(true)
                ->setLoadBeforeRenderingTemplate(true)
				->setLoadAsynchronous($defer),
            (new IncludeFileModel($style, 'onoffice-default', plugins_url('/css/onoffice-default.css', $pluginPath)))->setLoadBeforeRenderingTemplate(true),
            (new IncludeFileModel($style, 'onoffice-multiselect', plugins_url('/css/onoffice-multiselect.css', $pluginPath)))->setLoadBeforeRenderingTemplate(true),
            (new IncludeFileModel($style, 'onoffice-forms', plugins_url('/css/onoffice-forms.css', $pluginPath)))->setLoadBeforeRenderingTemplate(true),
            (new IncludeFileModel($style, 'select2', plugins_url('/vendor/select2/select2/dist/css/select2.min.css', $pluginPath)))->setLoadBeforeRenderingTemplate(true)
		];
		$values []= (new IncludeFileModel($style, 'onoffice_style', $this->getStyleUriByVersion()))->setLoadBeforeRenderingTemplate(true);
		$values = array_merge($values, $this->registerScriptAndStyles($pluginPath, $script, $style, $async, $defer));

		return $values;
	}

	/**
	 * @param string $formType
	 * @param bool $recaptcha
	 */

	public function addFormScripts(string $formType, bool $recaptcha) 
	{
		$scripts = $this->renderScriptForForm($formType, $recaptcha);
		$this->enqueueScripts($scripts);
	}

	/**
	 * @param string $key
	 */

	public function addEstateScripts(string $key) 
	{
		$scripts = [];
		$styles = [];

		if ($key === Template::KEY_ESTATELIST) {
			$scripts = $this->renderScriptForEstateListPage();
		}
		if ($key === Template::KEY_ESTATEDETAIL) {
			$scripts = $this->renderScriptForEstateDetailPage();
			$styles = $this->renderStyleForEstateDetailPage();
		}
		$this->enqueueScripts($scripts);
		$this->enqueueStyles($styles);
	}

	/**
	 * @param string $key
	 */

	public function addAddressScripts(string $key) 
	{
		$scripts = [];
		$styles = [];
		
		if ($key === Template::KEY_ADDRESSLIST) {
			$scripts = $this->renderScriptForAddressListPage();
		}
		if ($key === Template::KEY_ADDRESSDETAIL) {
			$styles = $this->renderStyleForAddressDetail();
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
		foreach ($scripts as $identifier) {
			if (!wp_script_is($identifier)) {
				wp_enqueue_script($identifier);
			}
		}
	}
 
	/**
	 * @param array $styles
	 * @return void
	 */
	private function enqueueStyles(array $styles)
	{
		foreach ($styles as $identifier) {
			if (!wp_style_is($identifier)) {
				wp_enqueue_style($identifier);
			}
		}
	}

	/**
	 * @param string $pluginPath
	 * @param string $script
	 * @param string $async
	 * @param string $defer
	 * @return array
	 */
	private function registerScriptAndStyles(string $pluginPath, string $script, $style, string $async, string $defer): array
	{
		$values = [
			(new IncludeFileModel($script, 'onoffice-custom-select', plugins_url('/dist/onoffice-custom-select.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true),
			(new IncludeFileModel($script, 'onoffice-multiselect', plugins_url('/dist/onoffice-multiselect.min.js', $pluginPath)))
				->setLoadInFooter(true)
				->setLoadAsynchronous($async),
			(new IncludeFileModel($script, 'onoffice-estatetype', plugins_url('/dist/onoffice-estatetype.min.js', $pluginPath)))
				->setDependencies(['onoffice-multiselect'])
				->setLoadInFooter(true)
				->setLoadAsynchronous($async),
			(new IncludeFileModel($script, 'onoffice-leadform', plugins_url('/dist/onoffice-leadform.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true),
			(new IncludeFileModel($script, 'onoffice-prevent-double-form-submission', plugins_url('/dist/onoffice-prevent-double-form-submission.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true),
			(new IncludeFileModel($script, 'onoffice-apply-thousand-separator', plugins_url('dist/onoffice-apply-thousand-separator.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true),
			(new IncludeFileModel($script, 'accessible-slick', plugins_url('/third_party/accessible-slick/accessible-slick.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true)
				->setLoadAsynchronous($defer),
			(new IncludeFileModel($script, 'onoffice_defaultview', plugins_url('/dist/onoffice_defaultview.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true),
			(new IncludeFileModel($script, 'onoffice-sort-list-selector', plugins_url('/dist/onoffice-sort-list-selector.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true),
			(new IncludeFileModel($script, 'onoffice-form-preview', plugins_url('/dist/onoffice-form-preview.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true),
			(new IncludeFileModel($script, 'onoffice-custom-select', plugins_url('/dist/onoffice-custom-select.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true),
			new IncludeFileModel($style, 'onoffice-address-detail', plugins_url('/css/onoffice-address-detail.css', $pluginPath)),
			new IncludeFileModel($style, 'accessible-slick', plugins_url('/third_party/accessible-slick/accessible-slick.css', $pluginPath)),
			new IncludeFileModel($style, 'accessible-slick-theme', plugins_url('/third_party/accessible-slick/accessible-slick-theme.css', $pluginPath)),
		];
        if (Favorites::isFavorizationEnabled()) {
            $values []= (new IncludeFileModel($script, 'onoffice-favorites', plugins_url('/dist/favorites.min.js', $pluginPath)))
                ->setDependencies(['jquery'])
                ->setLoadBeforeRenderingTemplate(true);
        }

		if (get_option('onoffice-settings-captcha-sitekey') !== '') {
			$values[] = (new IncludeFileModel($script, 'onoffice-captchacontrol', plugins_url('/dist/onoffice-captchacontrol.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(false)
				->setLoadBeforeRenderingTemplate(true);
		}
		if (get_option('onoffice-settings-honeypot')) {
			$values[] = (new IncludeFileModel($script, 'onoffice-honeypot', plugins_url('/dist/onoffice-honeypot.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true);
		}

		return $values;
	}

	/**
	 * @param string $formType
	 * @param bool $recaptcha
	 * @return array
	 */
	private function renderScriptForForm(string $formType, bool $recaptcha): array
	{
		$scripts = [
			'onoffice-custom-select', 
			'onoffice-multiselect', 
			'onoffice-estatetype', 
			'onoffice-leadform', 
			'onoffice-prevent-double-form-submission', 
			'onoffice-apply-thousand-separator'
		];

		if ($recaptcha) {
			$scripts[] = 'onoffice-captchacontrol';
		}

		if ($formType === Form::TYPE_APPLICANT_SEARCH) {
			$scripts[] = 'onoffice-form-preview';
		}

		if (in_array($formType, [Form::TYPE_CONTACT, Form::TYPE_OWNER, Form::TYPE_INTEREST])) {
			$scripts[] = 'onoffice-honeypot';
		}

		return $scripts;
	}

	/**
	 * @return array
	 */

	private function renderScriptForEstateListPage(): array
	{
		$scripts = [
			'onoffice-sort-list-selector', 
			'onoffice-form-preview', 
			'onoffice-custom-select', 
			'onoffice-multiselect', 
			'onoffice-estatetype', 
			'onoffice-apply-thousand-separator'
		];

		return $scripts;
	}

	/**
	 * @return array
	 */
	private function renderStyleForAddressDetail(): array
	{
		$styles = [
			'onoffice-address-detail'
		];
        return $styles;
    }

	/**
	 * @return array
	 */

	private function renderScriptForEstateDetailPage(): array
	{
		$scripts = [
			'accessible-slick',
			'onoffice_defaultview'
		];

		return $scripts;
	}

	/**
	 * @return array
	 */

	private function renderStyleForEstateDetailPage(): array 
	{
		$styles = [
			'accessible-slick',
			'accessible-slick-theme'
		];
		return $styles;
	}

	/**
	 * @return array
	 */

	private function renderScriptForAddressListPage(): array
	{
		$scripts = [
			'onoffice-custom-select'
		];

		return $scripts;
	}


	/**
	 * @param string $scriptIdentifier
	 * @return void
	 */
	public function localizeScript(string $scriptIdentifier)
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
