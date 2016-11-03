<?php

/**
 *
 *    Copyright (C) 2016  onOffice Software AG
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace onOffice\WPlugin;

use onOffice\SDK\onOfficeSDK;

/**
 *
 */

class EstateUnits {
	/** @var array */
	private $_estateUnits = array();

	/** @var string */
	private $_config = null;

	/** @var string */
	private $_view = null;

	/** @var int */
	private $_recordsPerPage = null;

	/**
	 *
	 * @param int[] $estateIds
	 *
	 */

	 public function __construct( $estateIds, $configName, $configView ) {
		$this->_config = $configName;
		$this->_view = $configView;
		$pSDKWrapper = new SDKWrapper();
		$handleGetEstateUnits = $pSDKWrapper->addRequest(
			onOfficeSDK::ACTION_ID_GET, 'idsfromrelation', array(
				'relationtype' => onOfficeSDK::RELATION_TYPE_COMPLEX_ESTATE_UNITS,
				'parentids' => $estateIds,
			)
		);

		$pSDKWrapper->sendRequests();

		$responseArrayEstateUnits = $pSDKWrapper->getRequestResponse( $handleGetEstateUnits );
		$this->evaluateEstateUnits( $responseArrayEstateUnits );
	 }


	 /**
	 *
	 * @param type $responseArrayEstateUnits
	 * @throws \onOffice\SDK\Exception\HttpFetchNoResultException
	 *
	 */

	private function evaluateEstateUnits( $responseArrayEstateUnits ) {
		if ( ! isset( $responseArrayEstateUnits['data']['records'] ) ) {
			throw new \onOffice\SDK\Exception\HttpFetchNoResultException();
		}

		$records = $responseArrayEstateUnits['data']['records'];

		foreach ( $records as $properties ) {
			foreach ( $properties['elements'] as $complex => $units ) {
				$this->_estateUnits[$complex] = $units;
			}
		}
	}


	/**
	 *
	 * @param int $estateId
	 * @return int[]
	 *
	 */

	public function getEstateUnits( $estateId ) {
		$units = array();
		if ( array_key_exists($estateId, $this->_estateUnits ) ) {
			$units = $this->_estateUnits[$estateId];
		}

		return $units;
	}


	/**
	 *
	 * @param int $estateId
	 * @return int
	 *
	 */

	public function getUnitCount( $estateId ) {
		$units = $this->getEstateUnits( $estateId );

		return count( $units );
	}


	/**
	 *
	 * @param int $count
	 *
	 */

	public function setRecordsPerPage( $count ) {
		$this->_recordsPerPage = $count;
	}


	/**
	 *
	 * @param int $estateId
	 * @return string
	 *
	 */

	public function generateHtmlOutput( $estateId ) {
		$units = $this->getEstateUnits( $estateId );

		$filter = array(
			'Id' => array(
				array( 'op' => 'in', 'val' => $units ),
			),
		);

		$estateConfig = ConfigWrapper::getInstance()->getConfigByKey( 'estate' );
		$templateName = $estateConfig[$this->_config]['views'][$this->_view]['template'];

		$pEstateList = new EstateList( $this->_config, $this->_view );
		$pEstateList->loadEstates( 1, $filter );

		$pTemplate = new Template( $templateName, 'estate', 'default' );
		$pTemplate->setEstateList( $pEstateList );
		$htmlOutput = $pTemplate->render();

		return $htmlOutput;
	}
}
