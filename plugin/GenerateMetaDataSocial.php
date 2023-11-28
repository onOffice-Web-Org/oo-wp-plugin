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
	const TWITTER_KEY = 'twitter';

	/** */
	const TWITTER_DESCRIPTION_LIMIT_CHARACTER = 200;

	/** */
	const TWITTER_TITLE_LIMIT_CHARACTER = 70;

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
		$metaDataOpenGraphSupport = ['title', 'description', 'image', 'url', 'type', 'locale', 'site_name'];

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
					$locale = get_locale();
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
}