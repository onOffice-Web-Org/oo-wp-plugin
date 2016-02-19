<?php

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


	/**
	 *
	 * @param int[] $estateIds
	 * @param string[] $pictureCategories
	 *
	 */

	public function __construct( $estateIds, $pictureCategories ) {
		$pSDKWrapper = new SDKWrapper();
		$handleGetEstatePicturesOriginal = $pSDKWrapper->addRequest(
			onOfficeSDK::ACTION_ID_GET, 'estatepictures', array(
				'estateids' => $estateIds,
				'categories' => $pictureCategories,
			)
		);

		$pSDKWrapper->sendRequests();

		$responseArrayEstatePicturesOriginal = $pSDKWrapper->getRequestResponse(
			$handleGetEstatePicturesOriginal );
		$this->collectEstatePictures( $responseArrayEstatePicturesOriginal );
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
}
