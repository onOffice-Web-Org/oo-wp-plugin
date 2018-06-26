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
use onOffice\WPlugin\Types\ImageTypes;
use onOffice\WPlugin\Types\MovieLinkTypes;
use onOffice\WPlugin\Utility\__String;

/**
 *
 */

class EstateFiles
{
	/** @var array */
	private $_estateFiles = array();

	/** @var int */
	private $_handleEstatePictures = null;

	/** @var array */
	private $_pictureCategories = array();


	/**
	 *
	 * @param string[] $pictureCategories
	 *
	 */

	public function __construct( array $pictureCategories )
	{
		$this->_pictureCategories = $pictureCategories;

		if (count($pictureCategories) > 0) {
			add_action('oo_beforeEstateRelations', array($this, 'registerRequest'), 10, 2);
			add_action('oo_afterEstateRelations', array($this, 'parseRequest'), 10, 1);
		}
	}


	/**
	 *
	 * @param SDKWrapper $pSDKWrapper
	 * @param array $estateIds
	 *
	 */

	public function registerRequest( SDKWrapper $pSDKWrapper, array $estateIds )
	{
		$this->_handleEstatePictures = $pSDKWrapper->addRequest(
			onOfficeSDK::ACTION_ID_GET, 'estatepictures', array(
				'estateids' => array_values($estateIds),
				'categories' => $this->_pictureCategories,
				'language' => Language::getDefault(),
			)
		);
	}


	/**
	 *
	 * @param SDKWrapper $pSDKWrapper
	 *
	 */

	public function parseRequest( SDKWrapper $pSDKWrapper)
	{
		$responseArrayEstatePictures = $pSDKWrapper->getRequestResponse(
			$this->_handleEstatePictures );
		$this->collectEstateFiles( $responseArrayEstatePictures );
	}


	/**
	 *
	 * @param array $responseArrayEstatePictures
	 * @throws HttpFetchNoResultException
	 *
	 */

	private function collectEstateFiles( $responseArrayEstatePictures )
	{
		if ( ! isset( $responseArrayEstatePictures['data']['records'] ) ) {
			throw new HttpFetchNoResultException();
		}

		$records = $responseArrayEstatePictures['data']['records'];

		foreach ($records as $fileEntry) {
			$fileId = $fileEntry['id'];
			foreach ( $fileEntry['elements'] as $properties ) {
				$estateId = $properties['estateid'];
				$imageType = $properties['type'];
				$imageUrl = $properties['url'];
				$imageText = $properties['text'];
				$imageTitle = $properties['title'];

				$image = array(
					'id' => $fileId,
					'url' => $this->correctUrl($imageUrl),
					'title' => $imageTitle,
					'text' => $imageText,
					'type' => $imageType,
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

	public function getEstatePictures( $estateId )
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

	public function getEstateMovieLinks( $estateId )
	{
		$callback = MovieLinkTypes::class.'::isMovieLink';
		return $this->getFilesOfTypeByCallback($estateId, $callback);
	}


	/**
	 *
	 * @param int $estateId
	 * @param string $callback
	 * @return array
	 *
	 */

	private function getFilesOfTypeByCallback($estateId, $callback)
	{
		$result = array();

		if ( isset($this->_estateFiles[$estateId]) ) {
			$images = $this->_estateFiles[$estateId];

			foreach ($images as $imageId => $imageProperties) {
				$resultCb = call_user_func($callback, $imageProperties['type']);
				if ($resultCb === true) {
					$result[$imageId] = $imageProperties;
				}
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

	public function getEstateFileUrl( $fileId, $estateId, array $options = null )
	{
		$size = null;

		if ( ! is_null($options) ) {
			$width = null;
			$height = null;

			if ( array_key_exists( 'width', $options ) ) {
				$width = $options['width'];
			}

			if ( array_key_exists( 'height', $options ) ) {
				$height = $options['height'];
			}

			$size = '@'.$width.'x'.$height; // values such as 'x300' or '300x' are totally okay
		}

		if ( ! empty( $this->_estateFiles[$estateId][$fileId] ) ) {
			return esc_url( $this->_estateFiles[$estateId][$fileId]['url'].$size );
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

	public function getEstatePictureTitle( $imageId, $estateId )
	{
		$value = $this->getEstatePictureValues($imageId, $estateId);
		$result = null;

		if ( $value !== array() ) {
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

	public function getEstatePictureText( $imageId, $estateId )
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

	public function getEstatePictureValues( $imageId, $estateId )
	{
		if ( isset( $this->_estateFiles[$estateId][$imageId] ) ) {
			return $this->_estateFiles[$estateId][$imageId];
		}

		return array();
	}
}
