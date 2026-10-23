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

namespace onOffice\tests\Mocks;

use onOffice\SDK\onOfficeSDK;
use onOffice\tests\SDKWrapperMocker;

/**
 * Estate supervisor lookup for an API user without the right "Benutzerdaten über API auslesen".
 */

trait EstateSupervisorLookupMocker
{
	/**
	 * @param SDKWrapperMocker $pSDKWrapperMocker
	 * @param int $estateId
	 * @param int $userId
	 * @param string $userName
	 */

	private function mockEstateSupervisorLookupWithoutUserReadRight(
		SDKWrapperMocker $pSDKWrapperMocker, int $estateId, int $userId, string $userName)
	{
		$pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_READ, 'estate', '', [
			'filter' => ['Id' => [['op' => '=', 'val' => $estateId]]],
			'data' => ['benutzer'],
		], null, [
			'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:read',
			'resourceid' => '',
			'resourcetype' => 'estate',
			'cacheable' => true,
			'identifier' => '',
			'data' => [
				'meta' => ['cntabsolute' => null],
				'records' => [
					['id' => $estateId, 'type' => 'estate', 'elements' => ['benutzer' => (string)$userId]],
				],
			],
			'status' => ['errorcode' => 0, 'message' => 'OK'],
		]);

		$pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'users', '', [], null, [
			'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:get',
			'resourceid' => '',
			'resourcetype' => 'users',
			'cacheable' => true,
			'identifier' => '',
			'data' => [
				'meta' => ['cntabsolute' => null],
				'records' => [
					['id' => $userId, 'type' => '', 'elements' => ['username' => $userName]],
				],
			],
			'status' => ['errorcode' => 0, 'message' => 'OK'],
		]);

		$pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_READ, 'user', '', [
			'data' => ['Nr', 'Name'],
			'filter' => ['Nr' => [['op' => '=', 'val' => (string)$userId]]],
			'listlimit' => 1,
		], null, $this->getMissingUserReadRightResponse());
	}


	/**
	 * @return array
	 */

	private function getMissingUserReadRightResponse(): array
	{
		return [
			'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:read',
			'resourceid' => '',
			'resourcetype' => 'user',
			'cacheable' => false,
			'identifier' => '',
			'data' => [],
			'status' => ['errorcode' => 500, 'message' => 'Error'],
		];
	}


	/**
	 * @param SDKWrapperMocker $pSDKWrapperMocker
	 * @return bool
	 */

	private function hasUserResourceRequest(SDKWrapperMocker $pSDKWrapperMocker): bool
	{
		foreach ($pSDKWrapperMocker->getRequestArray() as $request) {
			if (json_decode($request, true)[1] === 'user') {
				return true;
			}
		}

		return false;
	}
}
