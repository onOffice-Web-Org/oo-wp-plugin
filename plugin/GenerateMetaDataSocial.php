<?php

/**
 *
 *    Copyright (C) 2023 onOffice Software AG
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

namespace onOffice\WPlugin;

/**
 *
 */

class GenerateMetaDataSocial
{

	/** */
	const OPEN_GRAPH_KEY = 'og';

	/** */
	const OPEN_GRAPH_DESCRIPTION_LIMIT_CHARACTER = 300;

	/** */
	const OPEN_GRAPH_TITLE_LIMIT_CHARACTER = 60;

	/** */
	const OPEN_GRAPH_WEBSITE_TYPE = 'website';

	/** */
	const OPEN_GRAPH_DEFAULT_IMAGE_WIDTH = 1200;

	/** */
	const OPEN_GRAPH_DEFAULT_IMAGE_HEIGHT = 630;

	/** */
	const TWITTER_KEY = 'twitter';

	/** */
	const TWITTER_DESCRIPTION_LIMIT_CHARACTER = 200;

	/** */
	const TWITTER_TITLE_LIMIT_CHARACTER = 70;

	/** */
	const FACEBOOK_LOCALES = [
		'af_ZA',
		'ak_GH',
		'am_ET',
		'ar_AR',
		'as_IN',
		'ay_BO',
		'az_AZ',
		'be_BY',
		'bg_BG',
		'bp_IN',
		'bn_IN',
		'br_FR',
		'bs_BA',
		'ca_ES',
		'cb_IQ',
		'ck_US',
		'co_FR',
		'cs_CZ',
		'cx_PH',
		'cy_GB',
		'da_DK',
		'de_DE',
		'el_GR',
		'en_GB',
		'en_PI',
		'en_UD',
		'en_US',
		'em_ZM',
		'eo_EO',
		'es_ES',
		'es_LA',
		'es_MX',
		'et_EE',
		'eu_ES',
		'fa_IR',
		'fb_LT',
		'ff_NG',
		'fi_FI',
		'fo_FO',
		'fr_CA',
		'fr_FR',
		'fy_NL',
		'ga_IE',
		'gl_ES',
		'gn_PY',
		'gu_IN',
		'gx_GR',
		'ha_NG',
		'he_IL',
		'hi_IN',
		'hr_HR',
		'hu_HU',
		'ht_HT',
		'hy_AM',
		'id_ID',
		'ig_NG',
		'is_IS',
		'it_IT',
		'ik_US',
		'iu_CA',
		'ja_JP',
		'ja_KS',
		'jv_ID',
		'ka_GE',
		'kk_KZ',
		'km_KH',
		'kn_IN',
		'ko_KR',
		'ks_IN',
		'ku_TR',
		'ky_KG',
		'la_VA',
		'lg_UG',
		'li_NL',
		'ln_CD',
		'lo_LA',
		'lt_LT',
		'lv_LV',
		'mg_MG',
		'mi_NZ',
		'mk_MK',
		'ml_IN',
		'mn_MN',
		'mr_IN',
		'ms_MY',
		'mt_MT',
		'my_MM',
		'nb_NO',
		'nd_ZW',
		'ne_NP',
		'nl_BE',
		'nl_NL',
		'nn_NO',
		'nr_ZA',
		'ns_ZA',
		'ny_MW',
		'om_ET',
		'or_IN',
		'pa_IN',
		'pl_PL',
		'ps_AF',
		'pt_BR',
		'pt_PT',
		'qc_GT',
		'qu_PE',
		'qr_GR',
		'qz_MM',
		'rm_CH',
		'ro_RO',
		'ru_RU',
		'rw_RW',
		'sa_IN',
		'sc_IT',
		'se_NO',
		'si_LK',
		'su_ID',
		'sk_SK',
		'sl_SI',
		'sn_ZW',
		'so_SO',
		'sq_AL',
		'sr_RS',
		'ss_SZ',
		'st_ZA',
		'sv_SE',
		'sw_KE',
		'sy_SY',
		'sz_PL',
		'ta_IN',
		'te_IN',
		'tg_TJ',
		'th_TH',
		'tk_TM',
		'tl_PH',
		'tl_ST',
		'tn_BW',
		'tr_TR',
		'ts_ZA',
		'tt_RU',
		'tz_MA',
		'uk_UA',
		'ur_PK',
		'uz_UZ',
		've_ZA',
		'vi_VN',
		'wo_SN',
		'xh_ZA',
		'yi_DE',
		'yo_NG',
		'zh_CN',
		'zh_HK',
		'zh_TW',
		'zu_ZA',
		'zz_TR',
	];

	/**
	 * @param array $valueMetaData
	 * @param array $socialKey
	 * @return array
	 */
	public function generateMetaDataSocial(array $valueMetaData, array $socialKey): array
	{
		$metaData = [];
		switch ($socialKey) {
			case [self::OPEN_GRAPH_KEY]:
				$metaData = $this->generateOpenGraphData($valueMetaData);
				break;
			case [self::TWITTER_KEY]:
				$metaData = $this->generateTwitterCard($valueMetaData);
				break;
		}

		return $metaData;
	}

	/**
	 * @param array $valueMetaData
	 * @return array
	 */
	private function generateOpenGraphData(array $valueMetaData): array
	{
		$openGraphData = [];
		$metaDataOpenGraphSupport = ['title', 'description', 'image', 'image:width', 'image:height', 'url', 'type', 'locale', 'site_name'];
		$checkImageExist = false;

		foreach ($metaDataOpenGraphSupport as $key) {
			switch ($key) {
				case 'title':
					$title = $valueMetaData[$key];
					if (!empty($title)) {
						$limitTitle = $this->limitCharacter($title, self::OPEN_GRAPH_TITLE_LIMIT_CHARACTER);
						$titleTag = $this->generateMetaDataItem($key, $limitTitle);
						$openGraphData = array_merge($openGraphData, $titleTag);
					}
					break;
				case 'description':
					$description = $valueMetaData[$key];
					if (!empty($description)) {
						$limitDescription = $this->limitCharacter($description, self::OPEN_GRAPH_DESCRIPTION_LIMIT_CHARACTER);
						$descriptionTag = $this->generateMetaDataItem($key, $limitDescription);
						$openGraphData = array_merge($openGraphData, $descriptionTag);
					}
					break;
				case 'image':
					$image = $valueMetaData[$key];
					if (!empty($image)) {
						$imageTag = $this->generateMetaDataItem($key, $image);
						$openGraphData = array_merge($openGraphData, $imageTag);
						$checkImageExist = true;
					}
					break;
				case 'image:width':
					if ($checkImageExist) {
						$imageWidthTag = $this->generateMetaDataItem($key, self::OPEN_GRAPH_DEFAULT_IMAGE_WIDTH);
						$openGraphData = array_merge($openGraphData, $imageWidthTag);
					}
					break;
				case 'image:height':
					if ($checkImageExist) {
						$imageHeightTag = $this->generateMetaDataItem($key, self::OPEN_GRAPH_DEFAULT_IMAGE_HEIGHT);
						$openGraphData = array_merge($openGraphData, $imageHeightTag);
					}
					break;
				case 'type':
					$typeTag = $this->generateMetaDataItem($key, self::OPEN_GRAPH_WEBSITE_TYPE);
					$openGraphData = array_merge($openGraphData, $typeTag);
					break;
				case 'url':
					$url = $valueMetaData[$key];
					$urlTag = $this->generateMetaDataItem($key, $url);
					$openGraphData = array_merge($openGraphData, $urlTag);
					break;
				case 'locale':
					$locale = $this->locale();
					$localeTag = $this->generateMetaDataItem($key, $locale);
					$openGraphData = array_merge($openGraphData, $localeTag);
					break;
				case 'site_name':
					$siteName = get_bloginfo('name');
					$siteNameTag = $this->generateMetaDataItem($key, $siteName);
					$openGraphData = array_merge($openGraphData, $siteNameTag);
					break;
				default:
					break;
			}
		}

		return $openGraphData;
	}

	/**
	 * @param array $valueMetaData
	 * @return array
	 */
	private function generateTwitterCard(array $valueMetaData): array
	{
		$twitterCard = [];
		$twitterCardSupport = ['title', 'description', 'image'];

		foreach ($twitterCardSupport as $key) {
			switch ($key) {
				case 'title':
					$title = $valueMetaData[$key];
					if (!empty($title)) {
						$limitTitle = $this->limitCharacter($title, self::TWITTER_TITLE_LIMIT_CHARACTER);
						$titleTag = $this->generateMetaDataItem($key, $limitTitle);
						$twitterCard = array_merge($twitterCard, $titleTag);
					}
					break;
				case 'description':
					$description = $valueMetaData[$key];
					if (!empty($description)) {
						$limitDescription = $this->limitCharacter($description, self::TWITTER_DESCRIPTION_LIMIT_CHARACTER);
						$descriptionTag = $this->generateMetaDataItem($key, $limitDescription);
						$twitterCard = array_merge($twitterCard, $descriptionTag);
					}
					break;
				case 'image':
					$image = $valueMetaData[$key];
					if (!empty($image)) {
						$imageTag = $this->generateMetaDataItem($key, $image);
						$twitterCard = array_merge($twitterCard, $imageTag);
					}
					break;
				default:
					break;
			}
		}

		return $twitterCard;
	}

	/**
	 * @param string $metaTagKey
	 * @param string $valueMetaDataItem
	 * @return array
	 */
	private function generateMetaDataItem(string $metaTagKey, string $valueMetaDataItem): array
	{
		$metaDataSocialItem = [];
		$metaDataSocialItem[$metaTagKey] = $valueMetaDataItem;

		return $metaDataSocialItem;
	}

	/**
	 * @param string $text
	 * @param int $limitCharacter
	 * @return string
	 */
	private function limitCharacter(string $text, int $limitCharacter): string
	{
		if (strlen($text) > $limitCharacter) {
			$shortenedText = substr($text, 0, $limitCharacter);
			if (substr($text, $limitCharacter, 1) != ' ') {
				$shortenedText = substr($shortenedText, 0, strrpos($shortenedText, ' '));
			}
			$text = $shortenedText;
		}

		return $text;
	}

	/**
	 * @return string
	 */
	private function locale(): string
	{
		$locale = get_locale();
		$locale = $this->convertFacebookLocaleFormat($locale);
		$locale = $this->validate($locale);

		return $locale;
	}

	/**
	 * @param string $locale
	 * @return string
	 */
	private function convertFacebookLocaleFormat(string $locale): string
	{
		$notCorrectFormatLocales = [
			'ca' => 'ca_ES',
			'en' => 'en_US',
			'el' => 'el_GR',
			'et' => 'et_EE',
			'ja' => 'ja_JP',
			'sq' => 'sq_AL',
			'uk' => 'uk_UA',
			'vi' => 'vi_VN',
			'zh' => 'zh_CN',
			'te' => 'te_IN',
			'ur' => 'ur_PK',
			'cy' => 'cy_GB',
			'eu' => 'eu_ES',
			'th' => 'th_TH',
			'af' => 'af_ZA',
			'hy' => 'hy_AM',
			'gu' => 'gu_IN',
			'kn' => 'kn_IN',
			'mr' => 'mr_IN',
			'kk' => 'kk_KZ',
			'lv' => 'lv_LV',
			'sw' => 'sw_KE',
			'tl' => 'tl_PH',
			'ps' => 'ps_AF',
			'as' => 'as_IN',
		];

		if (isset($notCorrectFormatLocales[$locale])) {
			$locale = $notCorrectFormatLocales[$locale];
		}

		if (2 === strlen($locale)) {
			$locale = $this->join($locale);
		}

		return $locale;
	}

	/**
	 * @param string $locale
	 * @return string
	 */
	private function validate(string $locale): string
	{
		if (in_array($locale, self::FACEBOOK_LOCALES, true)) {
			return $locale;
		}

		$locale = $this->join(substr($locale, 0, 2));

		return in_array($locale, self::FACEBOOK_LOCALES, true) ? $locale : 'en_US';
	}

	/**
	 *
	 * @param string $locale
	 *
	 * @return string
	 */
	private function join(string $locale): string
	{
		return strtolower($locale) . '_' . strtoupper($locale);
	}
}