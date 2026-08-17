<?php

/**
 *
 *    Copyright (C) 2026 onOffice GmbH
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

namespace onOffice\WPlugin\Controller;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\SDKWrapper;

/**
 * Resolves, for every currently active WPML language, the language-specific "MainID" of a
 * given parent/main estate at Enterprise. Used by the "complexunits" list type to store one
 * parent estate id per language alongside the list configuration, so that the standalone list
 * (which has no page context to derive the estate id from) can query the correct language
 * variant of the parent estate's child estates.
 *
 * ASSUMPTION / TODO: There is no prior code or documented API contract in this plugin for
 * resolving a language-specific "MainID" of an estate. This implementation re-reads the
 * parent estate via the "estate" "read" action with a "language" parameter and takes the
 * returned record id as the language-specific id. This must be verified against a real
 * onOffice Enterprise instance before rollout and adjusted if the actual contract differs.
 */
class ComplexUnitsMainIdResolver
{
	/** @var SDKWrapper */
	private $_pSDKWrapper;

	/**
	 * @param SDKWrapper $pSDKWrapper
	 */
	public function __construct(SDKWrapper $pSDKWrapper)
	{
		$this->_pSDKWrapper = $pSDKWrapper;
	}

	/**
	 * @param string $parentEstateId onOffice internal estate id of the stammobjekt
	 * @return array Map of onOffice 3-letter language code (see Language::LOCALE_MAPPING) to
	 *               the language-specific main estate id. Languages that could not be resolved
	 *               are omitted.
	 */
	public function resolveMainIdsByLanguage(string $parentEstateId): array
	{
		$result = [];

		if ($parentEstateId === '') {
			return $result;
		}

		// Language::getAllWPMLLanguages() already filters out locales that cannot be mapped
		// to an onOffice 3-letter language code, so no null language is ever sent here.
		foreach (Language::getAllWPMLLanguages() as $languageCode) {
			$mainId = $this->resolveMainId($parentEstateId, $languageCode);
			if ($mainId !== null) {
				$result[$languageCode] = $mainId;
			}
		}

		return $result;
	}

	/**
	 * @param string $parentEstateId
	 * @param string $languageCode onOffice 3-letter language code
	 * @return string|null
	 */
	private function resolveMainId(string $parentEstateId, string $languageCode): ?string
	{
		$pAction = new APIClientActionGeneric($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'estate');
		$pAction->setParameters([
			'data' => ['Id'],
			'filter' => [
				'Id' => [
					['op' => '=', 'val' => (int) $parentEstateId],
				],
			],
			'language' => $languageCode,
		]);
		$pAction->addRequestToQueue()->sendRequests();

		try {
			$records = $pAction->getResultRecords();
		} catch (ApiClientException $pException) {
			return null;
		}

		$firstRecord = reset($records);

		if (is_array($firstRecord) && isset($firstRecord['id']) && $firstRecord['id'] !== '') {
			return (string) $firstRecord['id'];
		}

		return null;
	}
}
