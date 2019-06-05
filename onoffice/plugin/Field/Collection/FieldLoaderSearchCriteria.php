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

namespace onOffice\WPlugin\Field\Collection;

use Generator;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\SDKWrapper;


/**
 *
 */

class FieldLoaderSearchCriteria
	implements FieldLoader
{
	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var FieldRowConverterSearchCriteria */
	private $_pRowConverter = null;


	/**
	 *
	 * @param SDKWrapper $pSDKWrapper
	 * @param FieldRowConverterSearchCriteria $pFieldRowConverter
	 *
	 */

	public function __construct(
		SDKWrapper $pSDKWrapper,
		FieldRowConverterSearchCriteria $pFieldRowConverter)
	{
		$this->_pSDKWrapper = $pSDKWrapper;
		$this->_pRowConverter = $pFieldRowConverter;
	}


	/**
	 *
	 * @return Generator
	 *
	 */

	public function load(): Generator
	{
		$pApiClientActionSearchCriteriaFields = new APIClientActionGeneric
			($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'searchCriteriaFields');
		$pApiClientActionSearchCriteriaFields->setParameters([
			'language' => Language::getDefault(),
			'additionalTranslations' => true,
		]);
		$pApiClientActionSearchCriteriaFields->addRequestToQueue()->sendRequests();

		$result = $pApiClientActionSearchCriteriaFields->getResultRecords();
		$fields = array_column($result, 'elements');

		foreach ($fields as $category) {
			if ($category['name'] === 'Umkreis') {
				// ...
			}

			foreach ($category['fields'] as $row) {
				$rowConverted = $this->_pRowConverter->convertRow($row);
				yield $rowConverted['id'] => $rowConverted;
			}
		}
	}
}
