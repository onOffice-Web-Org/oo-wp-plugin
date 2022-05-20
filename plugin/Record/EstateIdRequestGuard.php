<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

declare (strict_types=1);

namespace onOffice\WPlugin\Record;

use onOffice\WPlugin\ArrayContainerEscape;
use onOffice\WPlugin\Factory\EstateListFactory;
use onOffice\WPlugin\Utility\Redirector;

/**
 *
 * Checks if an estate ID exists
 *
 */

class EstateIdRequestGuard
{
	/** @var EstateListFactory */
	private $_pEstateDetailFactory;

	/** * @var ArrayContainerEscape */
	private $_estateData;


	/**
	 *
	 * @param EstateListFactory $pEstateDetailFactory
	 *
	 */

	public function __construct(EstateListFactory $pEstateDetailFactory)
	{
		$this->_pEstateDetailFactory = $pEstateDetailFactory;
	}


	/**
	 *
	 * @param int $estateId
	 * @return bool
	 *
	 */

	public function isValid(int $estateId): bool
	{
		$pEstateDetail = $this->_pEstateDetailFactory->createEstateDetail($estateId);
		$pEstateDetail->loadEstates();
		$this->_estateData = $pEstateDetail->estateIterator();
		return $this->_estateData !== false;
	}


	/**
	 *
	 * @param  int  $estateId
	 * @param  Redirector  $pRedirector
	 *
	 * @return void
	 */

	public function estateDetailUrlChecker( int $estateId, Redirector $pRedirector ) {
		$estateTitle = $this->_estateData->getValue( 'objekttitel' );
		$pRedirector->redirectDetailView( $estateId, $estateTitle );
	}
}
