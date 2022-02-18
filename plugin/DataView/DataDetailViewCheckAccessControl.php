<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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

declare(strict_types=1);

namespace onOffice\WPlugin\DataView;


use DI\DependencyException;
use DI\NotFoundException;
use onOffice\SDK\Exception\HttpFetchNoResultException;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\API\APIEmptyResultException;
use onOffice\WPlugin\Factory\EstateListFactory;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 * DO NOT MOVE OR RENAME - NAME AND/OR NAMESPACE MAY BE USED IN SERIALIZED DATA
 *
 */

class DataDetailViewCheckAccessControl
{
	/** @var DataDetailViewHandler */
	private $pDataDetailViewHandler = null;

	/** @var int */
	private $estateId = null;


	/**
	 * @param int $estateId
	 */

	public function __construct(int $estateId)
	{
		$this->estateId = $estateId;
		$this->pDataDetailViewHandler = new DataDetailViewHandler();
	}


	/**
	 * @return bool
	 */

	public function checkAccessControl(): bool
	{
		$accessControl = $this->pDataDetailViewHandler->getDetailView()->hasDetailView();

		if (!$accessControl) {
			$estateListFactory = new EstateListFactory($this->pDataDetailViewHandler);
			$pEstateDetail = $estateListFactory->createEstateDetail($this->estateId);
			$pEstateDetail->loadEstates();
			$pEstateDetail->estateIterator();
			$rawValues = $pEstateDetail->getRawValues();
			$referenz = $rawValues->getValueRaw($this->estateId)['elements']['referenz'];

			if ($referenz === "1") {
				return false;
			}
		}
		return true;
	}
}
