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
use onOffice\WPlugin\ImpressumConfiguration;
use onOffice\WPlugin\ImpressumConfigurationDefault;

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

	/** @var array */
	private $_data = [];

	/** @var ImpressumConfiguration */
	private $_pImpressumConfiguration = null;

	/** @var bool */
	private $_doneLoading = false;


	/**
	 *
	 * @param ImpressumConfiguration $pImpressumConfiguration
	 *
	 */

	public function __construct(ImpressumConfiguration $pImpressumConfiguration = null)
	{
		$this->_pImpressumConfiguration =
			$pImpressumConfiguration ?? new ImpressumConfigurationDefault();
	}


	/**
	 *
	 * @return $this
	 *
	 */

	public function load(): self
	{
		if (!$this->_doneLoading) {
			$requestParameters = ['language' => Language::getDefault()];
			$pSDKWrapper = $this->_pImpressumConfiguration->getSDKWrapper();

			$pApiClientAction = new APIClientActionGeneric
				($pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'impressum');

			$pApiClientAction->setParameters($requestParameters);
			$pApiClientAction->addRequestToQueue()->sendRequests();
			$records = $pApiClientAction->getResultRecords();
			$this->_data = $records[0]['elements'] ?? [];
			$this->_doneLoading = true;
		}
		return $this;
	}


	/**
	 *
	 * @param string $key
	 * @return string
	 *
	 */

	public function getDataByKey(string $key): string
	{
		return $returnValue = $this->_data[$key] ?? '';
	}


	/** @return array */
	public function getData(): array
		{ return $this->_data; }
}