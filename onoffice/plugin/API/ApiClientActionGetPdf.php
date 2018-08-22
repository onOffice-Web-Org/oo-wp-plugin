<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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

namespace onOffice\WPlugin\API;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class ApiClientActionGetPdf
	extends APIClientActionGeneric
{
	/**
	 *
	 * @return array
	 *
	 */

	public function getResultRecords()
	{
		if ($this->getResultStatus()) {
			$result = $this->getResult();

			$documentBase64 = $result['data']['records'][0]['elements']['document'];
			$document = base64_decode($documentBase64);
			$parameters = $this->getParameters();

			if (isset($parameters['gzcompress']) && $parameters['gzcompress']) {
				$document = gzuncompress($document);
			}

			if ($document !== false) {
				return $document;
			}
		}
	}


	/**
	 *
	 * @return bool
	 *
	 */

	public function getResultStatus(): bool
	{
		$result = $this->getResult();
		return is_array($result) && isset($result['data']['records'][0]['elements']['document']);
	}


	/**
	 *
	 * @return bool
	 *
	 */

	public function getMimeTypeStatus(): bool
	{
		$result = $this->getResult();
		return isset($result['data']['records'][0]['elements']['type']);
	}


	/**
	 *
	 * @return string|null
	 *
	 */

	public function getMimeTypeResult()
	{
		$result = $this->getResult();

		if ($this->getMimeTypeStatus()) {
			return $result['data']['records'][0]['elements']['type'];
		}
	}
}
