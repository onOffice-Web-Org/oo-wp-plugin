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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

namespace onOffice\WPlugin;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\SDKWrapper;

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

	/** @var Impressum */
	static private $_pInstance = null;

	/** @var array */
	private $_data = array();

	/** @var array */
	private static $_keys = array
		(
			self::KEY_BANK,
			self::KEY_BERUFSAUFSICHTSBEHOERDE,
			self::KEY_BIC,
			self::KEY_CHAMBER,
			self::KEY_CITY,
			self::KEY_COUNTRY,
			self::KEY_COUNTRY,
			self::KEY_EMAIL,
			self::KEY_EMAIL,
			self::KEY_FAX,
			self::KEY_FIRMA,
			self::KEY_FIRSTNAME,
			self::KEY_HANDELSREGISTER,
			self::KEY_HANDELSREGISTERNUMMER,
			self::KEY_HOMEPAGE,
			self::KEY_HOUSENUMBER,
			self::KEY_IBAN,
			self::KEY_LASTNAME,
			self::KEY_MOBIL,
			self::KEY_PHONE,
			self::KEY_POSTCODE,
			self::KEY_STATE,
			self::KEY_STREET,
			self::KEY_TITLE,
			self::KEY_UST_ID,
			self::KEY_VERTRETUNGSBERECHTIGTER,
		);

	/**
	 *
	 */

	private function __construct()
	{
		$language = Language::getDefault();

		$pSDKWrapper = new SDKWrapper();
		$requestParameters = array('language' => $language);
		$handle = $pSDKWrapper->addRequest(
				onOfficeSDK::ACTION_ID_READ, 'impressum', $requestParameters);

		$pSDKWrapper->sendRequests();
		$response = $pSDKWrapper->getRequestResponse( $handle );

		$this->_data = $response['data']['records'][0]['elements'];
	}


	/** @return array */
	public function getData()
	{ return $this->_data; }


	/**
	 *
	 * @param string $key
	 * @return string
	 *
	 */

	public function getDataByKey($key)
	{
		$returnValue = null;

		if (array_key_exists($key, $this->_data))
		{
			$returnValue = $this->_data[$key];
		}

		return $returnValue;
	}


	/**
	 *
	 * @return Impressum
	 *
	 */

	public static function getInstance()
	{
		if (self::$_pInstance === null)
		{
			self::$_pInstance = new Impressum();
		}

		return self::$_pInstance;
	}


	/**
	 *
	 * @param string $templateString
	 * @return string
	 *
	 */

	public static function replaceAll($templateString)
	{
		$pInstance = self::getInstance();

		$searchpattern = array();
		$replacepattern = array();

		foreach (self::$_keys as $key)
		{
			$searchpattern []= '/\[oo_basicdata '.$key.'\]/';
			$replacepattern []= $pInstance->getDataByKey($key);
		}

		$template = preg_replace($searchpattern, $replacepattern, $templateString);

		return $template;
	}
}
