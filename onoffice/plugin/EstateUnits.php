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

use onOffice\SDK\Exception\HttpFetchNoResultException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataListViewFactory;
use onOffice\WPlugin\Filter\DefaultFilterBuilderUnitList;

/**
 *
 */

class EstateUnits {
	/** @var array */
	private $_estateUnits = array();

	/** @var DataListView */
	private $_pDataListView = null;


	/**
	 *
	 * @param int[] $estateIds
	 * @param string $viewName
	 *
	 */

	 public function __construct( $estateIds, $viewName ) {
		$pDataListViewFactory = new DataListViewFactory();
		$this->_pDataListView = $pDataListViewFactory->getListViewByName(
			$viewName, DataListView::LISTVIEW_TYPE_UNITS );

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
	 * @param array $responseArrayEstateUnits
	 * @throws HttpFetchNoResultException
	 *
	 */

	private function evaluateEstateUnits( $responseArrayEstateUnits ) {
		if ( ! isset( $responseArrayEstateUnits['data']['records'] ) ) {
			throw new HttpFetchNoResultException();
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
	 * @param int $estateId
	 * @return string
	 *
	 */

	public function generateHtmlOutput( $estateId ) {
		$units = $this->getEstateUnits( $estateId );
		$random = $this->_pDataListView->getRandom();

		if ($random) {
			// shuffle() twice: once here and once in EstateList
			shuffle($units);
		}

		$pEstateList = new EstateList( $this->_pDataListView );
		$pEstateList->setShuffleResult($random);
		$pDefaultFilterBuilder = new DefaultFilterBuilderUnitList();
		$pDefaultFilterBuilder->setUnitIds( $units );
		$pEstateList->setDefaultFilterBuilder( $pDefaultFilterBuilder );
		$pEstateList->loadEstates( 1 );

		$templateName = $this->_pDataListView->getTemplate();
		$pTemplate = new Template( $templateName, 'estate', 'default' );
		$pTemplate->setEstateList( $pEstateList );
		$htmlOutput = $pTemplate->render();

		return $htmlOutput;
	}
}
