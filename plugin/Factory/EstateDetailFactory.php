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

namespace onOffice\WPlugin\Factory;

use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\EstateDetail;
use onOffice\WPlugin\Filter\DefaultFilterBuilderDetailView;


/**
 *
 */

class EstateDetailFactory
{
	/** @var DataDetailViewHandler */
	private $_pDataDetailViewHandler;


	/**
	 *
	 * @param DataDetailViewHandler $pDataDetailViewHandler
	 *
	 */

	public function __construct(DataDetailViewHandler $pDataDetailViewHandler)
	{
		$this->_pDataDetailViewHandler = $pDataDetailViewHandler;
	}


	/**
	 *
	 * @param int $estateId
	 * @return EstateDetail
	 *
	 */

	public function createEstateDetail(int $estateId): EstateDetail
	{
		$pDataDetailView = $this->_pDataDetailViewHandler->getDetailView();

		$pDefaultFilterBuilder = new DefaultFilterBuilderDetailView();
		$pDefaultFilterBuilder->setEstateId($estateId);

		$pEstateDetail = new EstateDetail($pDataDetailView);
		$pEstateDetail->setDefaultFilterBuilder($pDefaultFilterBuilder);
		$pEstateDetail->setEstateId($estateId);
		return $pEstateDetail;
	}
}
