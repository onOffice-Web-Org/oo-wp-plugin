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

namespace onOffice\WPlugin\Controller\ContentFilter;

use Exception;
use onOffice\WPlugin\Impressum;
use onOffice\WPlugin\Utility\Logger;


/**
 *
 */

class ContentFilterShortCodeImprint
	implements ContentFilterShortCode
{
	/** @var Impressum */
	private $_pImpressum = null;

	/** @var Logger */
	private $_pLogger = null;


	/**
	 *
	 * @param Impressum $pImpressum
	 * @param Logger $pLogger
	 *
	 */

	public function __construct(Impressum $pImpressum, Logger $pLogger)
	{
		$this->_pImpressum = $pImpressum;
		$this->_pLogger = $pLogger;
	}


	/**
	 *
	 * @param array $attributesInput
	 * @return string
	 *
	 */

	public function replaceShortCodes(array $attributesInput): string
	{
		$value = '';

		try {
			$this->_pImpressum->load();
			if (count($attributesInput) === 1) {
				$attribute = $attributesInput[0];
				$value = $this->_pImpressum->getDataByKey($attribute);
			}
		} catch (Exception $pException) {
			$value = $this->_pLogger->logErrorAndDisplayMessage($pException);
		}
		return $value;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getTag(): string
	{
		return 'oo_basicdata';
	}
}
