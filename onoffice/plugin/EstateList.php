<?php

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

use onOffice\SDK\onOfficeSDK;

/**
 *
 */


class EstateList {
	/** @var onOffice\SDK\onOfficeSDK */
	private $_pOnOfficeSdk = null;

	/** @var array */
	private $_fieldList = array();

	/** @var array */
	private $_config = array();

	/** @var array */
	private $_estateFiles = null;


	/**
	 *
	 * @param array $config
	 *
	 */

	public function __construct( array $config ) {
		$this->_pOnOfficeSdk = new onOfficeSDK();
		$this->_config = $config;
	}


	/**
	 *
	 * @param string $content
	 * @return string
	 *
	 */

	public function getFilterContent( $content ) {
		$regexSearch = '!(\[oo_estate ([0-9a-z]*)\])!';
		$matches = array();

		preg_match_all( $regexSearch, $content, $matches );

		$matchescount = count( $matches ) - 1;

		if ( 0 == $matchescount ) {
			return $content;
		}

		$onofficeTags = array_pop( $matches );

		foreach ( $onofficeTags as $id => $name ) {
			if ( ! array_key_exists( $name, $this->_config['estate'] ) ||
				 ! array_key_exists( 'data', $this->_config['estate'][$name] ) ) {
				continue;
			}
			if ( ! array_key_exists( 'filter', $this->_config['estate'][$name] ) ) {
				$this->_config['estate'][$name]['filter'] = array();
			}

			$filter = $this->_config['estate'][$name]['filter'];
			$data = $this->_config['estate'][$name]['data'];
			$content = str_replace( $matches[0][$id], $this->getEstateList( $data, $filter ), $content );
		}

		return $content;
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function getEstateList( $data = array(), $filter = array() ) {
		$pSdk = $this->_pOnOfficeSdk;
		$pSdk->setApiVersion( '1.5' );

		$parametersGetEstateList = array(
			'data' => $data,
			'filter' => $filter,
		);

		$idReadEstate = $pSdk->callGeneric( onOfficeSDK::ACTION_ID_READ, 'estate', $parametersGetEstateList );
		$parametersGetFieldList = array(
			'labels' => 1,
			'language' => 'DEU',
		);

		$token = $this->_config['token'];
		$secret = $this->_config['secret'];

		$idGetFields = $pSdk->callGeneric( onOfficeSDK::ACTION_ID_GET, 'fields', $parametersGetFieldList );
		$pSdk->sendRequests( $token, $secret);

		$responseArrayEstates = $pSdk->getResponseArray( $idReadEstate );

		$estateIds = $this->collectEstateIds($responseArrayEstates);
		$idGetEstatePictures = $pSdk->callGeneric
			( onOfficeSDK::ACTION_ID_GET, 'estatepictures', array('estateids' => $estateIds, 'categories' => 'Titelbild') );
		$responseArrayFieldList = $pSdk->getResponseArray( $idGetFields );


		$pSdk->sendRequests( $this->_config['token'], $this->_config['secret']);
		$responseArrayEstatePictures = $pSdk->getResponseArray( $idGetEstatePictures );

		$this->collectEstatePictures($responseArrayEstatePictures);

		$fieldList = $responseArrayFieldList['data']['records'];
		$this->createFieldList( $fieldList );

		return $this->createHtml( $responseArrayEstates );
	}


	/**
	 *
	 * @param array $estateResponseArray
	 * @return array
	 *
	 */

	private function collectEstateIds( $estateResponseArray ) {
		if ( ! array_key_exists( 'data', $estateResponseArray ) ) {
			return array();
		}

		$estateProperties = $estateResponseArray['data']['records'];

		$estateIds = array();

		foreach ( $estateProperties as $estate ) {
			if ( ! array_key_exists( 'id', $estate ) ) {
				return array();
			}

			$estateIds[] = $estate['id'];
		}

		return $estateIds;
	}


	/**
	 *
	 * @param array $responseArrayEstatePictures
	 * @return null
	 *
	 */

	private function collectEstatePictures( $responseArrayEstatePictures ) {
		if ( null !== $this->_estateFiles ||
			! array_key_exists('data', $responseArrayEstatePictures) ||
			! array_key_exists('records', $responseArrayEstatePictures['data'])) {
			return;
		}

		$this->_estateFiles = array();
		$records = $responseArrayEstatePictures['data']['records'];

		foreach ( $records as $properties ) {
			$this->_estateFiles[$properties['elements']['estateid']][] = $properties['elements']['url'];
		}
	}


	/**
	 *
	 * @param array $fieldResult
	 * @return null
	 *
	 */

	private function createFieldList( $fieldResult ) {
		if ( count( $fieldResult) == 0 ) {
			return;
		}

		foreach ($fieldResult as $moduleProperties) {
			if ( ! array_key_exists( 'elements', $moduleProperties ) ) {
				continue;
			}

			foreach ($moduleProperties['elements'] as $fieldName => $fieldProperties) {
				if ( 'label' == $fieldName ) {
					continue;
				}

				$this->_fieldList[$moduleProperties['id']][$fieldName] = $fieldProperties;
			}
		}
	}


	/**
	 *
	 * @param array $responseArray
	 * @return string
	 *
	 */

	private function createHtml( $responseArray ) {
		if ( ! array_key_exists( 'data', $responseArray ) ) {
			return;
		}

		$records = $responseArray['data']['records'];
		$output = '';

		foreach ( $records as $record ) {
			$recordType = $record['type'];
			$output .= '<p>';

			foreach ( $record['elements'] as $field => $value ) {
				$fieldNewName = $field;
				if ( is_numeric( $value ) && 0 == $value ) {
					continue;
				}

				if ( array_key_exists( $recordType, $this->_fieldList ) &&
					array_key_exists( $field, $this->_fieldList[$recordType] ) ) {
					$fieldNewName = $this->_fieldList[$recordType][$field]['label'];
				}


				$output .= wptexturize( $fieldNewName.': '.$value ).'<br>';
			}

			if ( array_key_exists( $record['id'], $this->_estateFiles ) ) {
				foreach ( $this->_estateFiles[$record['id']] as $picture ) {
					$output .= '<img src="'.$picture.'" width=200>';
				}
			}

			$output .= '</p>';
		}

		return $output;
	}
}