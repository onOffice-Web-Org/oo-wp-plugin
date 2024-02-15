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

/**
 *
 */

class ScriptLoaderGenericConfigurationDefault
	implements ScriptLoaderGenericConfiguration
{
	/** */
	const ESTATE_TAG = 'oo_estate';

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
		$styleVersion = $this->getOnOfficeStyleVersion();
		$onOfficeStyleUri = $this->getStyleUriByVersion($styleVersion);
		$values []= (new IncludeFileModel($style, $styleVersion, $onOfficeStyleUri));
		if (Favorites::isFavorizationEnabled()) {
			$values []= (new IncludeFileModel($script, 'onoffice-favorites', plugins_url('/dist/favorites.min.js', $pluginPath)))
				->setDependencies(['jquery']);
		}

		return array_merge($values, $this->addScripts($pluginPath, $script, $style, $defer, $async));
	}

	/**
	 * @param string $pluginPath
	 * @param string $script
	 * @param string $style
	 * @param string $defer
	 * @param string $async
	 * @return array
	 */

	private function addScripts(string $pluginPath, string $script, string $style, string $defer, string $async): array
	{
		$scripts = [];
		$pageContent = get_the_content();

		if (empty($pageContent)) {
			return $scripts;
		}

		if ($this->isEstateListPage($pageContent)) {
			$scripts = $this->renderScriptForEstateListPage($scripts, $pluginPath, $script, $async);
		}

		$shortcodeFormForDetailPage = !empty(get_option('onoffice-default-view')) ? get_option('onoffice-default-view')->getShortCodeForm() : '';
		if ($this->isDetailEstatePage($pageContent)) {
			$scripts = $this->renderScriptFormPageForEstateDetailPage($scripts, $pluginPath, $script, $style, $defer);
		}

		if ($this->isFormPage($pageContent) || ($this->isDetailEstatePage($pageContent) && ! empty($shortcodeFormForDetailPage))) {
			$scripts = $this->renderScriptForFormPage($scripts, $pluginPath, $script, $pageContent, $async);
		}

		return $scripts;
	}

	/**
	 * @param string $content
	 * @return bool
	 */
	private function isEstateListPage(string $content): bool
	{
		return $this->matchesShortcode($content, self::ESTATE_TAG, 'view', '[^"]*') &&
				!$this->matchesShortcode($content, self::ESTATE_TAG, 'view', 'detail') ||
				$this->matchesShortcode($content, self::ESTATE_TAG, 'units', '[^"]*');
	}

	/**
	 * @param string $content
	 * @return bool
	 */
	private function isDetailEstatePage(string $content): bool
	{
		return $this->matchesShortcode($content, self::ESTATE_TAG, 'view', 'detail');
	}

	/**
	 * @param string $content
	 * @return bool
	 */
	private function isFormPage(string $content): bool
	{
		return $this->matchesShortcode($content, self::FORM_TAG, 'form', '[^"]*');
	}

	/**
	 * @param string $content
	 * @param string $tag
	 * @param string $attribute
	 * @param string $valuePattern
	 * @return bool
	 */
	private function matchesShortcode(string $content, string $tag, string $attribute, string $valuePattern): bool
	{
		$pattern = '/\[' . $tag . '\s+' . $attribute . '="' . $valuePattern . '"\]/';
		return (bool) preg_match($pattern, $content);
	}

	/**
	 * @param array $scripts
	 * @param string $pluginPath
	 * @param string $script
	 * @param string $pageContent
	 * @param string $async
	 * @return array
	 */

	private function renderScriptForFormPage(array $scripts, string $pluginPath, string $script, string $pageContent, string $async): array
	{
		$forms = $this->getFormsByPageContent($pageContent);
				
		$hasGeneralForm = !empty(array_filter($forms, function($form) {
			return in_array($form->form_type, [Form::TYPE_CONTACT, Form::TYPE_OWNER, Form::TYPE_INTEREST]);
		}));

		$hasApplicantSearchForm = !empty(array_filter($forms, function($form) {
			return $form->form_type === Form::TYPE_APPLICANT_SEARCH;
		}));

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

		if ($hasApplicantSearchForm) {
			$scripts[] = (new IncludeFileModel($script, 'onoffice-form-preview', plugins_url('/dist/onoffice-form-preview.min.js', $pluginPath)))
					->setDependencies(['jquery'])
					->setLoadInFooter(true);
			$this->localizeFormPreviewScript();
		}

		if (get_option('onoffice-settings-honeypot') == true && $hasGeneralForm) {
			$scripts[] = (new IncludeFileModel($script, 'onoffice-honeypot', plugins_url('/dist/onoffice-honeypot.min.js', $pluginPath)))
					->setDependencies(['jquery'])
					->setLoadInFooter(true);
		}

		return $scripts;
	}

	/**
	 * @param string $pageContent
	 * @return array
	 */

	private function getFormsByPageContent(string $pageContent): array
	{		
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();

		$names = array_map(function ($item) {
			return "'" . esc_sql($item) . "'";
		}, $this->extractFormNames($pageContent));
		$names = implode(',', $names);
	
		$pRecordManagerReadForm = $pContainer->get(RecordManagerReadForm::class);
		$pRecordManagerReadForm->addColumn('form_type');
		$pRecordManagerReadForm->addWhere("`name` IN(" . $names . ")");

		return $pRecordManagerReadForm->getRecords();
	}

	/**
	 * @param string $pageContent
	 * @return array
	 */

	private function extractFormNames(string $pageContent): array
	{
		preg_match_all('/\[oo_form form="([^"]+)"\]/', $pageContent, $matches);
		return $matches[1];
	}

	/**
	 * @param array $scripts
	 * @param string $pluginPath
	 * @param string $script
	 * @param string $style
	 * @param string $defer
	 * @return array
	 */

	private function renderScriptFormPageForEstateDetailPage(array $scripts, string $pluginPath, string $script, string $style, string $defer): array
	{
		$scripts[] = (new IncludeFileModel($script, 'slick', plugins_url('/third_party/slick/slick.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true)
				->setLoadAsynchronous($defer);
		$scripts[] = (new IncludeFileModel($script, 'onoffice_defaultview', plugins_url('/dist/onoffice_defaultview.min.js', $pluginPath)))
				->setDependencies(['jquery'])
				->setLoadInFooter(true);
		$scripts[] = new IncludeFileModel($style, 'slick', plugins_url('/third_party/slick/slick.css', $pluginPath));
		$scripts[] = new IncludeFileModel($style, 'slick-theme', plugins_url('/third_party/slick/slick-theme.css', $pluginPath));

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

		$this->localizeFormPreviewScript();

		return $scripts;
	}

	/**
	 * @return void
	 */
	private function localizeFormPreviewScript()
	{
		wp_localize_script('onoffice-form-preview', 'onoffice_form_preview_strings', [
			'amount_none' => __('0 matches', 'onoffice-for-wp-websites'),
			'amount_one' => __('Show exact match', 'onoffice-for-wp-websites'),
			/* translators: %s is the amount of results */
			'amount_other' => __('Show %s matches', 'onoffice-for-wp-websites'),
			'nonce_estate' => wp_create_nonce('onoffice-estate-preview'),
			'nonce_applicant_search' => wp_create_nonce('onoffice-applicant-search-preview'),
		]);
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
            return get_stylesheet_directory_uri() . '/onoffice-theme/templates/onoffice-style.css';
        }

        $onofficeCssStyleFilePath = get_template_directory() . '/onoffice-theme/templates/onoffice-style.css';
        if (file_exists($onofficeCssStyleFilePath)) {
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
        $folderTemplates[ TemplateCall::TEMPLATE_FOLDER_PARENT_THEME ] = glob( get_template_directory()
            . '/onoffice-theme' );

        $defaultview = 'onoffice_defaultview';
        $newstyle = 'onoffice_style';

        $onofficeCssStyleVersion = $defaultview;
        if ( ! empty( $folderTemplates[ TemplateCall::TEMPLATE_FOLDER_THEME ] ) ) {
            $onofficeCssStyleVersion = ! empty( glob( get_stylesheet_directory()
                . '/onoffice-theme/templates/onoffice-style.css' ) )
                ? $newstyle
                : $defaultview;
        } elseif ( ! empty( $folderTemplates[ TemplateCall::TEMPLATE_FOLDER_PARENT_THEME ] ) ) {
            $onofficeCssStyleVersion = ! empty( glob( get_template_directory()
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
