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

use Exception;
use const JSON_PRETTY_PRINT;
use function json_encode;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class ApiClientException
	extends Exception
{
	/** @var APIClientActionGeneric */
	private $_pApiClientAction = null;


	/**
	 *
	 * @param APIClientActionGeneric $pApiClientAction
	 *
	 */

	public function __construct(APIClientActionGeneric $pApiClientAction)
	{
		$this->_pApiClientAction = $pApiClientAction;
		parent::__construct();
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function __toString()
	{
		$pApiClientAction = $this->_pApiClientAction;
		$messageFormat = [
			'actionid' => $pApiClientAction->getActionId(),
			'resourceid' => $pApiClientAction->getResourceId(),
			'resourcetype' => $pApiClientAction->getResourceType(),
			'parameters' => $pApiClientAction->getParameters(),
		];

		$message = json_encode($messageFormat, JSON_PRETTY_PRINT);
		$errorCode = "errorCode: ".json_encode($pApiClientAction->getErrorCode(), JSON_PRETTY_PRINT);
		return $message."\n\n".$errorCode."\n\n".parent::__toString();
	}


	/**
	 *
	 * @return APIClientActionGeneric
	 *
	 */

	public function getApiClientAction(): APIClientActionGeneric
	{
		return $this->_pApiClientAction;
	}
}
