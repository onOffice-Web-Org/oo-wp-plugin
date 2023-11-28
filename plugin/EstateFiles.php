<?php

/**
 *
 *    Copyright (C) 2016 onOffice Software AG
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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2016, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

use onOffice\SDK\Exception\HttpFetchNoResultException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Types\ImageTypes;
use onOffice\WPlugin\Types\LinksTypes;
use onOffice\WPlugin\Types\MovieLinkTypes;
use onOffice\WPlugin\Utility\__String;
use function esc_url;

/**
 *
 */

class EstateFiles
{
	/** @var array */
	private $_estateFiles = array();

	/** @var array */
	private $_pictureCategories = array();

	/**
	 * EstateFiles constructor.
	 *
	 * @param string[] $pictureCategories
	 * @param array $estateIds
	 * @param SDKWrapper $pSDKWrapper
	 * @throws API\APIEmptyResultException
	 * @throws HttpFetchNoResultException
	 */

	public function getAllFiles(array $pictureCategories, array $estateIds, SDKWrapper $pSDKWrapper)
	{
		$this->_pictureCategories = $pictureCategories;

		if (count($pictureCategories) > 0) {
			$pAPIClientAction = new APIClientActionGeneric(
				$pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'estatepictures');
			$pAPIClientAction->setParameters([
				'estateids' => array_values($estateIds),
				'categories' => $this->_pictureCategories,
				'language' => Language::getDefault(),
			]);

			$pAPIClientAction->addRequestToQueue()->sendRequests();

			if (!$pAPIClientAction->getResultStatus()) {
				throw new HttpFetchNoResultException();
			}
			$this->collectEstateFiles($pAPIClientAction->getResultRecords());
		}
	}


	/**
	 *
	 * @param array $responseArrayEstatePictures
	 *
	 */

	private function collectEstateFiles($responseArrayEstatePictures)
	{
		foreach ($responseArrayEstatePictures as $fileEntry) {
			$fileId = $fileEntry['id'];
			foreach ($fileEntry['elements'] as $properties) {
				$estateId = $properties['estateid'];

				$image = array(
					'id' => $fileId,
					'url' => $this->correctUrl($properties['url']),
					'title' => $properties['title'],
					'text' => $properties['text'],
					'type' => $properties['type'],
				);

				$this->_estateFiles[$estateId][$fileId] = $image;
			}
		}
	}


	/**
	 *
	 * @param string $url
	 * @return string
	 *
	 */

	private function correctUrl($url)
	{
		$pUrlStr = __String::getNew($url);
		if (!$pUrlStr->startsWith('http://') && !$pUrlStr->startsWith('https://'))
		{
			$url = 'http://'.$url;
		}

		return $url;
	}


	/**
	 *
	 * @param int $estateId
	 * @return array
	 *
	 */

	public function getEstatePictures($estateId): array
	{
		$callback = ImageTypes::class.'::isImageType';
		return $this->getFilesOfTypeByCallback($estateId, $callback);
	}


	/**
	 *
	 * @param int $estateId
	 * @return array
	 *
	 */

	public function getEstateMovieLinks($estateId): array
	{
		$callback = MovieLinkTypes::class.'::isMovieLink';
		return $this->getFilesOfTypeByCallback($estateId, $callback);
	}


	/**
	 *
	 * @param int $estateId
	 * @return array
	 *
	 */

	public function getEstateLinks($estateId, $type = ''): array
	{
		$callback = null;

		switch ($type) {
			case 'ogulo':
				$callback = LinksTypes::class.'::isOguloLink';
				break;
			case 'object':
				$callback = LinksTypes::class.'::isObjectLink';
				break;
			case 'link':
				$callback = LinksTypes::class.'::isLink';
				break;
			default :
				break;
		}

		return empty($callback) ? [] : $this->getFilesOfTypeByCallback($estateId, $callback);
	}


	/**
	 *
	 * @param int $estateId
	 * @param string $callback
	 * @return array
	 *
	 */

	private function getFilesOfTypeByCallback($estateId, $callback): array
	{
		$result = [];
		$images = $this->_estateFiles[$estateId] ?? [];

		foreach ($images as $imageId => $imageProperties) {
			$resultCb = call_user_func($callback, $imageProperties['type']);
			if ($resultCb === true) {
				$result[$imageId] = $imageProperties;
			}
		}

		return $result;
	}


	/**
	 *
	 * @param int $fileId
	 * @param int $estateId
	 * @param array $options
	 * @return string
	 *
	 */

	public function getEstateFileUrl($fileId, $estateId, array $options = null)
	{
		$size = null;

		if (!is_null($options)) {
			$width = null;
			$height = null;

			if (array_key_exists('width', $options)) {
				$width = $options['width'];
			}

			if (array_key_exists('height', $options)) {
				$height = $options['height'];
			}

			$size = '@'.$width.'x'.$height; // values such as 'x300' or '300x' are totally okay
		}

		if (!empty($this->_estateFiles[$estateId][$fileId])) {
			return esc_url($this->_estateFiles[$estateId][$fileId]['url'].$size);
		}

		return null;
	}


	/**
	 *
	 * @param int $imageId
	 * @param int $estateId
	 * @return string
	 *
	 */

	public function getEstatePictureTitle($imageId, $estateId)
	{
		$value = $this->getEstatePictureValues($imageId, $estateId);
		$result = null;

		if ($value !== array()) {
			$result = $value['title'];
		}

		return $result;
	}


	/**
	 *
	 * @param int $imageId
	 * @param int $estateId
	 * @return string
	 *
	 */

	public function getEstatePictureText($imageId, $estateId)
	{
		$value = $this->getEstatePictureValues($imageId, $estateId);
		$result = null;

		if ( $value !== array() ) {
			$result = $value['text'];
		}

		return $result;
	}


	/**
	 *
	 * @param int $imageId
	 * @param int $estateId
	 * @return array
	 *
	 */

	public function getEstatePictureValues($imageId, $estateId)
	{
		return $this->_estateFiles[$estateId][$imageId] ?? [];
	}

	/**
	 * @return array
	 */
	public function getEstateFileInformation(): array
	{
		return $this->_estateFiles;
	}
}
