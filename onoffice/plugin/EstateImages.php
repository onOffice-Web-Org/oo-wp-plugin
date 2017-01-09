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

use onOffice\SDK\onOfficeSDK;

/**
 *
 */

class EstateImages {
	/** @var array */
	private $_estateImages = array();

	/** @var int */
	private $_handleEstatePictures = null;

	/** @var array */
	private $_pictureCategories = array();


	/**
	 *
	 * @param string[] $pictureCategories
	 *
	 */

	public function __construct( $pictureCategories ) {
		$this->_pictureCategories = $pictureCategories;

		add_action('oo_beforeEstateRelations', array($this, 'registerRequest'), 10, 2);
		add_action('oo_afterEstateRelations', array($this, 'parseRequest'), 10, 1);
	}


	/**
	 *
	 * @param \onOffice\WPlugin\SDKWrapper $pSDKWrapper
	 * @param array $estateIds
	 *
	 */

	public function registerRequest( SDKWrapper $pSDKWrapper, array $estateIds ) {
		$this->_handleEstatePictures = $pSDKWrapper->addRequest(
			onOfficeSDK::ACTION_ID_GET, 'estatepictures', array(
				'estateids' => $estateIds,
				'categories' => $this->_pictureCategories,
			)
		);
	}


	/**
	 *
	 * @param \onOffice\WPlugin\SDKWrapper $pSDKWrapper
	 *
	 */

	public function parseRequest( SDKWrapper $pSDKWrapper) {
		$responseArrayEstatePictures = $pSDKWrapper->getRequestResponse(
			$this->_handleEstatePictures );
		$this->collectEstatePictures( $responseArrayEstatePictures );
	}


	/**
	 *
	 * @param type $responseArrayEstatePictures
	 * @throws \onOffice\SDK\Exception\HttpFetchNoResultException
	 *
	 */

	private function collectEstatePictures( $responseArrayEstatePictures ) {
		if ( ! isset( $responseArrayEstatePictures['data']['records'] ) ) {
			throw new \onOffice\SDK\Exception\HttpFetchNoResultException();
		}

		$records = $responseArrayEstatePictures['data']['records'];

		foreach ( $records as $properties ) {
			$estateId = $properties['elements']['estateid'];
			$imageType = $properties['elements']['type'];
			$imageUrl = $properties['elements']['url'];
			$imageText = $properties['elements']['text'];
			$imageTitle = $properties['elements']['title'];
			$imageId = $properties['id'];

			$image = array(
				'id' => $imageId,
				'url' => $imageUrl,
				'title' => $imageTitle,
				'text' => $imageText,
				'imagetype' => $imageType,
			);

			$this->_estateImages[$estateId][$imageId] = $image;
		}
	}


	/**
	 *
	 * @param int $estateId
	 * @return array
	 *
	 */

	public function getEstatePictures( $estateId ) {
		if ( array_key_exists( $estateId, $this->_estateImages ) ) {
			return $this->_estateImages[$estateId];
		}

		return array();
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getEstatePictureUrl( $imageId, $estateId, array $options = null ) {
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

		if ( ! empty( $this->_estateImages[$estateId][$imageId] ) ) {
			return esc_url( $this->_estateImages[$estateId][$imageId]['url'].$size );
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

	public function getEstatePuctureTitle( $imageId, $estateId)
	{
		if ( ! empty( $this->_estateImages[$estateId][$imageId] ) )	{
			return $this->_estateImages[$estateId][$imageId]['title'];
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

	public function getEstatePictureText( $imageId, $estateId)
	{
		if ( ! empty( $this->_estateImages[$estateId][$imageId] ) )	{
			return $this->_estateImages[$estateId][$imageId]['text'];
		}

		return null;
	}



	/**
	 *
	 * @param int $imageId
	 * @param int $estateId
	 * @return array
	 *
	 */

	public function getEstatePictureValues( $imageId, $estateId)
	{
		if ( ! empty( $this->_estateImages[$estateId][$imageId] ) ) {
			return $this->_estateImages[$estateId][$imageId];
		}

		return array();
	}
}
