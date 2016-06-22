<?php

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2016, onOffice(R) Software AG
 *
 */


namespace onOffice\WPlugin\Region;

use onOffice\WPlugin\SDKWrapper;
use onOffice\SDK\onOfficeSDK;

/**
 *
 */

class RegionController {
	/** @var string */
	private $_language = null;

	/** @var array */
	private $_regions = null;


	/**
	 *
	 * @param string $language
	 *
	 */

	public function __construct( $language ) {
		$this->_language = $language;
	}


	/**
	 *
	 */

	private function fetchRegions() {
		$pSdk = new SDKWrapper();
		$regionHandle = $pSdk->addRequest( onOfficeSDK::ACTION_ID_GET, 'regions' );
		$pSdk->sendRequests();

		$response = $pSdk->getRequestResponse( $regionHandle );
		$regionList = $response['data']['records'];
		$this->_regions = $this->createRegionObjects( $regionList );
	}


	/**
	 *
	 * @param array $regionList
	 * @param bool $level1
	 * @return \onOffice\WPlugin\Region\Region[]
	 *
	 */

	private function createRegionObjects( array $regionList, $level1 = true ) {
		$regions = array();
		foreach ( $regionList as $regionProperties ) {
			if ( $level1 ) {
				$elements = $regionProperties['elements'];
			} else {
				$elements = $regionProperties;
			}
			$id = $elements['id'];
			$name = $elements['name'];
			$description = $elements['description'];
			$postalCodes = $elements['postalcodes'];
			$state = $elements['state'];
			$country = $elements['country'];

			$pRegion = new Region( $id, $this->_language );
			$pRegion->setName( $name );
			$pRegion->setDescription( $description );
			$pRegion->setPostalCodes( $postalCodes );
			$pRegion->setState( $state );
			$pRegion->setCountry( $country );

			$children = $this->createRegionObjects( $elements['children'], false );
			$pRegion->setChildren( $children );

			$regions[] = $pRegion;
		}

		return $regions;
	}


	/**
	 *
	 * @return Region[]
	 *
	 */

	public function getRegions() {
		if ( is_null( $this->_regions ) ) {
			$this->fetchRegions();
		}

		return $this->_regions;
	}
}
