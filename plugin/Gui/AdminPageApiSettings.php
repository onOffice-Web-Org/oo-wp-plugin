<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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

namespace onOffice\WPlugin\Gui;

use DI\ContainerBuilder;
use onOffice\WPlugin\Favorites;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Renderer\InputModelRenderer;
use onOffice\WPlugin\Types\MapProvider;
use onOffice\WPlugin\WP\WPOptionWrapperDefault;
use function __;
use function admin_url;
use function do_settings_sections;
use function esc_attr;
use function esc_html;
use function get_option;
use function json_encode;
use onOffice\WPlugin\Utility\SymmetricEncryption;
use function settings_fields;
use function submit_button;
use onOffice\WPlugin\Controller\AdminViewController;
use function wp_enqueue_style;
use Parsedown;
/**
 *
 */

class AdminPageApiSettings
	extends AdminPage
{
	/**
	 * @var SymmetricEncryption
	 */
	private $_encrypter;

	/**
	 *
	 * @param string $pageSlug
	 *
	 */
	public function __construct($pageSlug)
	{
		parent::__construct($pageSlug);
		$this->_encrypter = $this->getContainer()->make(SymmetricEncryption::class);
		$this->addFormModelAPI();
		$this->addFormModelEmail();
		$this->addFormModelMapProvider($pageSlug);
		$this->addFormModelGoogleMapsKey();
		$this->addFormModelGoogleCaptcha();
		$this->addFormModelSocialMetaData();
		$this->addFormModelHoneypot();
		$this->addFormModelFavorites($pageSlug);
        $this->addFormModelDetailView($pageSlug);
		$this->addFormModelPagination($pageSlug);
		$this->addFormModelGoogleBotSettings();
	}


	/**
	 *
	 */

	private function addFormModelAPI()
	{
		$labelKey = __('API token', 'onoffice-for-wp-websites');
		$labelSecret = __('API secret', 'onoffice-for-wp-websites');
		$pInputModelApiKey = new InputModelOption('onoffice-settings', 'apikey', $labelKey, 'string');
		$optionNameKey = $pInputModelApiKey->getIdentifier();
		$apiKey = get_option($optionNameKey);
		if (defined('ONOFFICE_CREDENTIALS_ENC_KEY')) {
			try {
				$apiKeyDecrypt = $this->_encrypter->decrypt(get_option($optionNameKey), ONOFFICE_CREDENTIALS_ENC_KEY);
			} catch (\RuntimeException $e) {
				$apiKeyDecrypt = $apiKey;
			}
			$apiKey = $apiKeyDecrypt;
		} else {
			update_option('onoffice-is-encryptcredent', false);
		}
		$pInputModelApiKey->setValue($apiKey);
		$pInputModelApiSecret = new InputModelOption('onoffice-settings', 'apisecret', $labelSecret, 'string');
		$pInputModelApiSecret->setIsPassword(true);
		$optionNameSecret = $pInputModelApiSecret->getIdentifier();
		$pInputModelApiKey->setSanitizeCallback(function ($apiKey) {
			return $this->encrypteCredentials($apiKey);
		});
		$pInputModelApiSecret->setSanitizeCallback(function($password) use ($optionNameSecret) {
			$password = $this->encrypteCredentials($password);
			if (defined('ONOFFICE_CREDENTIALS_ENC_KEY')) {
				$password = $this->checkPassword($password, $optionNameSecret);
				try {
					$passwordDecrypt = $this->_encrypter->decrypt($password, ONOFFICE_CREDENTIALS_ENC_KEY);
				} catch (\RuntimeException $e) {
					$passwordDecrypt = $password;
				}
				$password = $this->encrypteCredentials($passwordDecrypt);
			} else {
				update_option('onoffice-is-encryptcredent', false);
			}
			return $this->checkPassword($password, $optionNameSecret);
		});
		$pInputModelApiSecret->setValue(get_option($optionNameSecret, $pInputModelApiSecret->getDefault()));

		$pFormModel = new FormModel();
		$pFormModel->addInputModel($pInputModelApiSecret);
		$pFormModel->addInputModel($pInputModelApiKey);
		$pFormModel->setGroupSlug('onoffice-api');
		$pFormModel->setPageSlug($this->getPageSlug());
		if (get_option('onoffice-is-encryptcredent')) {
			$pFormModel->setLabel(__('API settings (encrypted)', 'onoffice-for-wp-websites'));
		} else {
			$pFormModel->setLabel(__('API settings', 'onoffice-for-wp-websites'));
		}

		$this->addFormModel($pFormModel);
	}

	private function addFormModelEmail()
	{
		$labelDefaultEmailAddress = __('Default Email Address', 'onoffice-for-wp-websites');
		$pInputModelDefaultEmailAddress = new InputModelOption
		('onoffice-settings', 'default-email', $labelDefaultEmailAddress, 'string');
		$pInputModelDefaultEmailAddress->setHtmlType(InputModelOption::HTML_TYPE_EMAIL);
		$optionDefaultEmail = $pInputModelDefaultEmailAddress->getIdentifier();
		$pInputModelDefaultEmailAddress->setValue(get_option($optionDefaultEmail, $pInputModelDefaultEmailAddress->getDefault()));

		$pFormModel = new FormModel();
		$pFormModel->addInputModel($pInputModelDefaultEmailAddress);
		$pFormModel->setGroupSlug('onoffice-default-email');
		$pFormModel->setPageSlug($this->getPageSlug());
		$pFormModel->setLabel(__('Email', 'onoffice-for-wp-websites'));

		$this->addFormModel($pFormModel);
	}

	/**
	 *
	 */

	private function addFormModelGoogleCaptcha()
	{
		$labelSiteKey = __('Site Key', 'onoffice-for-wp-websites');
		$labelSecretKey = __('Secret Key', 'onoffice-for-wp-websites');
		$pInputModelCaptchaSiteKey = new InputModelOption
			('onoffice-settings', 'captcha-sitekey', $labelSiteKey, 'string');
		$optionNameKey = $pInputModelCaptchaSiteKey->getIdentifier();
		$pInputModelCaptchaSiteKey->setValue(get_option($optionNameKey));
		$pInputModelCaptchaPageSecret = new InputModelOption
			('onoffice-settings', 'captcha-secretkey', $labelSecretKey, 'string');
		$pInputModelCaptchaPageSecret->setIsPassword(true);
		$optionNameSecret = $pInputModelCaptchaPageSecret->getIdentifier();
		$pInputModelCaptchaPageSecret->setSanitizeCallback(function($password) use ($optionNameSecret) {
			return $this->checkPassword($password, $optionNameSecret);
		});

		$pInputModelCaptchaPageSecret->setValue
			(get_option($optionNameSecret, $pInputModelCaptchaPageSecret->getDefault()));

		$pFormModel = new FormModel();
		$pFormModel->addInputModel($pInputModelCaptchaSiteKey);
		$pFormModel->addInputModel($pInputModelCaptchaPageSecret);
		$pFormModel->setGroupSlug('onoffice-google-recaptcha');
		$pFormModel->setPageSlug($this->getPageSlug());
		$pFormModel->setLabel(__('Google reCAPTCHA', 'onoffice-for-wp-websites'));
		$pFormModel->setTextCallback(function() {
			$this->renderTestFormReCaptcha();
		});

		$this->addFormModel($pFormModel);
	}

	/**
	 *
	 */

	private function addFormModelSocialMetaData()
	{
		$labelOpenGraph = __('Enable Open Graph', 'onoffice-for-wp-websites');
		$pInputModelOpenGraph = new InputModelOption
			('onoffice-settings', 'opengraph', $labelOpenGraph, InputModelOption::SETTING_TYPE_BOOLEAN);
		$pInputModelOpenGraph->setValuesAvailable(1);
		$pInputModelOpenGraph->setHtmlType(InputModelOption::HTML_TYPE_TOGGLE_SWITCH);
		$pInputModelOpenGraph->setValue(get_option($pInputModelOpenGraph->getIdentifier()));

		$labelTwitterCards = __('Enable Twitter Cards', 'onoffice-for-wp-websites');
		$pInputModelTwitterCards = new InputModelOption
			('onoffice-settings', 'twittercards', $labelTwitterCards, InputModelOption::SETTING_TYPE_BOOLEAN);
		$pInputModelTwitterCards->setValuesAvailable(1);
		$pInputModelTwitterCards->setHtmlType(InputModelOption::HTML_TYPE_TOGGLE_SWITCH);
		$pInputModelTwitterCards->setValue(get_option($pInputModelTwitterCards->getIdentifier()));

		$pFormModel = new FormModel();
		$pFormModel->addInputModel($pInputModelTwitterCards);
		$pFormModel->addInputModel($pInputModelOpenGraph);
		$pFormModel->setGroupSlug('onoffice-settings');
		$pFormModel->setPageSlug($this->getPageSlug());
		$pFormModel->setLabel(__('Social MetaData', 'onoffice-for-wp-websites'));

		$this->addFormModel($pFormModel);
	}

	/**
	 *
	 */

	private function addFormModelGoogleMapsKey()
	{
		$labelgoogleMapsKey = __('Google Maps Key', 'onoffice-for-wp-websites');
		$pInputModelGoogleMapsKey = new InputModelOption
				('onoffice-settings', 'googlemaps-key', $labelgoogleMapsKey, 'string');
		$optionMapKey = $pInputModelGoogleMapsKey->getIdentifier();
		$pInputModelGoogleMapsKey->setValue(get_option($optionMapKey));

		$pFormModel = new FormModel();
		$pFormModel->addInputModel($pInputModelGoogleMapsKey);
		$pFormModel->setGroupSlug('onoffice-google-maps-key');
		$pFormModel->setPageSlug($this->getPageSlug());
		$pFormModel->setLabel(__('Google Maps Key', 'onoffice-for-wp-websites'));

		$this->addFormModel($pFormModel);
	}

	private function addFormModelGoogleBotSettings()
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();
		$pAdminViewController = $pContainer->get(AdminViewController::class);
		$listPluginSEOActive = [];
		$listPluginSEOActive = $pAdminViewController->getPluginSEOActive();
		$listNamePluginSEO = implode(", ",$listPluginSEOActive);
		$titleDoNotModify = esc_html__("This plugin will not modify the title and description. This enables other plugins to manage those tags.",'onoffice-for-wp-websites');
		$summaryDetailDoNotModify = esc_html__( 'Custom fields', 'onoffice-for-wp-websites' );
		$descriptionDetailDoNotModify = sprintf(esc_html__( 'When this option is active, you can use special custom fields to use data from onOffice enterprise in your SEO plugins.
		To display the property number (fieldname %1$s) and title (fieldname %2$s) of the current estate in the detail page\'s title, you can use the custom fields %3$s and %4$s.
		For information on how to use custom fields consult you SEO plugin\'s documentation.', 'onoffice-for-wp-websites' ), '<code>objektnr_extern</code>', '<code>objekttitel</code>', '<code>onoffice_objektnr_extern</code>', '<code>onoffice_objekttitel</code>');
		$searchSupportDoNotModify = sprintf(esc_html__( 'To find out the fieldname of a field, look in the detail page\'s field list. When you expand a field, it shows you the "Key of Field" which you should put behind %s to form the custom field name. You can use any field that you have added to the estate field list on the right of the detail page.',
			'onoffice-for-wp-websites' ), '<code>onoffice_</code>');
		$titleEllipsisDoNotModify = sprintf(esc_html__( 'If you need to shorten a field to e.g. 50 characters, you can use %1$s. This will shorten the estate\'s description to at most 50 characters, including the ellipsis. It will not cut words in the middle, but leave them out if they are too long, so "This is too loooong" will never become "This is too looo…" but rather "This is too…". To display up to 150 characters, you can use %2$s.',
			'onoffice-for-wp-websites' ), '<code>onoffice_ellipsis50_objektbeschreibung</code>', '<code>onoffice_ellipsis150_objektbeschreibung</code>');
		$titleDescriptionDoNotModify = sprintf(esc_html__( 'The title and description of the detail page are set using the %1$s and %2$s tags. They make it possible to show a summary of the page when you share a link.',
			'onoffice-for-wp-websites' ), '<code>&lt;title&gt;</code>', '<code>&lt;meta name="description&gt;</code>');
		$descriptionDoNotModify = sprintf( '<div class="do-not-modify">
									<p>%1$s</p>
									<details>
									<summary>%2$s</summary>
									<p>%3$s</p>
									</br>
									<p>%4$s</p>
									</br>
									<p>%5$s</p>
									</details>
									</br>
							</div> <p>%6$s</p>', $titleDoNotModify, $summaryDetailDoNotModify,
			$descriptionDetailDoNotModify, $searchSupportDoNotModify, $titleEllipsisDoNotModify,
			$titleDescriptionDoNotModify );
		$messageNoticeSEO = sprintf(esc_html__('We have detected an active SEO plugin: %s. This option can lead to conflicts with the SEO plugin.
								We recommend that you configure the onOffice plugin to not modify the title and description.','onoffice-for-wp-websites'), $listNamePluginSEO);
		$messageNoticeSEO =  Parsedown::instance()
			->setBreaksEnabled(true)->text(
				$messageNoticeSEO
			);
		$descriptionNoticeSeo = sprintf('<div id="notice-seo">%s</div>', $messageNoticeSEO);
		$descriptionFillOut = '<p class="description-notice">
					'.esc_html__("This plugin will fill out the title and description with the information from the estate that is shown. This option is recommended if you are not using a SEO plugin.",'onoffice-for-wp-websites').'
				 </p>';
		if ( count($listPluginSEOActive) > 0 && get_option('onoffice-settings-title-and-description') == 0) {
			$descriptionFillOut = $descriptionNoticeSeo.$descriptionFillOut;
		}
		$labelGoogleBotIndexPdfExpose = __('Allow indexing of PDF brochures', 'onoffice-for-wp-websites');
		$pInputModeGoogleBotIndexPdfExpose = new InputModelOption('onoffice-settings', 'google-bot-index-pdf-expose',
			$labelGoogleBotIndexPdfExpose, InputModelOption::SETTING_TYPE_BOOLEAN);
		$pInputModeGoogleBotIndexPdfExpose->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModeGoogleBotIndexPdfExpose->setValuesAvailable(1);
		$pInputModeGoogleBotIndexPdfExpose->setValue(get_option($pInputModeGoogleBotIndexPdfExpose->getIdentifier()) == 1);
		$pInputModeGoogleBotIndexPdfExpose->setDescriptionTextHTML(__('If you allow indexing, your search engine ranking can be negatively affected and your brochures can be available from search engines even months after the corresponding estate is deleted.','onoffice-for-wp-websites'));
		$labelTitleAndDescription = __('Title and description', 'onoffice-for-wp-websites');
		$pInputModeTitleAndDescription = new InputModelOption('onoffice-settings', 'title-and-description',
			$labelTitleAndDescription, InputModelOption::SETTING_TYPE_NUMBER);
		$pInputModeTitleAndDescription->setHtmlType(InputModelOption::HTML_TYPE_RADIO);
		$pInputModeTitleAndDescription->setValuesAvailable([
			__('Fill out', 'onoffice-for-wp-websites'),
			__('Do not modify', 'onoffice-for-wp-websites'),
		]);
		if(get_option('onoffice-settings-title-and-description')){
			update_option('onoffice-click-button-close-action', 0);
		};

		$pInputModeTitleAndDescription->setValue(get_option($pInputModeTitleAndDescription->getIdentifier()));
		$pInputModeTitleAndDescription->setDescriptionRadioTextHTML([
			$descriptionFillOut,$descriptionDoNotModify
		]);


		$pFormModel = new FormModel();
		$pFormModel->addInputModel($pInputModeTitleAndDescription);
		$pFormModel->addInputModel($pInputModeGoogleBotIndexPdfExpose);
		$pFormModel->setGroupSlug('onoffice-google-bot');
		$pFormModel->setPageSlug($this->getPageSlug());
		$pFormModel->setLabel(__('SEO', 'onoffice-for-wp-websites'));

		$this->addFormModel($pFormModel);
	}

	/**
	 *
	 * @param string $password
	 * @return bool
	 *
	 */

	public function checkPassword($password, $optionName)
	{
		return $password != '' ? $password : get_option($optionName);
	}

	/**
	 * @param $password
	 * @return string
	 */
	public function encrypteCredentials(string $password)
	{
		if ($password && defined('ONOFFICE_CREDENTIALS_ENC_KEY') && ONOFFICE_CREDENTIALS_ENC_KEY) {
			$password = $this->_encrypter->encrypt($password, ONOFFICE_CREDENTIALS_ENC_KEY);
			update_option('onoffice-is-encryptcredent', true);
		}
		return $password;
	}


	/**
	 *
	 */

	public function renderTestFormReCaptcha()
	{
		$tokenOptions = get_option('onoffice-settings-captcha-sitekey', '');
		$secretOptions = get_option('onoffice-settings-captcha-secretkey', '');
		$stringTranslations = [
			'response_ok' => __('The keys are OK.', 'onoffice-for-wp-websites'),
			'response_error' => __('There was an error:', 'onoffice-for-wp-websites'),
			'missing-input-secret' => __('The secret parameter is missing.', 'onoffice-for-wp-websites'),
			'invalid-input-secret' => __('The secret parameter is invalid or malformed.', 'onoffice-for-wp-websites'),
			'missing-input-response' => __('The response parameter is missing.', 'onoffice-for-wp-websites'),
			'invalid-input-response' => __('The response parameter is invalid or malformed.', 'onoffice-for-wp-websites'),
			'bad-request' => __('The request is invalid or malformed.', 'onoffice-for-wp-websites'),
		];

		if ($tokenOptions !== '' && $secretOptions !== '') {
			$template = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'resource'
				.DIRECTORY_SEPARATOR.'CaptchaTestForm.html');
			printf($template,
				json_encode(admin_url('admin-ajax.php')),
				json_encode($stringTranslations),
				esc_html($tokenOptions));
		} else {
			echo __('In order to use Google reCAPTCHA, you need to provide your keys. '
				.'You\'re free to enable it in the form settings for later use.', 'onoffice-for-wp-websites');
		}
	}


	/**
	 *
	 */

	public function renderContent()
	{
		$this->generatePageMainTitle(__('Settings', 'onoffice-for-wp-websites'));

		echo '<form method="post" action="options.php">';

		/* @var $pInputModelRenderer InputModelRenderer */
		$pInputModelRenderer = $this->getContainer()->get(InputModelRenderer::class);

		foreach ($this->getFormModels() as $pFormModel) {
			$pInputModelRenderer->buildForm($pFormModel);
		}

		settings_fields($this->getPageSlug());
		do_settings_sections($this->getPageSlug());

		submit_button(__('Save changes and clear API cache', 'onoffice-for-wp-websites'));
		echo '</form>';
	}


	/**
	 *
	 */

	public function displayCacheClearSuccess()
	{
		$class = 'notice notice-success is-dismissible';
		$message = __('The cache was cleaned.', 'onoffice-for-wp-websites');

		printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
	}

    /**
     *
     * @param string $pageSlug
     *
     */

    private function addFormModelMapProvider(string $pageSlug)
    {
        $groupSlugMaps = 'onoffice-maps';
        $mapProviderLabel = __('Map Provider', 'onoffice-for-wp-websites');
        $pInputModelMapProvider = new InputModelOption($groupSlugMaps, 'mapprovider',
            $mapProviderLabel, InputModelOption::SETTING_TYPE_NUMBER);
        $pInputModelMapProvider->setHtmlType(InputModelOption::HTML_TYPE_RADIO);
        $selectedValue = get_option($pInputModelMapProvider->getIdentifier(), MapProvider::PROVIDER_DEFAULT);
        $pInputModelMapProvider->setValue($selectedValue);
        $pInputModelMapProvider->setValuesAvailable([
            MapProvider::OPEN_STREET_MAPS => __('OpenStreetMap', 'onoffice-for-wp-websites'),
            MapProvider::GOOGLE_MAPS => __('Google Maps', 'onoffice-for-wp-websites'),
        ]);

        $pFormModel = new FormModel();
        $pFormModel->addInputModel($pInputModelMapProvider);
        $pFormModel->setGroupSlug($groupSlugMaps);
        $pFormModel->setPageSlug($pageSlug);
        $pFormModel->setLabel(__('Maps', 'onoffice-for-wp-websites'));

        $this->addFormModel($pFormModel);
    }

    /**
     *
     * @param string $pageSlug
     *
     */
    private function addFormModelDetailView(string $pageSlug)
    {
        $groupSlugView = 'onoffice-detail-view';
        $showTitleInUrl = __('Show title in URL', 'onoffice-for-wp-websites');
        $pInputModelShowTitleUrl = new InputModelOption($groupSlugView, 'showTitleUrl',
            $showTitleInUrl, InputModelOption::SETTING_TYPE_BOOLEAN);
        $pInputModelShowTitleUrl->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
        $pInputModelShowTitleUrl->setValuesAvailable(1);
        $pInputModelShowTitleUrl->setValue(get_option($pInputModelShowTitleUrl->getIdentifier()) == 1);
        $pInputModelShowTitleUrl->setDescriptionTextHTML(__('If this checkbox is selected, the title of the property will be part of the URLs of the detail views. The title is placed after the record number, e.g. <code>/1234-nice-location-with-view</code>. No more than the first five words of the title are used.', 'onoffice-for-wp-websites'));

        $pFormModel = new FormModel();
        $pFormModel->addInputModel($pInputModelShowTitleUrl);
        $pFormModel->setGroupSlug($groupSlugView);
        $pFormModel->setPageSlug($pageSlug);
        $pFormModel->setLabel(__('Detail View URLs', 'onoffice-for-wp-websites'));

        $this->addFormModel($pFormModel);
    }

	/**
	 *
	 */

	private function addFormModelHoneypot()
	{
		$labelSiteKey = __('Add honeypot to forms', 'onoffice-for-wp-websites');
		$pInputModelHoneypot = new InputModelOption
			('onoffice-settings', 'honeypot', $labelSiteKey, InputModelOption::SETTING_TYPE_BOOLEAN);
		$optionNameKey = $pInputModelHoneypot->getIdentifier();
		$pInputModelHoneypot->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelHoneypot->setValuesAvailable(1);
		$pInputModelHoneypot->setValue(get_option($optionNameKey));
		$pInputModelHoneypot->setDescriptionTextHTML( __( 'A honeypot is an invisible field to trick spam bots. Note that when a bot is detected, it will pretend the form was sent successfully. We recommend to keep this active, but you can deactivate it if it causes any problems.',
			'onoffice-for-wp-websites' ) );

		$pFormModel = new FormModel();
		$pFormModel->addInputModel($pInputModelHoneypot);
		$pFormModel->setGroupSlug('onoffice-honeypot-form');
		$pFormModel->setPageSlug($this->getPageSlug());
		$pFormModel->setLabel(__('Forms', 'onoffice-for-wp-websites'));

		$this->addFormModel($pFormModel);
	}

    /**
     *
     * @param string $pageSlug
     *
     */
    private function addFormModelFavorites(string $pageSlug)
    {
        $groupSlugFavs = 'onoffice-favorization';
        $enableFavLabel = __('Enable Watchlist', 'onoffice-for-wp-websites');
        $favButtonLabel = __('Expression used', 'onoffice-for-wp-websites');
        $pInputModelEnableFav = new InputModelOption($groupSlugFavs, 'enableFav',
            $enableFavLabel, InputModelOption::SETTING_TYPE_BOOLEAN);
        $pInputModelEnableFav->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
        $pInputModelEnableFav->setValuesAvailable(1);
        $pInputModelEnableFav->setValue(get_option($pInputModelEnableFav->getIdentifier()) == 1);
        $pInputModelFavButtonLabel = new InputModelOption($groupSlugFavs, 'favButtonLabelFav',
            $favButtonLabel, InputModelOption::SETTING_TYPE_NUMBER);
        $pInputModelFavButtonLabel->setHtmlType(InputModelOption::HTML_TYPE_RADIO);
        $pInputModelFavButtonLabel->setValue(get_option($pInputModelFavButtonLabel->getIdentifier()));
        $pInputModelFavButtonLabel->setValuesAvailable([
            Favorites::KEY_SETTING_MEMORIZE => __('Watchlist', 'onoffice-for-wp-websites'),
            Favorites::KEY_SETTING_FAVORIZE => __('Favorites', 'onoffice-for-wp-websites'),
        ]);

        $pFormModel = new FormModel();
        $pFormModel->addInputModel($pInputModelEnableFav);
        $pFormModel->addInputModel($pInputModelFavButtonLabel);
        $pFormModel->setGroupSlug($groupSlugFavs);
        $pFormModel->setPageSlug($pageSlug);
        $pFormModel->setLabel(__('Watchlist', 'onoffice-for-wp-websites'));

        $this->addFormModel($pFormModel);
    }

    /**
     * @param string $pageSlug
     */
    private function addFormModelPagination(string $pageSlug)
    {
        $groupSlugPaging = 'onoffice-pagination';
        $pagingLabel = __('Pagination', 'onoffice-for-wp-websites');
        $pInputModelPagingProvider = new InputModelOption($groupSlugPaging, 'paginationbyonoffice',
            $pagingLabel, InputModelOption::SETTING_TYPE_NUMBER);
        $pInputModelPagingProvider->setHtmlType(InputModelOption::HTML_TYPE_RADIO);
        $selectedValue = get_option($pInputModelPagingProvider->getIdentifier(), 0);
        $pInputModelPagingProvider->setValue($selectedValue);
        $pInputModelPagingProvider->setValuesAvailable([
            0 => __('By WP Theme', 'onoffice-for-wp-websites'),
            1 => __('By onOffice-Plugin', 'onoffice-for-wp-websites')
        ]);
        $pFormModel = new FormModel();
        $pFormModel->addInputModel($pInputModelPagingProvider);
        $pFormModel->setGroupSlug($groupSlugPaging);
        $pFormModel->setPageSlug($pageSlug);
        $pFormModel->setLabel($pagingLabel);

        $this->addFormModel($pFormModel);
    }
}
