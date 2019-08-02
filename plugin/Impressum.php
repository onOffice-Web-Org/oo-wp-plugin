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

declare (strict_types=1);

namespace onOffice\WPlugin;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;

/**
 *
 */

class Impressum
{
	/** */
	const KEY_TITLE = 'title';

	/** */
	const KEY_FIRSTNAME = 'firstname';

	/** */
	const KEY_LASTNAME = 'lastname';

	/** */
	const KEY_FIRMA = 'firma';

	/** */
	const KEY_POSTCODE = 'postcode';

	/** */
	const KEY_CITY = 'city';

	/** */
	const KEY_STREET = 'street';

	/** */
	const KEY_HOUSENUMBER = 'housenumber';

	/** */
	const KEY_STATE = 'state';

	/** */
	const KEY_COUNTRY = 'country';

	/** */
	const KEY_PHONE = 'phone';

	/** */
	const KEY_MOBIL = 'mobil';

	/** */
	const KEY_FAX = 'fax';

	/** */
	const KEY_EMAIL = 'email';

	/** */
	const KEY_HOMEPAGE = 'homepage';

	/** */
	const KEY_VERTRETUNGSBERECHTIGTER = 'vertretungsberechtigter';

	/** */
	const KEY_BERUFSAUFSICHTSBEHOERDE = 'berufsaufsichtsbehoerde';

	/** */
	const KEY_HANDELSREGISTER = 'handelsregister';

	/** */
	const KEY_HANDELSREGISTERNUMMER = 'handelsregisterNr';

	/** */
	const KEY_UST_ID = 'ustId';

	/** */
	const KEY_BANK = 'bank';

	/** */
	const KEY_IBAN = 'iban';

	/** */
	const KEY_BIC = 'bic';

	/** */
	const KEY_CHAMBER = 'chamber';

	/** @var SDKWrapper */
	private $_pSDKWrapper = null;


	/**
	 *
	 * @param SDKWrapper $pSDKWrapper
	 *
	 */

	public function __construct(SDKWrapper $pSDKWrapper)
	{
		$this->_pSDKWrapper = $pSDKWrapper;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function load(): array
	{
		$pApiClientAction = new APIClientActionGeneric
			($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'impressum');

		$pApiClientAction->setParameters(['language' => Language::getDefault()]);
		$pApiClientAction->addRequestToQueue()->sendRequests();
		$records = $pApiClientAction->getResultRecords();
		return $records[0]['elements'] ?? [];
	}
}